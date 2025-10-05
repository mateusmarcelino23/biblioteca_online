<?php
session_start();
/*
Inicia a sessão do PHP.
- Necessário para acessar $_SESSION e saber se o usuário é administrador.
- Sem isso, não é possível proteger o endpoint.
*/

require_once '../../includes/auth_admin.php';
/*
Inclui arquivo que valida se o usuário logado é administrador.
- Garante que apenas admins possam criar novos professores.
- auth_admin.php normalmente verifica $_SESSION e redireciona se não for admin.
*/

require_once '../../includes/conn.php';
/*
Inclui a conexão com o banco de dados.
- $conn será usado para todas as consultas SQL neste script.
- Sem isso, não seria possível acessar a tabela 'professores'.
*/

header('Content-Type: application/json');
/*
Define que a saída do script será JSON.
- Essencial para requisições AJAX do frontend.
- Sem isso, o retorno poderia ser interpretado como HTML ou texto comum.
*/

// Handle GET request for fetching professor data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'] ?? '';
    if (empty($id)) {
        responder(false, 'ID do professor é obrigatório.');
    }
    $stmt = $conn->prepare("SELECT * FROM professores WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $professor = $result->fetch_assoc();
        echo json_encode(['success' => true, 'professor' => $professor]);
    } else {
        responder(false, 'Professor não encontrado.');
    }
    $stmt->close();
    $conn->close();
    exit;
}

// Função auxiliar para retornar JSON padronizado
function responder($sucesso, $mensagem = '')
{
    /*
    - $sucesso: true ou false indicando se a operação foi bem-sucedida
    - $mensagem: mensagem de retorno legível para o usuário
    - exit(): garante que nada mais será executado após o envio da resposta
    */
    echo json_encode([
        'success' => $sucesso,
        'message' => $mensagem
    ]);
    exit;
}

// ------------------------
// Captura e validação dos dados recebidos via POST
// ------------------------

$nome = trim($_POST['nome'] ?? '');
/*
Recebe o nome do professor.
- trim() remove espaços extras no início e no final.
- ?? '' garante que, se $_POST['nome'] não existir, usamos string vazia.
*/

$email = trim($_POST['email'] ?? '');
/*
Recebe o email do professor.
- Mesma lógica do nome.
*/

$cpf = trim($_POST['cpf'] ?? '');

$senha = trim($_POST['senha'] ?? '');
/*
Recebe a senha do professor.
- Será criptografada antes de salvar no banco.
*/

$id = $_POST['id'] ?? '';

$admin = $_POST['admin'] ?? 0;

// Se o valor de admin for um array (devido ao input hidden + checkbox), pegar o último valor
if (is_array($admin)) {
    $admin = end($admin);
}

// Garantir que o valor seja 0 ou 1
$admin = ($admin == '1' || $admin === 1) ? 1 : 0;

$ativo = $_POST['ativo'] ?? 1;
$ativo = ($ativo == '1' || $ativo === 1) ? 1 : 0;

/*
Define se o professor terá privilégios de administrador.
- Checkbox HTML envia valor apenas se marcado.
- Se marcado = 1 (admin), caso contrário = 0 (professor normal).
*/

// ------------------------
// Validações dos dados
// ------------------------

if (empty($nome)) {
    responder(false, 'O nome é obrigatório.');
}

if (empty($email)) {
    responder(false, 'O email é obrigatório.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    responder(false, 'Email inválido.');
}

if (empty($id) && empty($senha)) {
    responder(false, 'A senha é obrigatória.');
}

if (!empty($senha) && strlen($senha) < 6) {
    responder(false, 'A senha deve ter pelo menos 6 caracteres.');
}

// ------------------------
// Verificar se o email já existe
// ------------------------

if (!empty($id)) {
    $stmt = $conn->prepare("SELECT id FROM professores WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $id);
} else {
    $stmt = $conn->prepare("SELECT id FROM professores WHERE email = ?");
    $stmt->bind_param("s", $email);
}
/*
Query SQL para checar se o email já está cadastrado.
- Usamos prepared statement para evitar SQL Injection.
- Apenas selecionamos 'id' porque não precisamos de mais nada.
- Para update, excluímos o próprio id.
*/

$stmt->execute();
// Executa a query

if ($stmt->get_result()->num_rows > 0) {
    responder(false, 'Este email já está cadastrado.');
    // Se houver algum resultado, o email já existe
}

$stmt->close();
// Fecha o statement anterior para liberar memória

// ------------------------
// Criptografar a senha se fornecida
// ------------------------

$senha_hash = !empty($senha) ? password_hash($senha, PASSWORD_DEFAULT) : '';
/*
Criptografa a senha usando o algoritmo padrão do PHP (normalmente bcrypt).
- Nunca armazene senhas em texto puro.
- PASSWORD_DEFAULT garante compatibilidade futura.
- Para update, só criptografa se senha fornecida.
*/

// ------------------------
// Inserir ou atualizar professor no banco
// ------------------------

if (!empty($id)) {
    // Update
    if (!empty($senha)) {
        $sql = "UPDATE professores SET nome = ?, email = ?, cpf = ?, senha = ?, admin = ?, ativo = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssiii", $nome, $email, $cpf, $senha_hash, $admin, $ativo, $id);
    } else {
        $sql = "UPDATE professores SET nome = ?, email = ?, cpf = ?, admin = ?, ativo = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssiii", $nome, $email, $cpf, $admin, $ativo, $id);
    }

    if ($stmt->execute()) {
        responder(true, 'Professor atualizado com sucesso!');
    } else {
        responder(false, 'Erro ao atualizar professor: ' . $stmt->error);
    }
} else {
    // Insert
    $sql_insert = "INSERT INTO professores (nome, email, cpf, senha, admin, ativo) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ssssii", $nome, $email, $cpf, $senha_hash, $admin, $ativo);

    if ($stmt_insert->execute()) {
        responder(true, 'Professor cadastrado com sucesso!');
    } else {
        responder(false, 'Erro ao cadastrar professor: ' . $stmt_insert->error);
    }
    $stmt_insert->close();
}

$conn->close();
// Fecha a conexão com o banco
