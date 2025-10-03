<?php
// session_start(): inicia uma sessão ou continua a sessão existente
session_start();

// isset(): verifica se a variável de sessão 'professor_id' existe
if (!isset($_SESSION['professor_id'])) {
    // header(): envia cabeçalho HTTP para redirecionamento
    header("Location: ../frontend/login.php");
    // exit(): encerra imediatamente a execução do script
    exit();
}

// require(): inclui e executa o arquivo de conexão com o banco de dados
require '../includes/conn.php';

// isset(): verifica se a variável $_GET['id'] existe
if (!isset($_GET['id'])) {
    // header(): recarrega a página de edição de livros
    header("Location: ../frontend/visualizar_livros.php");
    exit();
}

// Armazena o valor de $_GET['id'] na variável $id
$id = $_GET['id'];

// prepare(): prepara uma query SQL para execução segura
$sql = "SELECT * FROM livros WHERE id = ?";
$stmt = $conn->prepare($sql);

// bind_param(): associa variáveis aos parâmetros da query preparada
$stmt->bind_param("i", $id);

// execute(): executa a query preparada
$stmt->execute();

// get_result(): obtém o resultado da execução da query
$result = $stmt->get_result();

// num_rows: retorna o número de linhas do resultado
if ($result->num_rows === 0) {
    echo "<div class='alert alert-danger'>Livro não encontrado!</div>";
    exit();
}

// fetch_assoc(): retorna a próxima linha do resultado como array associativo
$livro = $result->fetch_assoc();

// $_SERVER["REQUEST_METHOD"]: verifica o método HTTP da requisição
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // $_POST[]: acessa os dados enviados pelo formulário via POST
    $titulo = $_POST['titulo'];
    $autor = $_POST['autor'];
    $ano_publicacao = $_POST['ano_publicacao'];
    $genero = $_POST['genero'];
    $isbn = $_POST['isbn'];
    $quantidade = $_POST['quantidade'];

    // prepare(): prepara a query de atualização
    $sql_update = "UPDATE livros SET titulo = ?, autor = ?, ano_publicacao = ?, genero = ?, isbn = ?, quantidade = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);

    // bind_param(): associa variáveis aos parâmetros da query de atualização
    $stmt_update->bind_param("ssssssi", $titulo, $autor, $ano_publicacao, $genero, $isbn, $quantidade, $id);

    // execute(): executa a query de atualização
    if ($stmt_update->execute()) {
        // $_SESSION[]: armazena uma mensagem de sucesso na sessão
        $_SESSION['mensagem'] = "Livro atualizado com sucesso!";
        // header(): recarrega a página de edição de livros
        header("Location: ../frontend/editar_livro.php?id=" . $id);
        exit();
    } else {
        echo "<div class='alert alert-danger'>Erro ao atualizar o livro!</div>";
    }
}

// close(): fecha o statement
$stmt->close();

// close(): fecha a conexão com o banco
$conn->close();
?>