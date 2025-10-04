<?php
// Inicia sessão PHP
// Necessário para armazenar mensagens de erro/sucesso e dados do usuário entre páginas
session_start();

// Verifica se o professor está logado
// Se não estiver logado, redireciona para a página de login
if (!isset($_SESSION['professor_id'])) {
    header("Location: ../frontend/login.php"); // Redirecionamento
    exit(); // Interrompe execução do script
}

// Inclui arquivo de conexão com o banco de dados
// $conn será usado para todas as operações SQL
require '../config.php';
// --- DELETAR PROFESSOR ---
if (isset($_POST['delete'])) { // Se o formulário enviou a ação de deletar
    $professor_id = $_POST['professor_id']; // ID do professor que será deletado

    // Prepara query SQL segura usando prepared statement para evitar SQL Injection
    $sql_delete = "DELETE FROM professores WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);

    // Associa o parâmetro da query, "i" = integer
    $stmt_delete->bind_param("i", $professor_id);

    // Executa a query e verifica sucesso ou falha
    if ($stmt_delete->execute()) {
        // Mensagem de sucesso
        echo "<div class='alert alert-success'>Professor deletado com sucesso!</div>";
    } else {
        // Mensagem de erro
        echo "<div class='alert alert-danger'>Erro ao deletar professor!</div>";
    }

    // Fecha o statement
    $stmt_delete->close();
}

// --- CADASTRAR PROFESSOR ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['delete'])) {
    // Recebe dados do formulário
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $cpf = $_POST['cpf'];
    // Criptografa a senha usando algoritmo seguro padrão do PHP
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    // Remove qualquer caractere que não seja número do CPF
    // Assim o CPF fica limpo para armazenar no banco
    $cpf_limpo = preg_replace('/[^0-9]/', '', $cpf);

    // --- Verifica se email ou CPF já estão cadastrados ---
    $sql_check = "SELECT id FROM professores WHERE email = ? OR cpf = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ss", $email, $cpf_limpo); // "ss" = dois parâmetros string
    $stmt_check->execute();
    $stmt_check->store_result(); // Necessário para usar num_rows

    if ($stmt_check->num_rows > 0) {
        // Se já existe, define mensagem de erro na sessão
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => "Email ou CPF já cadastrado."
        ];
        header("Location: cadastro_professor.php"); // Redireciona
        exit();
    } else {
        // --- Insere novo professor ---
        $sql_insert = "INSERT INTO professores (nome, email, cpf, senha) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);

        // Associa parâmetros à query
        $stmt_insert->bind_param("ssss", $nome, $email, $cpf_limpo, $senha);

        // Executa a query e verifica sucesso ou falha
        if ($stmt_insert->execute()) {
            // Mensagem de sucesso
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => "Professor cadastrado com sucesso!"
            ];
            header("Location: cadastro_professor.php");
            exit();
        } else {
            // Mensagem de erro
            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => "Erro ao cadastrar professor."
            ];
            header("Location: cadastro_professor.php");
            exit();
        }

        // Fecha statement de insert
        $stmt_insert->close();
    }

    // Fecha statement de check
    $stmt_check->close();
}

// Fecha conexão com o banco
$conn->close();
