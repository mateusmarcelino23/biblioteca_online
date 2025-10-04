<?php
// Inclui o arquivo de conexão com o banco de dados
// Isso permite que a variável $conn exista aqui e possamos usar ela para consultas SQL
// '../includes/conn.php' é o caminho relativo do arquivo de conexão
require '../config.php';
// $quantidade = 1;
// Essa linha foi comentada, mas seria usada para definir uma quantidade padrão de livros
// No final, você definiu $quantidade mais abaixo, então não é necessária aqui

// Verifica se o formulário foi enviado usando o método POST
// $_SERVER['REQUEST_METHOD'] é uma variável superglobal do PHP que contém o método HTTP usado na requisição
// Aqui estamos checando se o formulário foi enviado com POST para processar os dados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    Recebe os dados do formulário.
    $_POST é uma superglobal que captura os dados enviados via POST.
    Cada campo do formulário é acessado pelo seu 'name'.
    O operador ?? é o "null coalescing", que verifica se a variável existe e não é nula.
    Se o campo não existir ou estiver vazio, ele define como string vazia ''.
    Isso previne erros de "undefined index".
    */
    $titulo = $_POST['titulo'] ?? '';          // Título do livro
    $autor = $_POST['autor'] ?? '';            // Autor do livro
    $isbn = $_POST['isbn'] ?? '';              // ISBN do livro
    $genero = $_POST['genero'] ?? '';          // Gênero do livro
    $ano_publicacao = $_POST['ano_publicacao'] ?? ''; // Ano de publicação

    // Quantidade padrão de livros
    // Aqui definimos manualmente a quantidade como 1, então não precisamos que o usuário informe
    $quantidade = 1;

    // Validação simples para garantir que o título não esteja vazio
    // empty() retorna true se a variável estiver vazia ou não existir
    if (empty($titulo)) {
        // Exibe uma mensagem de erro e encerra a execução do script
        echo "Preencha ao menos o título do livro.";
        exit; // interrompe o script para evitar que dados inválidos sejam enviados ao banco
    }

    // Tenta preparar a consulta SQL usando um bloco try/catch
    // Try/catch serve para capturar erros e evitar que o script quebre completamente
    try {
        /*
        Prepara a query SQL para inserir os dados na tabela 'livros'.
        ? são placeholders que serão substituídos pelos valores reais de forma segura.
        Isso evita SQL Injection, pois os valores são tratados como dados e não como código SQL.
        */
        $stmt = $conn->prepare("INSERT INTO livros (titulo, autor, isbn, genero, ano_publicacao, quantidade) VALUES (?, ?, ?, ?, ?, ?)");

        /*
        bind_param() associa as variáveis PHP aos placeholders (?) na query.
        Tipos usados:
        's' = string
        'i' = integer
        Ordem dos parâmetros importa e deve corresponder à ordem dos ?
        */
        $stmt->bind_param("sssssi", $titulo, $autor, $isbn, $genero, $ano_publicacao, $quantidade);

    } catch (Exception $e) {
        // Caso haja algum erro ao preparar a consulta, captura a exceção e exibe a mensagem
        echo "O irmão deu merda ai o" . $e->getMessage();
        // $e->getMessage() mostra o detalhe do erro, muito útil para debugging
    }

    // Executa a consulta SQL
    if ($stmt->execute()) {
        // Se der certo, exibe mensagem de sucesso
        echo "Livro cadastrado com sucesso!";
        // Link para visualizar os livros cadastrados
        echo "Você pode ver o livro em <a href='../frontend/visualizar_livros.php'>Visualizar Livros.</a>.";
    } else {
        // Caso a execução falhe, exibe a mensagem de erro
        // $stmt->error mostra o erro gerado pelo MySQL
        echo "Erro ao cadastrar livro: " . $stmt->error;
    }

    // Fecha o statement para liberar recursos
    $stmt->close();
}

// Fecha a conexão com o banco
// É uma boa prática fechar a conexão quando não precisamos mais dela
$conn->close();
?>
