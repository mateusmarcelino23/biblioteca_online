<?php
include '../includes/conn.php';

// $quantidade = 1;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $autor = $_POST['autor'] ?? '';
    $isbn = $_POST['isbn'] ?? '';
    $genero = $_POST['genero'] ?? '';
    $ano_publicacao = $_POST['ano_publicacao'] ?? '';
    $quantidade = 1;


    // Validação simples
    if (empty($titulo)) {
        echo "Preencha ao menos o título do livro.";
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO livros (titulo, autor, isbn, genero, ano_publicacao, quantidade) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $titulo, $autor, $isbn, $genero, $ano_publicacao, $quantidade);
    } catch (Exception $e) {
        echo "O irmão deu merda ai o" . $e->getMessage();
    }

    // Insere o livro no banco de dados
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
