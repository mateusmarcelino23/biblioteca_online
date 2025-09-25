<?php
session_start();

// Verifica se o professor está logado
if (!isset($_SESSION['professor_id'])) {
    header("Location: ../frontend/login.php");
    exit();
}

require '../includes/conn.php'; // Arquivo de conexão com o banco

// Verifica se o ID foi passado pela URL
if (!isset($_GET['id'])) {
    header("Location: visualizar_livros.php");
    exit();
}

// guarda o id do livro na variável
$id = $_GET['id'];

// exclui o livro do banco de dados
$sql = "DELETE FROM livros WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

// se ele executar a consulta ele mostra sucesso, senão ele mostra o erro
if ($stmt->execute()) {
    echo "<div class='alert alert-success'>Livro excluído com sucesso!</div>";
} else {
    echo "<div class='alert alert-danger'>Erro ao excluir o livro!</div>";
}

$stmt->close();
?>