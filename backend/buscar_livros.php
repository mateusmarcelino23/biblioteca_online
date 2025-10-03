<?php
// Inicia a sessão PHP
// Necessário para acessar variáveis de sessão como $_SESSION['professor_id']
// Sessões permitem manter dados do usuário entre páginas, como login e mensagens
session_start();

// Verifica se o professor está logado
// Se não estiver, redireciona para a página de login
if (!isset($_SESSION['professor_id'])) {
    header("Location: login.php"); // Redirecionamento
    exit(); // Interrompe a execução para impedir acesso não autorizado
}

// Inclui arquivo de conexão com banco de dados
// $conn será usado para todas as operações SQL
require '../includes/conn.php';

// --- Configuração de logs (comentada) ---
// Permite registrar logs de processamento para auditoria ou depuração
// $log_dir = __DIR__ . '/../logs/'; // Caminho absoluto da pasta logs
// if (!file_exists($log_dir)) mkdir($log_dir, 0755, true); // Cria pasta se não existir
// $log_file = $log_dir . 'livros_log_' . date('Y-m-d') . '.txt';

// Função registrar_log comentada
// Poderia registrar mensagens com timestamp e detalhes do processamento
// function registrar_log($mensagem, $detalhes = null) { ... }

// --- Função para buscar livros no Google Books pela API ---
function buscarLivroGoogle($termo)
{
    try {
        // Monta a URL da API com o termo de busca
        $url = 'https://www.googleapis.com/books/v1/volumes?q=' . urlencode($termo);

        // Tenta obter os dados via HTTP GET
        // O @ silencia warnings, caso a URL falhe
        $response = @file_get_contents($url);

        // Se falhou, lança exceção
        if ($response === false) {
            throw new Exception("Não foi possível acessar a API do Google Books.");
        }

        // Decodifica JSON para array associativo
        return json_decode($response, true);
    } catch (Exception $e) {
        // Retorna array vazio em caso de erro
        return [];
    }
}

// Função para buscar detalhes de um livro específico pelo ID na API do Google
function buscarDetalhesLivroGoogle($id_livro)
{
    $url = 'https://www.googleapis.com/books/v1/volumes/' . $id_livro;
    $response = file_get_contents($url);
    return json_decode($response, true);
}

// Função para formatar mensagens de erro do MySQL de forma amigável
function formatarErroBanco($erro)
{
    // Caso seja registro duplicado
    if (strpos($erro, "Duplicate entry") !== false) {
        if (strpos($erro, "isbn") !== false) {
            return "Já existe um livro com este ISBN cadastrado.";
        }
        return "Registro duplicado: este livro já existe no sistema.";
    }
    // Caso algum dado seja muito grande para a coluna
    if (strpos($erro, "Data too long") !== false) {
        return "Dados muito longos para algum campo. Verifique especialmente a URL da capa.";
    }
    // Para outros tipos de erro
    return "Erro no banco de dados: " . $erro;
}

// --- Processamento do formulário de cadastro de livros ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica se algum livro foi selecionado no POST
    if (isset($_POST['livros']) && !empty($_POST['livros'])) {
        $livros_selecionados = $_POST['livros']; // Array de IDs de livros
        $erros = []; // Array para armazenar erros de cada livro
        $sucessos = 0; // Contador de livros cadastrados com sucesso
        $livros_com_erro = []; // Detalhes dos livros com erro

        // Loop para processar cada livro selecionado
        foreach ($livros_selecionados as $id_livro) {
            try {
                // Busca detalhes do livro na API
                $livro = buscarDetalhesLivroGoogle($id_livro);

                // Dados do livro com fallback caso algum campo esteja ausente
                $titulo = $livro['volumeInfo']['title'] ?? 'Título Desconhecido';
                $autor = isset($livro['volumeInfo']['authors']) ? implode(', ', $livro['volumeInfo']['authors']) : 'Autor Desconhecido';
                $isbn = $livro['volumeInfo']['industryIdentifiers'][0]['identifier'] ?? 'ISBN Desconhecido';
                $capa_url = $livro['volumeInfo']['imageLinks']['thumbnail'] ?? 'sem_capa.png';
                $preview_link = $livro['volumeInfo']['previewLink'] ?? NULL;
                $descricao = $livro['volumeInfo']['description'] ?? NULL;
                $categoria = $livro['volumeInfo']['categories'][0] ?? NULL;
                $ano_publicacao = substr($livro['volumeInfo']['publishedDate'], 0, 4) ?? NULL;
                $genero = $livro['volumeInfo']['categories'][0] ?? NULL;
                $quantidade = 1; // Quantidade padrão

                // Validação básica: ISBN obrigatório
                if (empty($isbn)) {
                    throw new Exception("ISBN não encontrado para o livro '$titulo'");
                }

                // Query para inserir livro no banco de dados
                $sql = "INSERT INTO livros (titulo, autor, isbn, capa_url, descricao, categoria, ano_publicacao, genero, quantidade, preview_link)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                // Prepara a query
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Erro ao preparar query: " . $conn->error);
                }

                // Associa parâmetros com tipos corretos
                // s = string, i = integer
                $stmt->bind_param("ssssssssis", $titulo, $autor, $isbn, $capa_url, $descricao, $categoria, $ano_publicacao, $genero, $quantidade, $preview_link);

                // Executa a query
                if ($stmt->execute()) {
                    $sucessos++; // Incrementa contador de sucesso
                } else {
                    // Formata o erro para exibir mensagem amigável
                    $erro_formatado = formatarErroBanco($stmt->error);
                    $erros[] = "$titulo: " . $erro_formatado;
                    $livros_com_erro[] = [
                        'titulo' => $titulo,
                        'isbn' => $isbn,
                        'erro' => $stmt->error
                    ];
                }
            } catch (Exception $e) {
                // Captura exceções e adiciona nos arrays de erro
                $erros[] = "Erro ao processar livro: " . $e->getMessage();
                $livros_com_erro[] = [
                    'id' => $id_livro,
                    'erro' => $e->getMessage()
                ];
            }
        }

        // Mensagens de feedback ao usuário via sessão
        if (empty($erros)) {
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => "$sucessos livro(s) cadastrado(s) com sucesso!"
            ];
            header("Location: buscar_livros.php");
        } else {
            $mensagem = "$sucessos livro(s) cadastrado(s) com sucesso, mas alguns apresentaram erros.";
            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => $mensagem
            ];
            // Armazena detalhes de erros e sucessos na sessão
            $_SESSION['erros'] = $erros;
            $_SESSION['sucessos'] = $sucessos;
            $_SESSION['livros_com_erro'] = $livros_com_erro;
            header("Location: buscar_livros.php");
        }
        exit(); // Interrompe execução após redirecionamento
    } else {
        // Nenhum livro selecionado
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => "Nenhum livro selecionado para cadastro."
        ];
        header("Location: buscar_livros.php");
        exit();
    }
}

// --- Processamento do formulário de busca (GET) ---
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['termo_busca'])) {
    $termo_busca = $_GET['termo_busca']; // Termo digitado pelo usuário
    $livros = buscarLivroGoogle($termo_busca); // Busca na API do Google
}
