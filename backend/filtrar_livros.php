<?php
// Inclui o arquivo de conexão com o banco de dados
require_once '../includes/conn.php';

// --- Construção da query base ---
// Seleciona os campos id, título e ISBN da tabela livros
// WHERE 1=1 é uma técnica que facilita adicionar filtros dinamicamente
$query = "SELECT id, titulo, isbn FROM livros WHERE 1=1";
$params = array(); // Array para armazenar os parâmetros da query

// --- Filtro opcional por busca ---
// Se o parâmetro 'busca' foi passado via GET e não está vazio
if (!empty($_GET['busca'])) {
    // Adiciona condição para filtrar pelo título ou pelo ISBN
    $query .= " AND (titulo LIKE ? OR isbn LIKE ?)";
    // Usa curingas % para pesquisa parcial
    $params[] = "%" . $_GET['busca'] . "%";
    $params[] = "%" . $_GET['busca'] . "%";
}

// --- Ordenação ---
// Ordena os resultados pelo título em ordem alfabética
$query .= " ORDER BY titulo ASC";

// --- Preparação e execução da query ---
// Prepared statements para segurança contra SQL Injection
$stmt = $conn->prepare($query);

// Se houver parâmetros, faz o bind dinâmico
if (!empty($params)) {
    // str_repeat('s', count($params)) cria uma string 's' para cada parâmetro (todos strings)
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}

// Executa a consulta
$stmt->execute();

// Recupera os resultados
$result = $stmt->get_result();

// --- Gerar opções HTML ---
// Opção padrão para o select
echo '<option value="">Selecione o livro</option>';

// Se houver resultados, cria uma option para cada livro
if ($result->num_rows > 0) {
    while ($livro = $result->fetch_assoc()) {
        // htmlspecialchars evita problemas de XSS
        echo sprintf(
            '<option value="%s">%s (ISBN: %s)</option>',
            $livro['id'],                      // valor do option (id do livro)
            htmlspecialchars($livro['titulo']), // título do livro
            htmlspecialchars($livro['isbn'])    // ISBN do livro
        );
    }
}

// Fecha o statement e libera recursos
$stmt->close();

// Fecha a conexão com o banco de dados
$conn->close();
