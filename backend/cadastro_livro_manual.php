<?php
include '../includes/conn.php';

// $quantidade = 1;
// verifica se o formulário foi enviado com post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // recebe os dados do formulário
    $titulo = $_POST['titulo'] ?? '';
    $autor = $_POST['autor'] ?? '';
    $isbn = $_POST['isbn'] ?? '';
    $genero = $_POST['genero'] ?? '';
    $ano_publicacao = $_POST['ano_publicacao'] ?? '';
    // quantidade padrão de livros, coloquei aqui pra não precisar vir do formulário
    $quantidade = 1;


    // Validação simples
    if (empty($titulo)) {
        echo "Preencha ao menos o título do livro.";
        exit;
    }

    // tenta preparar a consulta
    try {
        $stmt = $conn->prepare("INSERT INTO livros (titulo, autor, isbn, genero, ano_publicacao, quantidade) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $titulo, $autor, $isbn, $genero, $ano_publicacao, $quantidade);
    } catch (Exception $e) {
        echo "O irmão deu merda ai o" . $e->getMessage();
    }

    // executa a consulta
    if ($stmt->execute()) {
        echo "Livro cadastrado com sucesso!";
        echo "Você pode ver o livro em <a href='../frontend/visualizar_livros.php'>Visualizar Livros.</a>.";
    } else {
        echo "Erro ao cadastrar livro: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
