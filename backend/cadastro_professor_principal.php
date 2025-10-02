<?php
// Inicia sessão PHP
// Necessário para armazenar mensagens de erro/sucesso e dados temporários do usuário
session_start();

// Inclui arquivo de conexão com banco de dados
// $conn será a variável usada para todas operações SQL
include '../includes/conn.php';

// --- Função para validar CPF ---
function validarCPF($cpf)
{
    // Remove tudo que não for número
    // Assim podemos receber CPF com pontos e traços e ainda validar
    $cpf = preg_replace('/[^0-9]/', '', $cpf);

    // CPF precisa ter exatamente 11 dígitos
    if (strlen($cpf) != 11) {
        return false;
    }

    // Elimina CPFs inválidos como 111.111.111-11 ou 222.222.222-22
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }

    // Calcula os dois dígitos verificadores do CPF
    for ($t = 9; $t < 11; $t++) {
        $soma = 0;
        for ($i = 0; $i < $t; $i++) {
            // Multiplica cada dígito pelo peso decrescente
            $soma += $cpf[$i] * (($t + 1) - $i);
        }

        // Calcula o dígito
        $digito = (10 * $soma) % 11;
        $digito = ($digito == 10) ? 0 : $digito;

        // Verifica se dígito bate com o CPF
        if ($cpf[$t] != $digito) {
            return false;
        }
    }

    // Se passou por todas as verificações, CPF é válido
    return true;
}

// --- Processamento do formulário ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Recebe e limpa os dados do formulário
    $nome = trim($_POST['nome']); // trim remove espaços extras
    $email = trim($_POST['email']);
    $cpf = trim($_POST['cpf']);
    $senha = $_POST['senha'];
    $codigo_secreto = trim($_POST['codigo_secreto'] ?? ''); // campo opcional

    // Validações básicas: campos obrigatórios
    if (empty($nome) || empty($email) || empty($cpf) || empty($senha)) {
        $_SESSION['erro_cadastro'] = 'Todos os campos são obrigatórios!';
        header("Location: ../frontend/cadastro_professor_principal.php");
        exit(); // Interrompe execução após redirecionamento
    }

    // --- Código secreto para admin ---
    $is_admin = 0; // padrão: não é admin
    if (!empty($codigo_secreto)) {
        // Consulta o código secreto armazenado no banco
        $stmt = $conn->prepare("SELECT secret_code FROM admin_settings LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        // Verifica se o código fornecido é correto
        if ($codigo_secreto === $row['secret_code']) {
            $is_admin = 1; // Usuário será admin
        } else {
            $_SESSION['erro_cadastro'] = 'Código secreto inválido!';
            header("Location: ../frontend/cadastro_professor_principal.php");
            exit();
        }
    }

    // --- Criptografia da senha ---
    // password_hash cria hash seguro para armazenar no banco
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // --- Preparação da consulta para inserir professor ---
    $stmt = $conn->prepare("INSERT INTO professores (nome, email, cpf, senha, ativo, admin) VALUES (?, ?, ?, ?, 1, ?)");
    // 'ssssi' significa: string, string, string, string, integer
    $stmt->bind_param("ssssi", $nome, $email, $cpf, $senha_hash, $is_admin);

    // --- Execução da consulta ---
    if ($stmt->execute()) {
        // Sucesso: define mensagem na sessão e redireciona para login
        $_SESSION['sucesso_cadastro'] = 'Cadastro realizado com sucesso! Você já pode fazer login.';
        header("Location: ../frontend/login.php");
    } else {
        // Falha: define mensagem de erro na sessão e redireciona
        $_SESSION['erro_cadastro'] = 'Erro ao cadastrar: ' . $conn->error;
        header("Location: ../frontend/cadastro_professor_principal.php");
    }

    // Fecha statement e conexão com banco
    $stmt->close();
    $conn->close();
    exit(); // Interrompe execução do script
}
