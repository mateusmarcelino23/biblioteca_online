<?php
/*
Essa página eu não uso mais, servia pra comunicação do professor com os alunos dentro do próprio site
*/
session_start();

// verifica se o professor tá logado
if (!isset($_SESSION['professor_id'])) {
    header("Location: ../frontend/login.php");
    exit();
}

require '../includes/conn.php';

// armazena o id do professor nessa variável
$professor_id = $_SESSION['professor_id'];

$erro = $sucesso = "";

//verifica se o formulário foi enviado via post
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // recebe os dados do formulário
    $aluno_id = $_POST['aluno_id'];
    $mensagem = $_POST['mensagem'];
    $data_envio = date('Y-m-d H:i:s');
    
    // isso aqui era pra aceitar imagens no chat
    $imagem_url = "";
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $diretorio = "_uploads/";
        $arquivo_nome = $_FILES['imagem']['name'];
        $arquivo_tmp = $_FILES['imagem']['tmp_name'];
        $extensao = pathinfo($arquivo_nome, PATHINFO_EXTENSION);
        $novo_nome = uniqid() . '.' . $extensao;
        
        if (in_array(strtolower($extensao), ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($arquivo_tmp, $diretorio . $novo_nome)) {
                $imagem_url = $diretorio . $novo_nome;
            } else {
                $erro = "Erro ao enviar a imagem!";
            }
        } else {
            $erro = "Formato de imagem inválido!";
        }
    }

    if (empty($mensagem)) {
        $erro = "A mensagem não pode estar vazia!";
    } else {
        // nem eu sei o que isso faz, foi o mano chatgpt que fez (assim como quase tudo o resto)
        $mensagem = preg_replace("/(http:\/\/|https:\/\/)([a-zA-Z0-9\-\._~\/?#[\]@!$&'()*+,;=]*[a-zA-Z0-9\-\._~\/?#[\]@!$&'()*+,;=])/", "<a href='$0' target='_blank'>$0</a>", $mensagem);

        $sql = "INSERT INTO mensagens (aluno_id, professor_id, mensagem, data_envio, imagem_url) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisss", $aluno_id, $professor_id, $mensagem, $data_envio, $imagem_url);

        if ($stmt->execute()) {
            $sucesso = "Mensagem enviada com sucesso!";
        } else {
            $erro = "Erro ao enviar mensagem!";
        }

        $stmt->close();
    }
}

$sql_alunos = "SELECT id, nome FROM alunos";
$result_alunos = $conn->query($sql_alunos);
?>