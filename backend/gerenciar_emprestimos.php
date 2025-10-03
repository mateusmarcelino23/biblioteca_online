<?php
session_start();
require '../includes/conn.php';

// Verifica se o professor está logado
if (!isset($_SESSION['professor_id'])) {
    header("Location: ../frontend/login.php");
    exit();
}

// Guarda o ID do professor logado
$professor_id = $_SESSION['professor_id'];

// Consulta todas as séries distintas dos alunos (para filtro ou dropdown)
$sql = "SELECT DISTINCT serie FROM alunos WHERE serie IS NOT NULL AND serie <> '' ORDER BY serie";
$classes = $conn->query($sql);

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['livro_id']) && isset($_POST['aluno_id'])) {
    // Recebe dados do formulário
    $livro_id = $_POST['livro_id'];
    $aluno_id = $_POST['aluno_id'];

    // Define datas de empréstimo e devolução
    $data_emprestimo = date("Y-m-d");                  // Hoje
    $data_devolucao = date("Y-m-d", strtotime("+15 days")); // 15 dias depois

    // Verifica se há quantidade disponível do livro
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

    // Se houver livros disponíveis
    if ($quantidade > 0) {
        // Insere o empréstimo
        $sql = "INSERT INTO emprestimos (livro_id, aluno_id, professor_id, data_emprestimo, data_devolucao) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiss", $livro_id, $aluno_id, $professor_id, $data_emprestimo, $data_devolucao);

        if ($stmt->execute()) {
            // Atualiza a quantidade do livro
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
        // Caso não haja livros disponíveis
        echo "<div class='alert alert-warning'>Livro indisponível para empréstimo.</div>";
    }
}
