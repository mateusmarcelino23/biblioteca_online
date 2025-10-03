<?php
session_start();
// Inicia a sessão para manter informações do usuário, necessário para verificar se é admin

require_once '../../includes/auth_admin.php';
// Garante que só administradores podem executar esse script

require_once '../../includes/conn.php';
// Conecta ao banco de dados

header('Content-Type: application/json');
// Define que a saída será JSON, útil para front-end consumir via AJAX

// Função para responder em JSON e encerrar execução do script
function responder($sucesso, $mensagem = '', $arquivo = '')
{
    echo json_encode([
        'success' => $sucesso, // true ou false, indicando sucesso ou erro
        'message' => $mensagem, // mensagem de feedback
        'file' => $arquivo      // nome do arquivo gerado (opcional)
    ]);
    exit; // encerra execução após enviar resposta
}

// Caminho do diretório onde serão armazenados os backups
$backup_dir = realpath(__DIR__ . '/../../backups');
// realpath converte caminho relativo em absoluto

// Se o diretório não existir, tenta criar
if ($backup_dir === false) {
    $backup_dir = __DIR__ . '/../../backups';
    if (!file_exists($backup_dir)) {
        if (!mkdir($backup_dir, 0777, true)) {
            // cria o diretório com permissões totais se necessário
            responder(false, 'Não foi possível criar o diretório de backups.');
        }
    }
}

// Nome do arquivo de backup baseado na data/hora atual
$timestamp = date('Y-m-d_H-i-s');
$backup_file = $backup_dir . '/backup_' . $timestamp . '.sql';

// Configurações do MySQL
$host = 'localhost';
$user = 'root';      // usuário MySQL
$pass = '';          // senha MySQL
$database = 'mvc_biblioteca'; // banco de dados a ser exportado

// Monta o comando mysqldump dependendo se há senha ou não
if ($pass !== '') {
    $command = sprintf(
        'mysqldump --host=%s --user=%s --password=%s %s > %s',
        escapeshellarg($host),
        escapeshellarg($user),
        escapeshellarg($pass),
        escapeshellarg($database),
        escapeshellarg($backup_file)
    );
} else {
    $command = sprintf(
        'mysqldump --host=%s --user=%s %s > %s',
        escapeshellarg($host),
        escapeshellarg($user),
        escapeshellarg($database),
        escapeshellarg($backup_file)
    );
}

// Executa o comando de backup via shell
exec($command, $output, $return_var);
// $output captura mensagens, $return_var captura código de retorno do comando

// Verifica se o arquivo foi criado e se tem tamanho mínimo (1KB)
if (!file_exists($backup_file) || filesize($backup_file) < 1024) {
    responder(false, 'Ocorreu um erro ao realizar o backup. O arquivo não foi gerado corretamente.');
}

// Limpar backups antigos, mantendo apenas os últimos 5
$backups = glob($backup_dir . '/backup_*.sql');
// glob retorna array com todos os arquivos que seguem o padrão
usort($backups, function ($a, $b) {
    return filemtime($b) - filemtime($a);
    // ordena por data de modificação mais recente primeiro
});
while (count($backups) > 5) {
    $old_backup = array_pop($backups); // remove backup mais antigo
    unlink($old_backup); // deleta o arquivo
}

// Registrar backup no banco de dados
$stmt = $conn->prepare("
    INSERT INTO backup_log (
        arquivo,
        data_backup,
        tamanho,
        usuario_id
    ) VALUES (?, NOW(), ?, ?)
");
$file_size = filesize($backup_file);
$usuario_id = $_SESSION['professor_id']; // id do usuário que gerou o backup
$stmt->bind_param('sii', basename($backup_file), $file_size, $usuario_id);
$stmt->execute(); // salva log do backup

// Retorna sucesso em JSON, incluindo nome do arquivo gerado
responder(
    true,
    'Backup do banco de dados realizado com sucesso!',
    basename($backup_file)
);

/*
    Observações:
    - Verifica diretório e cria se necessário
    - Usa mysqldump para gerar backup SQL
    - Limita quantidade de backups antigos
    - Registra no banco de dados
    - Retorna resposta JSON para front-end
*/
