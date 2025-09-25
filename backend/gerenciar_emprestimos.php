<?php
session_start();
require '../includes/conn.php';
//verifica se o professor tá logado
if (!isset($_SESSION['professor_id'])) {
    header("Location: ../frontend/login.php");
    exit();
}

//guarda o id do professor na variável
$professor_id = $_SESSION['professor_id'];
// faz a consulta pra pegar a serie dos alunos
$sql = "SELECT DISTINCT serie FROM alunos WHERE serie IS NOT NULL AND serie <> '' ORDER BY serie";
$classes = $conn->query($sql);

//verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['livro_id']) && isset($_POST['aluno_id'])) {
    // recebe os dados do formulário
    $livro_id = $_POST['livro_id'];
    $aluno_id = $_POST['aluno_id'];
    $data_emprestimo = date("Y-m-d");
    $data_devolucao = date("Y-m-d", strtotime("+15 days"));

    $sql = "SELECT quantidade FROM livros WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $livro_id);
    $stmt->execute();
    $stmt->bind_result($quantidade);
    if ($stmt->fetch() === null) {
        echo "<div class='alert alert-danger'>Erro ao verificar disponibilidade do livro.</div>";
        exit();
    }
    $stmt->close();

    if ($quantidade > 0) {
        $sql = "INSERT INTO emprestimos (livro_id, aluno_id, professor_id, data_emprestimo, data_devolucao) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiss", $livro_id, $aluno_id, $professor_id, $data_emprestimo, $data_devolucao);
        if ($stmt->execute()) {
            $sql = "UPDATE livros SET quantidade = quantidade - 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $livro_id);
            $stmt->execute();
            echo "<div class='alert alert-success'>Empréstimo registrado com sucesso!</div>";
        } else {
            echo "<div class='alert alert-danger'>Erro ao registrar empréstimo.</div>";
        }
        $stmt->close();
    } else {
        echo "<div class='alert alert-warning'>Livro indisponível para empréstimo.</div>";
    }
}
?>