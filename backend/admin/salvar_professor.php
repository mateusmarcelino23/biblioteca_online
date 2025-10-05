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

$admin = $_POST['admin'] ?? 0;

// Se o valor de admin for um array (devido ao input hidden + checkbox), pegar o último valor
if (is_array($admin)) {
    $admin = end($admin);
}

// Garantir que o valor seja 0 ou 1
$admin = ($admin == '1' || $admin === 1) ? 1 : 0;

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

if (empty($senha)) {
    responder(false, 'A senha é obrigatória.');
}

if (strlen($senha) < 6) {
    responder(false, 'A senha deve ter pelo menos 6 caracteres.');
}

// ------------------------
// Verificar se o email já existe
// ------------------------

$stmt = $conn->prepare("SELECT id FROM professores WHERE email = ?");
/*
Query SQL para checar se o email já está cadastrado.
- Usamos prepared statement para evitar SQL Injection.
- Apenas selecionamos 'id' porque não precisamos de mais nada.
*/

$stmt->bind_param("s", $email);
// Vincula o valor do email ao placeholder ? na query

$stmt->execute();
// Executa a query

if ($stmt->get_result()->num_rows > 0) {
    responder(false, 'Este email já está cadastrado.');
    // Se houver algum resultado, o email já existe
}

$stmt->close();
// Fecha o statement anterior para liberar memória

// ------------------------
// Criptografar a senha
// ------------------------

$senha_hash = password_hash($senha, PASSWORD_DEFAULT);
/*
Criptografa a senha usando o algoritmo padrão do PHP (normalmente bcrypt).
- Nunca armazene senhas em texto puro.
- PASSWORD_DEFAULT garante compatibilidade futura.
*/

// ------------------------
// Inserir novo professor no banco
// ------------------------

$sql_insert = "INSERT INTO professores (nome, email, cpf, senha, admin) VALUES (?, ?, ?, ?, ?)";
$stmt_insert = $conn->prepare($sql_insert);

// Associa parâmetros à query
$stmt_insert->bind_param("ssssi", $nome, $email, $cpf, $senha_hash, $admin);
/*
Vincula os valores aos placeholders:
- 's' = string (nome, email, senha_hash)
- 'i' = inteiro (admin)
*/

if ($stmt_insert->execute()) {
    responder(true, 'Professor cadastrado com sucesso!');
    // Retorna sucesso se a inserção ocorreu sem erros
} else {
    responder(false, 'Erro ao cadastrar professor: ' . $stmt_insert->error);
    // Retorna erro detalhando a falha do MySQL
}

$stmt_insert->close();
// Fecha o statement para liberar recursos

$conn->close();
// Fecha a conexão com o banco
