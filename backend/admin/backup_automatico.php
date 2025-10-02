<?php
require_once '../../includes/conn.php'; // Inclui conexão com o banco de dados para poder registrar logs e acessar configurações

// Verificar se o backup automático está ativado
$stmt = $conn->prepare("SELECT backup_automatico FROM configuracoes WHERE id = 1");
$stmt->execute(); // Executa a consulta
$config = $stmt->get_result()->fetch_assoc(); // Pega o resultado como array associativo

if (!$config['backup_automatico']) {
    // Se backup automático estiver desativado, encerra o script
    exit('Backup automático desativado.');
}

// Criar diretório de backup caso não exista
$backup_dir = '../../backups'; // Define onde os backups serão armazenados
if (!file_exists($backup_dir)) { // Verifica se o diretório já existe
    mkdir($backup_dir, 0777, true); // Cria diretório com permissões 0777, recursivamente
}

// Nome do arquivo de backup baseado em timestamp para não sobrescrever
$timestamp = date('Y-m-d_H-i-s'); // Pega data e hora atual
$backup_file = $backup_dir . '/backup_' . $timestamp . '.sql'; // Arquivo SQL temporário

// Configurações do MySQL
$host = 'localhost';
$user = 'root'; // Usuário do banco
$pass = ''; // Senha do banco
$database = 'biblioteca'; // Nome do banco

// Comando para gerar dump do banco de dados
$command = sprintf(
    'mysqldump --host=%s --user=%s --password=%s %s > %s', // Comando mysqldump
    escapeshellarg($host), // Escapa caracteres especiais
    escapeshellarg($user),
    escapeshellarg($pass),
    escapeshellarg($database),
    escapeshellarg($backup_file)
);

// Executar backup do banco de dados
exec($command, $output, $return_var); // $output captura saída, $return_var captura código de erro

if ($return_var !== 0) { // Se código de retorno não for 0, deu erro
    exit('Erro ao realizar backup do banco de dados.');
}

// Criar arquivo ZIP para armazenar SQL + arquivos importantes
$zip = new ZipArchive(); // Cria objeto ZipArchive
$zip_file = $backup_dir . '/backup_automatico_' . $timestamp . '.zip'; // Nome final do ZIP

if ($zip->open($zip_file, ZipArchive::CREATE) !== TRUE) { // Abre o ZIP para criar
    exit('Erro ao criar arquivo ZIP.');
}

// Adicionar o arquivo SQL dentro do ZIP, dentro da pasta "database"
$zip->addFile($backup_file, 'database/backup.sql');

// Diretórios que serão incluídos no backup
$dirs_to_backup = [
    '../../frontend' => 'frontend',
    '../../backend' => 'backend',
    '../../includes' => 'includes',
    '../../uploads' => 'uploads'
];

// Percorre cada diretório e adiciona arquivos ao ZIP
foreach ($dirs_to_backup as $dir => $zip_path) {
    if (file_exists($dir)) { // Verifica se o diretório existe
        // Iterator recursivo para percorrer todos os arquivos e subdiretórios
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) { // Apenas arquivos, ignora diretórios
                $file_path = $file->getRealPath(); // Caminho completo do arquivo
                $relative_path = $zip_path . '/' . substr($file_path, strlen($dir) + 1);
                // Define caminho relativo dentro do ZIP
                $zip->addFile($file_path, $relative_path); // Adiciona arquivo ao ZIP
            }
        }
    }
}

// Fecha o ZIP
$zip->close();

// Remove o arquivo SQL temporário, não precisamos mais dele
unlink($backup_file);

// Limpar backups antigos (manter apenas últimos 7 dias)
$backups = glob($backup_dir . '/backup_automatico_*.zip'); // Lista todos os ZIPs
usort($backups, function ($a, $b) {
    return filemtime($b) - filemtime($a); // Ordena por data mais recente
});

$dias_retencao = 7; // Quantos dias manter os backups
$data_limite = strtotime("-$dias_retencao days"); // Timestamp limite

foreach ($backups as $backup) {
    if (filemtime($backup) < $data_limite) { // Se backup é mais antigo que 7 dias
        unlink($backup); // Apaga o arquivo
    }
}

// Registrar backup no banco de dados
$stmt = $conn->prepare("
    INSERT INTO backup_log (
        arquivo,
        data_backup,
        tamanho,
        usuario_id,
        automatico
    ) VALUES (?, NOW(), ?, NULL, 1)
");

$file_size = filesize($zip_file); // Tamanho do arquivo em bytes
$stmt->bind_param('si', basename($zip_file), $file_size); // Vincula nome e tamanho
$stmt->execute(); // Executa inserção no banco

// Enviar email de notificação
$stmt = $conn->prepare("SELECT email_notificacao FROM configuracoes WHERE id = 1");
$stmt->execute();
$config = $stmt->get_result()->fetch_assoc();

$to = $config['email_notificacao']; // Destinatário
$subject = 'Backup Automático - Sistema Biblioteca'; // Assunto
$message = "
<html>
<head>
    <title>Backup Automático Realizado</title>
</head>
<body>
    <h2>Backup Automático Realizado com Sucesso</h2>
    <p>O backup automático do sistema foi realizado em " . date('d/m/Y H:i:s') . "</p>
    <p>Detalhes do backup:</p>
    <ul>
        <li>Arquivo: " . basename($zip_file) . "</li>
        <li>Tamanho: " . round($file_size / 1024 / 1024, 2) . " MB</li>
    </ul>
    <p>Este é um email automático, não responda.</p>
</body>
</html>
";

// Cabeçalhos do email
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=utf-8\r\n";
$headers .= "From: Sistema Biblioteca <noreply@biblioteca.com>\r\n";

mail($to, $subject, $message, $headers); // Envia email

// Mensagem final no console ou navegador
echo "Backup automático realizado com sucesso!\n";
echo "Arquivo: " . basename($zip_file) . "\n";
echo "Tamanho: " . round($file_size / 1024 / 1024, 2) . " MB\n";
