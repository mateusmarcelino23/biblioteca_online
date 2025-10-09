<?php
// Inicia a sessão do PHP
// session_start() é obrigatório sempre que queremos acessar ou criar variáveis de sessão
// As sessões permitem manter informações do usuário enquanto ele navega entre páginas
session_start();

// Verifica se o professor está logado
// $_SESSION['professor_id'] foi definido na página de login quando o professor autenticou
// Se não existir, significa que o usuário não está logado
if (!isset($_SESSION['professor_id'])) {
    // Redireciona para a página de login usando header()
    // É essencial usar exit() depois de header() para garantir que o script não continue executando
    header("Location: ../frontend/login.php");
    exit();
}

// Inclui o arquivo de conexão com o banco de dados
// $conn será a variável que contém a conexão ativa
require '../config.php';
// Função para buscar livros na API do Google
// Parâmetros:
// - $termo: o termo de busca (nome ou ISBN)
// - $tipo: 'nome' ou 'isbn', determina como a busca será feita
function buscarLivrosGoogle($termo, $tipo = 'nome')
{         
    // Monta a query da API
    // Se for ISBN, adiciona "isbn:" na frente do termo
    $query = $tipo === 'isbn' ? 'isbn:' . urlencode($termo) : urlencode($termo);
    // URL da API do Google Books com a query adicionada
    $url = 'https://www.googleapis.com/books/v1/volumes?q=' . $query;

    // Faz a requisição HTTP para a API
    // file_get_contents retorna o conteúdo da URL como string
    $response = file_get_contents($url);

    // Converte o JSON retornado pela API em array PHP associativo
    // json_decode($response, true) transforma JSON em array
    $data = json_decode($response, true);

    // Retorna os livros encontrados
    // Se não houver resultados, retorna array vazio para evitar erros
    return $data['items'] ?? [];
}

// Se for uma busca de livros via formulário
// $_SERVER["REQUEST_METHOD"] == "POST" garante que estamos processando um POST
// isset($_POST['buscar']) garante que é o formulário de busca
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['buscar'])) {
    // trim() remove espaços em branco no início e no fim do termo de busca
    $termo_busca = trim($_POST['termo_busca']);
    // Tipo de busca, padrão 'nome' caso não venha do formulário
    $tipo_busca = $_POST['tipo_busca'] ?? 'nome';
    // Chama a função que busca livros na API do Google
    $livros_encontrados = buscarLivrosGoogle($termo_busca, $tipo_busca);
}

// Se for um cadastro de livros via formulário
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cadastrar'])) {
    // Verifica se existem livros selecionados no POST
    if (!empty($_POST['livros'])) {
        // Loop pelos livros selecionados
        foreach ($_POST['livros'] as $livro_id) {
            // Busca informações do livro pelo ISBN na API do Google
            $livro_info = buscarLivrosGoogle($livro_id, 'isbn');
            // Pega o primeiro resultado, se existir
            $livro = $livro_info[0] ?? null;

            if ($livro) { // Se encontrou o livro na API
                // Extrai as informações, com fallback caso algum dado esteja ausente
                $titulo = $livro['volumeInfo']['title'] ?? 'Sem título';
                // authors é um array, implode transforma em string separada por vírgula
                $autor = implode(', ', $livro['volumeInfo']['authors'] ?? ['Desconhecido']);
                $descricao = $livro['volumeInfo']['description'] ?? 'Sem descrição';
                // Pega o primeiro identificador da indústria (geralmente ISBN)
                $isbn = $livro['volumeInfo']['industryIdentifiers'][0]['identifier'] ?? 'Não informado';
                $quantidade = $_POST['qntd']; // Definimos quantidade padrão como 1

                // Verifica se o livro já existe no banco de dados
                $sql_check = "SELECT id FROM livros WHERE isbn = ?";
                // Prepara a query para segurança (evita SQL Injection)
                $stmt_check = $conn->prepare($sql_check);
                // Associa o ISBN ao placeholder ?
                $stmt_check->bind_param("s", $isbn);
                // Executa a consulta
                $stmt_check->execute();
                // Armazena o resultado para poder verificar número de linhas
                $stmt_check->store_result();

                if ($stmt_check->num_rows == 0) { // Se não encontrou, insere
                    // SQL de inserção no banco
                    $sql = "INSERT INTO livros (titulo, autor, descricao, isbn, quantidade) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    // Associa os parâmetros à query, com tipos corretos
                    $stmt->bind_param("ssssi", $titulo, $autor, $descricao, $isbn, $quantidade);
                    // Executa a inserção
                    $stmt->execute();
                }
                // Fecha o statement de verificação para liberar recursos
                $stmt_check->close();
            }
        }
    }
}
