<?php
// Inicia a sessão PHP
session_start();

$toast = null;

// Verifica se o professor está logado
if (!isset($_SESSION['professor_id'])) {
    header("Location: ../frontend/login.php");
    exit();
}

// Inclui o arquivo de conexão com o banco de dados
require '../includes/conn.php';

// Verifica se o ID do aluno foi passado via URL
if (!isset($_GET['id'])) {
    header("Location: visualizar_alunos.php");
    exit();
}

// Recebe o ID do aluno da URL
$id = $_GET['id'];

// Prepara a query para buscar os dados do aluno
$sql = "SELECT * FROM alunos WHERE id = ?";
$stmt = $conn->prepare($sql);

// Associa o parâmetro do ID à query
$stmt->bind_param("i", $id);

// Executa a query
$stmt->execute();

// Obtém o resultado da query
$result = $stmt->get_result();

// Verifica se o aluno existe
if ($result->num_rows === 0) {
    echo "<div class='alert alert-danger'>Aluno não encontrado!</div>";
    exit();
}

// Armazena os dados do aluno para popular o formulário
$aluno = $result->fetch_assoc();

// Prepara a query para buscar todos os professores ativos
$sql_prof = "SELECT id, nome FROM professores WHERE ativo = 1";
$result_prof = $conn->query($sql_prof);

// Armazena os professores em um array
$professores = [];
while ($row = $result_prof->fetch_assoc()) {
    $professores[] = $row;
}

// Verifica se o formulário foi enviado via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recebe os dados enviados pelo formulário
    $nome = $_POST['nome'];
    $serie = $_POST['serie'];
    $email = $_POST['email'];
    $professor_id = !empty($_POST['professor_id']) ? $_POST['professor_id'] : null;

    // Prepara a query para atualizar os dados do aluno
    $sql_update = "UPDATE alunos SET nome = ?, serie = ?, email = ?, professor_id = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);

    // Associa os parâmetros à query
    if ($professor_id === null) {
        $stmt_update->bind_param("ssssi", $nome, $serie, $email, $professor_id, $id);
    } else {
        $stmt_update->bind_param("sssii", $nome, $serie, $email, $professor_id, $id);
    }

    // Executa a query de atualização
    if ($stmt_update->execute()) {
        $toast = [
            'type' => 'success',
            'message' => 'Aluno atualizado com sucesso!'
        ];

        // Atualiza os dados do aluno
        $stmt = $conn->prepare("SELECT * FROM alunos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $aluno = $stmt->get_result()->fetch_assoc();
    } else {
        $toast = [
            'type' => 'error',
            'message' => 'Erro ao atualizar o aluno!'
        ];
    }
}

// Fecha statements
$stmt->close();
if (isset($stmt_update)) $stmt_update->close();
?>
