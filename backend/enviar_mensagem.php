<?php
/*
Essa página atualmente não é usada.
Originalmente servia para comunicação do professor com os alunos dentro do site.
*/
session_start(); // Inicia sessão para acessar dados do professor logado

// --- Verifica se o professor está logado ---
if (!isset($_SESSION['professor_id'])) {
    // Se não estiver logado, redireciona para a página de login
    header("Location: ../frontend/login.php");
    exit();
}

// Inclui arquivo de conexão com o banco
require '../includes/conn.php';

// Armazena o ID do professor logado em uma variável para usar em consultas
$professor_id = $_SESSION['professor_id'];

// Variáveis para armazenar mensagens de erro ou sucesso
$erro = $sucesso = "";

// --- Verifica se o formulário foi enviado ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recebe dados enviados pelo formulário
    $aluno_id = $_POST['aluno_id']; // ID do aluno que vai receber a mensagem
    $mensagem = $_POST['mensagem']; // Texto da mensagem
    $data_envio = date('Y-m-d H:i:s'); // Data e hora atual

    // --- Processamento de imagem (opcional) ---
    $imagem_url = ""; // Inicializa variável
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $diretorio = "_uploads/"; // Pasta onde a imagem será salva
        $arquivo_nome = $_FILES['imagem']['name']; // Nome original do arquivo
        $arquivo_tmp = $_FILES['imagem']['tmp_name']; // Caminho temporário
        $extensao = pathinfo($arquivo_nome, PATHINFO_EXTENSION); // Extensão do arquivo
        $novo_nome = uniqid() . '.' . $extensao; // Gera nome único para evitar conflitos

        // Verifica se a extensão é válida
        if (in_array(strtolower($extensao), ['jpg', 'jpeg', 'png', 'gif'])) {
            // Move o arquivo para a pasta destino
            if (move_uploaded_file($arquivo_tmp, $diretorio . $novo_nome)) {
                $imagem_url = $diretorio . $novo_nome; // Salva caminho da imagem
            } else {
                $erro = "Erro ao enviar a imagem!";
            }
        } else {
            $erro = "Formato de imagem inválido!";
        }
    }

    // --- Validação da mensagem ---
    if (empty($mensagem)) {
        $erro = "A mensagem não pode estar vazia!";
    } else {
        // Transforma URLs em links clicáveis
        // Regex detecta URLs iniciadas com http:// ou https://
        $mensagem = preg_replace(
            "/(http:\/\/|https:\/\/)([a-zA-Z0-9\-\._~\/?#[\]@!$&'()*+,;=]*[a-zA-Z0-9\-\._~\/?#[\]@!$&'()*+,;=])/",
            "<a href='$0' target='_blank'>$0</a>",
            $mensagem
        );

        // --- Insere a mensagem no banco ---
        $sql = "INSERT INTO mensagens (aluno_id, professor_id, mensagem, data_envio, imagem_url) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql); // Prepared statement para segurança
        $stmt->bind_param("iisss", $aluno_id, $professor_id, $mensagem, $data_envio, $imagem_url);

        // Executa a query e verifica sucesso ou falha
        if ($stmt->execute()) {
            $sucesso = "Mensagem enviada com sucesso!";
        } else {
            $erro = "Erro ao enviar mensagem!";
        }

        $stmt->close(); // Fecha statement
    }
}

// --- Busca todos os alunos para exibir no formulário ---
// Poderia ser usado em um select para escolher destinatário da mensagem
$sql_alunos = "SELECT id, nome FROM alunos";
$result_alunos = $conn->query($sql_alunos); // Executa query e retorna objeto MySQLi
