<?php
// Inclui a conexão com o banco de dados
require '../config.php';
// --- Construção da query base ---
// Seleciona os campos do empréstimo, aluno e livro
// JOINs são usados para combinar dados de várias tabelas
$query = "SELECT 
    e.id,
    a.nome as aluno_nome,
    a.serie as aluno_serie,
    l.titulo as livro_titulo,
    l.isbn as livro_isbn,
    e.data_emprestimo,
    e.data_devolucao
FROM emprestimos e
JOIN alunos a ON e.aluno_id = a.id
JOIN livros l ON e.livro_id = l.id
WHERE 1=1"; // WHERE 1=1 facilita a adição de filtros dinâmicos

$params = array(); // Array para armazenar valores dos filtros

// --- Filtros opcionais ---
// Filtro por nome do aluno
if (!empty($_GET['filtro_aluno'])) {
    $query .= " AND a.nome LIKE ?"; // Adiciona condição LIKE
    $params[] = "%" . $_GET['filtro_aluno'] . "%"; // Valor do filtro com curingas %
}

// Filtro por série do aluno
if (!empty($_GET['filtro_serie'])) {
    $query .= " AND a.serie = ?"; // Condição de igualdade
    $params[] = $_GET['filtro_serie']; // Valor do filtro
}

// Filtro por título ou ISBN do livro
if (!empty($_GET['filtro_livro'])) {
    $query .= " AND (l.titulo LIKE ? OR l.isbn LIKE ?)"; // Permite pesquisa por título ou ISBN
    $params[] = "%" . $_GET['filtro_livro'] . "%";
    $params[] = "%" . $_GET['filtro_livro'] . "%"; // Repete para os dois placeholders
}

// --- Ordenação ---
// Mostra os empréstimos mais recentes primeiro
$query .= " ORDER BY e.data_emprestimo DESC";

// --- Preparação e execução da query ---
// Prepared statement evita SQL Injection
$stmt = $conn->prepare($query);

// Se houver parâmetros, faz o bind dinâmico
if (!empty($params)) {
    // str_repeat('s', count($params)) cria uma string 's' para cada parâmetro (todos strings)
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}

// Executa a consulta
$stmt->execute();

// Recupera os resultados como objeto MySQLi
$result = $stmt->get_result();

// --- Formatação dos resultados em HTML ---
// Se houver resultados, exibe em linhas de tabela
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Formata datas para o padrão brasileiro dd/mm/aaaa
        $data_emprestimo = date('d/m/Y', strtotime($row['data_emprestimo']));
        $data_devolucao = date('d/m/Y', strtotime($row['data_devolucao']));

        // Monta a linha da tabela
        echo "<tr>
                <td>{$row['aluno_nome']} ({$row['aluno_serie']})</td>
                <td>{$row['livro_titulo']} <br><small class='text-muted'>ISBN: {$row['livro_isbn']}</small></td>
                <td>{$data_emprestimo}</td>
                <td>{$data_devolucao}</td>
            </tr>";
    }
} else {
    // Se não houver resultados, exibe mensagem centralizada
    echo "<tr><td colspan='4' class='text-center'>Nenhum resultado encontrado</td></tr>";
}

// Fecha o statement para liberar recursos
$stmt->close();

// Fecha a conexão com o banco
$conn->close();
