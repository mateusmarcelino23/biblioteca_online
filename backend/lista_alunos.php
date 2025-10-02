<?php
session_start(); // Inicia a sessão do PHP para usar variáveis de sessão (como o ID do professor logado)

// Verifica se o professor está logado
if (!isset($_SESSION['professor_id'])) { // Se não existe a variável de sessão do professor
    header("Location: ../frontend/login.php"); // Redireciona para a página de login
    exit(); // Interrompe a execução do script, garantindo que nada abaixo seja executado
}

require '../includes/conn.php'; // Inclui a conexão com o banco de dados para poder executar queries SQL

// Verifica se a URL contém o parâmetro "deletar" (id do aluno a ser removido)
if (isset($_GET['deletar'])) {
    $aluno_id = $_GET['deletar']; // Armazena o ID do aluno passado na URL

    // Prepara a query SQL para deletar o aluno específico pelo ID
    $sql = "DELETE FROM alunos WHERE id = ?";
    $stmt = $conn->prepare($sql); // Prepara a query para evitar SQL Injection
    $stmt->bind_param("i", $aluno_id); // Vincula o ID do aluno como inteiro (i) ao statement

    // Executa a query e verifica se funcionou
    if ($stmt->execute()) {
        // Se a execução foi bem-sucedida, exibe mensagem de sucesso
        echo "<div class='alert alert-success fade show' role='alert'>
                Aluno deletado com sucesso!
              </div>";
    } else {
        // Se houve erro na execução, exibe mensagem de erro
        echo "<div class='alert alert-danger fade show' role='alert'>
                Erro ao deletar aluno!
              </div>";
    }

    $stmt->close(); // Fecha o statement para liberar memória e recursos
}

$conn->close(); // Fecha a conexão com o banco de dados
