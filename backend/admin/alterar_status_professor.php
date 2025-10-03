<?php
session_start(); // Inicia a sessão para poder acessar variáveis de sessão (como dados do admin logado)

require_once '../../includes/auth_admin.php'; // Inclui script que verifica se o usuário é admin (proteção de acesso)
require_once '../../includes/conn.php'; // Inclui a conexão com o banco de dados

header('Content-Type: application/json');
// Define que a resposta será JSON, útil para chamadas AJAX ou APIs, para o frontend interpretar corretamente

// Função auxiliar para responder JSON e encerrar o script
function responder($sucesso, $mensagem = '')
{
    echo json_encode([
        'success' => $sucesso, // true ou false
        'message' => $mensagem // mensagem opcional de retorno
    ]);
    exit; // Interrompe a execução após enviar a resposta
}

// Recebe e valida os dados enviados via POST
$id = intval($_POST['id'] ?? 0); // ID do professor, convertido para inteiro. Se não existir, 0
$status = intval($_POST['status'] ?? 0); // Status ativo/inativo, convertido para inteiro. Se não existir, 0

if ($id <= 0) { // Validação simples do ID
    responder(false, 'ID do professor inválido.'); // Retorna erro se o ID for inválido
}

// Verifica se o professor existe no banco de dados
$stmt = $conn->prepare("SELECT id FROM professores WHERE id = ?"); // Prepara a query segura
$stmt->bind_param("i", $id); // Vincula o parâmetro do tipo inteiro
$stmt->execute(); // Executa a query
if ($stmt->get_result()->num_rows === 0) { // Se não encontrar nenhum registro
    responder(false, 'Professor não encontrado.'); // Retorna erro
}
$stmt->close(); // Fecha o statement

// Atualiza o status do professor (ativo/inativo)
$stmt = $conn->prepare("UPDATE professores SET ativo = ? WHERE id = ?"); // Prepara query de atualização
$stmt->bind_param("ii", $status, $id); // Vincula os parâmetros: status e id (ambos inteiros)

if ($stmt->execute()) { // Executa a atualização
    responder(true, 'Status do professor atualizado com sucesso!'); // Retorna sucesso
} else {
    responder(false, 'Erro ao atualizar status do professor: ' . $conn->error);
    // Retorna erro detalhado caso falhe
}

$stmt->close(); // Fecha o statement
$conn->close(); // Fecha a conexão com o banco
