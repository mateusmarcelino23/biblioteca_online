<?php
session_start(); // Inicia a sessão do PHP para poder acessar variáveis de sessão, como o ID do professor

require '../config.php';
// Verifica se o professor está logado, se não estiver, redireciona para a página de login
if (!isset($_SESSION['professor_id'])) {
    header("Location: ../frontend/login.php"); // Redireciona para login
    exit(); // Interrompe a execução do script para garantir que nada mais rode
}

// Array que vai guardar os tipos de parâmetros que vamos enviar para o bind_param
$param_types = ''; // Inicialmente vazio, será preenchido dinamicamente dependendo dos filtros

// Array que vai guardar os valores dos parâmetros que vamos enviar para a query
$param_values = []; // Inicialmente vazio, será preenchido com os valores vindos do GET

// Monta a query base de listagem dos empréstimos
$sql = "
    SELECT 
        e.id,              -- ID do empréstimo
        l.titulo AS livro, -- Título do livro emprestado
        a.nome AS aluno,   -- Nome do aluno que pegou o livro
        e.data_emprestimo, -- Data em que o livro foi emprestado
        e.data_devolucao,  -- Data prevista de devolução
        e.devolvido        -- Status de devolução (0 = não devolvido, 1 = devolvido)
    FROM emprestimos e
    JOIN livros l ON e.livro_id = l.id   -- Relaciona o empréstimo com o livro correspondente
    JOIN alunos a ON e.aluno_id = a.id  -- Relaciona o empréstimo com o aluno correspondente
    WHERE 1                             -- Condição neutra para permitir concatenar filtros dinamicamente
";

// Array que define quais filtros podemos aplicar e como eles serão inseridos na query
$filtros = [
    'aluno' => 'a.nome LIKE ?',           // Filtro por nome do aluno
    'livro' => 'l.titulo LIKE ?',         // Filtro por título do livro
    'estado' => 'e.devolvido = ?',        // Filtro por estado (devolvido ou não)
    'data_inicio' => 'e.data_emprestimo >= ?', // Filtro por data inicial do empréstimo
    'data_fim' => 'e.data_emprestimo <= ?'    // Filtro por data final do empréstimo
];

// Percorre todos os filtros possíveis
foreach ($filtros as $key => $clause) {
    if (!empty($_GET[$key])) { // Verifica se o filtro foi enviado via URL
        $sql .= " AND $clause"; // Adiciona a cláusula na query
        $param_types .= 's';    // Define que o tipo do parâmetro é string (s)

        // Para filtros de nome do aluno ou título do livro, adiciona % para busca parcial
        if ($key === 'aluno' || $key === 'livro') {
            $param_values[] = "%" . $_GET[$key] . "%"; // Valor com curinga
        } else {
            $param_values[] = $_GET[$key]; // Valor direto para datas e estado
        }
    }
}

// Ordena os resultados para mostrar os empréstimos mais recentes primeiro
$sql .= " ORDER BY e.data_emprestimo DESC";

// Prepara a consulta SQL para ser executada
$stmt = $conn->prepare($sql); // Cria um statement para evitar SQL injection

// Se houver parâmetros, vincula eles ao statement
if (!empty($param_values)) {
    $stmt->bind_param($param_types, ...$param_values); // Bind dinâmico com os tipos e valores corretos
}

// Executa a query no banco de dados
$stmt->execute(); // Realiza a busca com os filtros aplicados

// Recupera o resultado da consulta
$result = $stmt->get_result(); // Obtém todos os registros encontrados

// Aqui você pode iterar os resultados para exibir na tela
// Exemplo: while ($row = $result->fetch_assoc()) { ... }

// Fecha o statement para liberar recursos
$stmt->close();

// Fecha a conexão com o banco
$conn->close();
