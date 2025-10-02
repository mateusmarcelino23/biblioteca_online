<?php
// Inicia a sessão PHP para acessar informações do usuário logado
session_start();

// --- Verifica se o professor está logado ---
// Se não estiver, redireciona para a página de login
if (!isset($_SESSION['professor_id'])) {
    header("Location: ../frontend/login.php");
    exit(); // Interrompe a execução após redirecionamento
}

// Inclui arquivo de conexão com o banco
// Cria a variável $conn usada para consultas SQL
require '../includes/conn.php';

// --- Verifica se o ID do livro foi passado via URL ---
// Sem isso não sabemos qual livro excluir
if (!isset($_GET['id'])) {
    // Redireciona para a página de visualização de livros se não tiver ID
    header("Location: visualizar_livros.php");
    exit();
}

// Armazena o ID do livro em uma variável para usar na query
$id = $_GET['id'];

// --- Deleta o livro no banco de dados ---
// Query com prepared statement para segurança contra SQL Injection
$sql = "DELETE FROM livros WHERE id = ?";
$stmt = $conn->prepare($sql);

// Associa o parâmetro do tipo inteiro (i = integer)
$stmt->bind_param("i", $id);

// Executa a query e verifica se foi bem-sucedida
if ($stmt->execute()) {
    // Se deu certo, mostra mensagem de sucesso
    echo "<div class='alert alert-success'>Livro excluído com sucesso!</div>";
} else {
    // Se deu errado, mostra mensagem de erro
    echo "<div class='alert alert-danger'>Erro ao excluir o livro!</div>";
}

// Fecha o statement para liberar recursos
$stmt->close();

// OBS: A conexão $conn poderia ser fechada com $conn->close() se não houver mais queries
