<?php
session_start();

// Verifica se o professor está logado, se não, redireciona para login
if (!isset($_SESSION['professor_id'])) {
    header("Location: login.php");
    exit();
}

/* chama o arquivo que faz a conexão com o banco de dados pra usar ele */
require '../includes/conn.php';

// verifica se o formulário foi enviado por post
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // recebe os dados que foram enviados do formulário
    $nome = $_POST['nome'];
    $serie = $_POST['serie'];
    $email = $_POST['email'];

    // valida o formado do email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => "Erro: Email inválido!"
        ];
        header("Location: cadastro_aluno.php");
        exit();
    }

    // essa linha só criptografa a senha do aluno (que pedia antes no formulário, mas eu tirei)
    // $senha = isset($_POST['senha']) ? password_hash($_POST['senha'], PASSWORD_DEFAULT) : null;

    // verifica se o email do aluno já está cadastrado
    $sql_check = "SELECT id FROM alunos WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // Mensagem de erro e redireciona
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => "Erro: Este email já está cadastrado!"
        ];
        header("Location: cadastro_aluno.php");
        exit();
    } else {
        // prepara a consulta pra cadastrar os dados do aluno
        $sql = "INSERT INTO alunos (nome, serie, email) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $nome, $serie, $email);

        // executa a consulta
        if ($stmt->execute()) {
            // Mensagem de sucesso e redireciona
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => "Aluno cadastrado com sucesso!"
            ];
            header("Location: cadastro_aluno.php");
            exit();
        } else {
            // Mensagem de erro e redireciona
            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => "Erro ao cadastrar aluno!"
            ];
            header("Location: cadastro_aluno.php");
            exit();
        }
    }

    $stmt_check->close();
    $stmt->close();
}

$conn->close();
?>
