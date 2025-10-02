<?php
session_start();
/*
Inicia a sessão do PHP.
- Sem isso, $_SESSION não funciona.
- Precisamos disso para verificar se o usuário está logado como administrador.
- Também é necessário para registrar o usuário que está alterando as configurações.
*/

require_once '../../includes/auth_admin.php';
/*
Inclui o arquivo que garante que apenas administradores podem acessar este script.
- auth_admin.php deve verificar a sessão do usuário.
- Se o usuário não for admin, normalmente redireciona ou encerra a execução.
- Protege endpoints críticos, como este que altera configurações do sistema.
*/

require_once '../../includes/conn.php';
/*
Inclui a conexão com o banco de dados.
- $conn será usado para todas as consultas SQL neste script.
- Sem isso, não seria possível atualizar as configurações no banco.
*/

header('Content-Type: application/json');
/*
Define que o conteúdo retornado pelo script será JSON.
- Essencial para requisições AJAX.
- Sem isso, o frontend pode interpretar o retorno como HTML, quebrando o fluxo.
- O cabeçalho deve ser enviado antes de qualquer saída (echo).
*/

function responder($sucesso, $mensagem = '')
{
    /*
    Função auxiliar para enviar resposta padronizada em JSON e encerrar execução.
    - $sucesso: boolean, indica se a operação foi bem-sucedida.
    - $mensagem: string, mensagem de retorno para o usuário.
    - exit: garante que nada mais será executado após a resposta.
    */
    echo json_encode([
        'success' => $sucesso, // true se operação bem-sucedida, false caso contrário
        'message' => $mensagem  // mensagem de retorno legível
    ]);
    exit; // termina imediatamente a execução do script
}

// ------------------------
// Captura dos dados enviados via POST
// ------------------------

$dias_emprestimo = intval($_POST['dias_emprestimo'] ?? 0);
/*
Dias padrão para empréstimo de livros.
- intval garante que sempre será um inteiro.
- Se o usuário não enviar nada, assume 0 como padrão.
*/

$max_livros_aluno = intval($_POST['max_livros_aluno'] ?? 0);
/*
Número máximo de livros que um aluno pode pegar simultaneamente.
- Se o valor estiver fora do limite definido mais abaixo, será rejeitado.
*/

$max_renovacoes = intval($_POST['max_renovacoes'] ?? 0);
/*
Número máximo de renovações de empréstimo que um aluno pode fazer por livro.
- Valores negativos não fazem sentido, então serão validados.
*/

$multa_dia_atraso = floatval($_POST['multa_dia_atraso'] ?? 0);
/*
Valor da multa por dia de atraso.
- floatval transforma a entrada em número decimal.
- Valores negativos não são permitidos.
*/

$backup_automatico = isset($_POST['backup_automatico']) ? 1 : 0;
/*
Define se o backup automático está ativado ou não.
- Checkbox HTML envia valor apenas se estiver marcado.
- Se marcado, 1 (ativo); se não, 0 (desativado).
*/

$email_notificacao = filter_var($_POST['email_notificacao'] ?? '', FILTER_VALIDATE_EMAIL);
/*
Email que receberá notificações de backup ou alertas do sistema.
- filter_var garante que seja um email válido.
- Se inválido, a validação abaixo irá rejeitar o envio.
*/

$tema_padrao = in_array($_POST['tema_padrao'] ?? '', ['light', 'dark']) ? $_POST['tema_padrao'] : 'light';
/*
Define o tema padrão da interface.
- Só permite "light" ou "dark".
- Se não enviado ou inválido, usa "light" como fallback.
*/

// ------------------------
// Validações dos dados
// ------------------------

// Dias de empréstimo devem estar entre 1 e 30
if ($dias_emprestimo < 1 || $dias_emprestimo > 30) {
    responder(false, 'Dias de empréstimo deve ser entre 1 e 30.');
}

// Máximo de livros por aluno entre 1 e 10
if ($max_livros_aluno < 1 || $max_livros_aluno > 10) {
    responder(false, 'Máximo de livros por aluno deve ser entre 1 e 10.');
}

// Máximo de renovações entre 0 e 5
if ($max_renovacoes < 0 || $max_renovacoes > 5) {
    responder(false, 'Máximo de renovações deve ser entre 0 e 5.');
}

// Multa diária não pode ser negativa
if ($multa_dia_atraso < 0) {
    responder(false, 'Multa por dia de atraso não pode ser negativa.');
}

// Validação do email de notificação
if (!$email_notificacao) {
    responder(false, 'Email de notificação inválido.');
}

// ------------------------
// Atualização das configurações no banco
// ------------------------

$stmt = $conn->prepare("
    UPDATE configuracoes SET
        dias_emprestimo = ?,
        max_livros_aluno = ?,
        max_renovacoes = ?,
        multa_dia_atraso = ?,
        backup_automatico = ?,
        email_notificacao = ?,
        tema_padrao = ?
    WHERE id = 1
");
/*
Prepara a query SQL.
- ? são placeholders para evitar SQL Injection.
- Sempre usar prepared statements para dados vindos do usuário.
- Apenas a linha com id=1 é atualizada, pois este é o registro único de configurações.
*/

$stmt->bind_param(
    "iiiidss", // Tipos: i=int, i=int, i=int, i=int/d=float, d=double, s=string, s=string
    $dias_emprestimo,
    $max_livros_aluno,
    $max_renovacoes,
    $multa_dia_atraso,
    $backup_automatico,
    $email_notificacao,
    $tema_padrao
);
/*
Vincula os valores aos placeholders da query.
- Ordem importa: deve ser igual à query.
- "i" = integer, "d" = double/float, "s" = string.
*/

// Executa a query e retorna resposta
if ($stmt->execute()) {
    responder(true, 'Configurações salvas com sucesso!');
} else {
    responder(false, 'Erro ao salvar configurações: ' . $conn->error);
}
// Envia sucesso ou mensagem de erro do MySQL

$stmt->close();
// Fecha o statement preparado, liberando memória

$conn->close();
// Fecha a conexão com o banco
