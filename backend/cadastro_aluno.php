<?php
// Inicia a sessão PHP
// Necessário para acessar variáveis de sessão ($_SESSION)
// Sessões permitem manter dados do usuário entre páginas, como login
session_start();

// Verifica se o professor está logado
// Se não houver 'professor_id' na sessão, significa que o usuário não autenticou
if (!isset($_SESSION['professor_id'])) {
    // Redireciona para a página de login
    header("Location: login.php");
    exit(); // Interrompe execução do script para evitar acesso não autorizado
}

// Inclui o arquivo de conexão com o banco de dados
// $conn será a variável que contém a conexão ativa
require '../config.php';
// Verifica se o formulário foi enviado via método POST
// $_SERVER["REQUEST_METHOD"] contém o método HTTP usado (GET, POST, etc)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Recebe os dados enviados pelo formulário
    // $_POST['campo'] acessa os valores do input com 'name="campo"'
    // Recebe dados do POST
    $nome = $_POST['nome'] ?? '';
    $ano = $_POST['ano'] ?? '';
    $sala = $_POST['sala'] ?? '';
    $email = $_POST['email'] ?? '';

    $sala = strtoupper($_POST['sala']);

    // Monta a série
    if (in_array($ano, ['1', '2', '3'])) {
        $serie = $ano . 'º Ano EM ' . $sala;
    } else {
        $serie = $ano . 'º Ano ' . $sala;
    }


    // Valida o formato do email usando filter_var
    // FILTER_VALIDATE_EMAIL retorna false se o email for inválido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Armazena mensagem de erro em sessão para exibir via "toast"
        $_SESSION['toast'] = [
            'type' => 'error', // Tipo da mensagem (error/success)
            'message' => "Erro: Email inválido!"
        ];
        // Redireciona de volta para a página de cadastro
        header("Location: cadastro_aluno.php");
        exit(); // Interrompe o script para evitar execução posterior
    }

    // Linha comentada: criptografia da senha (não usada no momento)
    // password_hash gera hash seguro da senha usando algoritmo padrão do PHP
    // $senha = isset($_POST['senha']) ? password_hash($_POST['senha'], PASSWORD_DEFAULT) : null;

    // Verifica se o email do aluno já existe no banco de dados
    // Usando prepared statement para evitar SQL Injection
    $sql_check = "SELECT id FROM alunos WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email); // "s" = string
    $stmt_check->execute(); // Executa a consulta
    $stmt_check->store_result(); // Armazena resultado para poder verificar número de linhas

    // Se já existe um aluno com esse email
    if ($stmt_check->num_rows > 0) {
        // Mensagem de erro usando sessão e redireciona
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => "Erro: Este email já está cadastrado!"
        ];
        header("Location: cadastro_aluno.php");
        exit();
    } else {
        // Prepara query para inserir os dados do novo aluno
        $sql = "INSERT INTO alunos (nome, serie, email, professor_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $nome, $serie, $email, $_SESSION['professor_id']); // Todos são strings

        // Executa a inserção no banco
        if ($stmt->execute()) {
            // Se deu certo, salva mensagem de sucesso na sessão
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => "Aluno cadastrado com sucesso!"
            ];
            header("Location: cadastro_aluno.php"); // Redireciona
            exit();
        } else {
            // Se houve erro, salva mensagem de erro na sessão
            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => "Erro ao cadastrar aluno!"
            ];
            header("Location: cadastro_aluno.php"); // Redireciona
            exit();
        }
    }
    
    // Fecha statements para liberar recursos
    $stmt_check->close();
    $stmt->close();
}

// Fecha conexão com o banco de dados
$conn->close();
