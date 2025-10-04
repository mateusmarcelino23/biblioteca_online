<?php
// Inicia a sessão PHP para manter dados entre páginas
// Necessário para acessar $_SESSION['professor_id'] e outros dados do usuário
session_start();

// Captura o ID do professor da sessão, ou null caso não esteja definido
$professor_id = $_SESSION['professor_id'] ?? null;

// var_dump($_POST); // Linha comentada, pode ser usada para depurar dados enviados via POST

// Verifica se o professor está logado
// Se não estiver, redireciona para a página de login
if (!isset($_SESSION['professor_id'])) {
    header("Location: ../frontend/login.php"); // Redireciona
    exit(); // Interrompe execução para evitar que código abaixo rode sem login
}

// Inclui arquivo de conexão com banco de dados
require '../config.php';
// Variáveis para armazenar mensagens de notificação (toast)
// $success_message será usada para mensagens de sucesso
// $error_message será usada para mensagens de erro
$success_message = null;
$error_message = null;

// Captura filtros de pesquisa enviados via GET
// trim() remove espaços em branco no início/fim
// Operador ternário simplifica a atribuição padrão
$filtro_aluno = isset($_GET['filtro_aluno']) ? trim($_GET['filtro_aluno']) : '';
$filtro_livro = isset($_GET['filtro_livro']) ? trim($_GET['filtro_livro']) : '';
$filtro_serie = isset($_GET['filtro_serie']) ? trim($_GET['filtro_serie']) : '';

// --- Consulta de alunos com filtros ---
// Começa a query básica
$alunos_query = "SELECT * FROM alunos WHERE 1=1"; // "1=1" permite concatenar condições dinamicamente

// Se houver filtro por nome de aluno, adiciona condição LIKE
if (!empty($filtro_aluno)) {
    $alunos_query .= " AND nome LIKE ?";
}

// Se houver filtro por série, adiciona condição de igualdade
if (!empty($filtro_serie)) {
    $alunos_query .= " AND serie = ?";
}

// Prepara a query usando prepared statement
// Prepared statements protegem contra SQL Injection
$stmt_alunos = $conn->prepare($alunos_query);

// Bind dos parâmetros dependendo de quais filtros foram informados
if (!empty($filtro_aluno) && !empty($filtro_serie)) {
    $aluno_param = "%$filtro_aluno%"; // '%' para busca parcial LIKE
    $stmt_alunos->bind_param("ss", $aluno_param, $filtro_serie);
} elseif (!empty($filtro_aluno)) {
    $aluno_param = "%$filtro_aluno%";
    $stmt_alunos->bind_param("s", $aluno_param);
} elseif (!empty($filtro_serie)) {
    $stmt_alunos->bind_param("s", $filtro_serie);
}

// Executa a query e pega os resultados
$stmt_alunos->execute();
$alunos_result = $stmt_alunos->get_result(); // Retorna um objeto result para iteração

// --- Consulta de livros com filtros ---
// Query básica de livros
$livros_query = "SELECT * FROM livros WHERE 1=1";

// Se houver filtro de título ou ISBN
if (!empty($filtro_livro)) {
    $livros_query .= " AND (titulo LIKE ? OR isbn LIKE ?)";
}

// Prepara statement
$stmt_livros = $conn->prepare($livros_query);

// Bind dos parâmetros de filtro
if (!empty($filtro_livro)) {
    $livro_param = "%$filtro_livro%";
    $stmt_livros->bind_param("ss", $livro_param, $livro_param);
}

// Executa query e pega resultados
$stmt_livros->execute();
$livros_result = $stmt_livros->get_result();

// Consulta séries distintas para popular filtro de série
$series_query = "SELECT DISTINCT serie FROM alunos ORDER BY serie";
$series_result = $conn->query($series_query);

// --- Processamento do formulário de empréstimo ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validação de entrada usando filter_input
    // FILTER_VALIDATE_INT garante que recebemos números inteiros válidos
    $aluno_id = filter_input(INPUT_POST, 'aluno_id', FILTER_VALIDATE_INT);
    $livro_id = filter_input(INPUT_POST, 'livro_id', FILTER_VALIDATE_INT);
    $data_emprestimo = filter_input(INPUT_POST, 'data_emprestimo'); // Data enviada em formato string
    $data_devolucao = filter_input(INPUT_POST, 'data_devolucao');

    // Se algum dado for inválido, retorna erro
    if (!$aluno_id || !$livro_id || !$data_emprestimo || !$data_devolucao) {
        $error_message = "Dados inválidos fornecidos!";
    } else {
        // Verifica se o livro está disponível
        $livro_check_sql = "SELECT quantidade FROM livros WHERE id = ?";
        $stmt = $conn->prepare($livro_check_sql);
        $stmt->bind_param("i", $livro_id);
        $stmt->execute();
        $stmt->bind_result($quantidade);
        $stmt->fetch();
        $stmt->close();

        if ($quantidade > 0) { // Se há estoque disponível
            $conn->begin_transaction(); // Inicia transação para manter consistência

            try {
                // Insere o empréstimo na tabela
                $sql = "INSERT INTO emprestimos (aluno_id, livro_id, data_emprestimo, data_devolucao, devolvido, professor_id)
                        VALUES (?, ?, ?, ?, 0, ?)";
                $stmt_emprestimo = $conn->prepare($sql);
                $stmt_emprestimo->bind_param("iissi", $aluno_id, $livro_id, $data_emprestimo, $data_devolucao, $professor_id);

                if ($stmt_emprestimo->execute()) {
                    // Atualiza estoque do livro subtraindo 1
                    $sql_update = "UPDATE livros SET quantidade = quantidade - 1 WHERE id = ?";
                    $stmt_update = $conn->prepare($sql_update);
                    $stmt_update->bind_param("i", $livro_id);
                    $stmt_update->execute();

                    $conn->commit(); // Confirma transação
                    $success_message = "Empréstimo registrado com sucesso!";
                } else {
                    throw new Exception("Erro ao registrar empréstimo!"); // Lança erro se insert falhar
                }
            } catch (Exception $e) {
                $conn->rollback(); // Reverte alterações se algo der errado
                $error_message = $e->getMessage(); // Mensagem de erro para o usuário
            }

            // Fecha statements se foram criados
            if (isset($stmt_emprestimo)) $stmt_emprestimo->close();
            if (isset($stmt_update)) $stmt_update->close();
        } else {
            $error_message = "Este livro não está disponível no momento!";
        }
    }
}

// Fecha statements e conexão para liberar recursos
if (isset($stmt_alunos)) $stmt_alunos->close();
if (isset($stmt_livros)) $stmt_livros->close();
$conn->close();
