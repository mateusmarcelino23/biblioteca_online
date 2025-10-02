<?php
// Inicia sessão PHP
// Necessário para acessar informações do usuário logado e armazenar mensagens
session_start();

// --- Verifica se o professor está logado ---
// Se não estiver logado, redireciona para a página de login
if (!isset($_SESSION['professor_id'])) {
    header("Location: ../frontend/login.php");
    exit(); // Interrompe execução do script após redirecionamento
}

// Inclui arquivo de conexão com o banco
// Cria a variável $conn usada para todas consultas SQL
require '../includes/conn.php';

// --- Verifica se o ID do livro foi passado via URL ---
// Sem isso não sabemos qual livro editar
if (!isset($_GET['id'])) {
    header("Location: visualizar_livros.php"); // Redireciona se não tiver ID
    exit();
}

// Recebe o ID do livro da URL
$id = $_GET['id'];

// --- Busca os dados do livro para exibir no formulário de edição ---
// Prepared statement para evitar SQL Injection
$sql = "SELECT * FROM livros WHERE id = ?";
$stmt = $conn->prepare($sql);

// Associa o parâmetro (i = integer)
$stmt->bind_param("i", $id);

// Executa a query
$stmt->execute();

// Obtém o resultado como array associativo
$result = $stmt->get_result();

// Verifica se o livro existe
if ($result->num_rows === 0) {
    // Se não encontrou, exibe mensagem de erro
    echo "<div class='alert alert-danger'>Livro não encontrado!</div>";
    exit();
}

// Armazena os dados do livro para popular o formulário
$livro = $result->fetch_assoc();

// --- Verifica se o formulário foi enviado para atualizar o livro ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recebe os dados enviados pelo formulário
    $titulo = $_POST['titulo'];
    $autor = $_POST['autor'];
    $ano_publicacao = $_POST['ano_publicacao'];
    $genero = $_POST['genero'];
    $isbn = $_POST['isbn'];
    $quantidade = $_POST['quantidade'];

    // --- Atualiza o livro no banco ---
    $sql_update = "UPDATE livros SET titulo = ?, autor = ?, ano_publicacao = ?, genero = ?, isbn = ?, quantidade = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);

    // Associa os parâmetros (s = string, i = integer)
    $stmt_update->bind_param("ssssssi", $titulo, $autor, $ano_publicacao, $genero, $isbn, $quantidade, $id);

    // Executa a query e verifica se foi bem-sucedida
    if ($stmt_update->execute()) {
        echo "<div class='alert alert-success'>Livro atualizado com sucesso!</div>";
    } else {
        echo "<div class='alert alert-danger'>Erro ao atualizar o livro!</div>";
    }
}

// Fecha statement de seleção
$stmt->close();

// OBS: Você poderia fechar a conexão com $conn->close() ao final, mas se o script tiver mais operações pode manter aberta
