<?php
session_start(); // Inicia a sessão, necessário para verificar autenticação

require_once '../../includes/auth_admin.php';
// Inclui script que verifica se o usuário é admin, bloqueando acesso não autorizado

// Recebe o nome do arquivo passado via GET e usa basename para evitar path traversal
$filename = basename($_GET['file'] ?? '');
// basename() garante que o usuário não consiga passar algo como ../../senha.txt para acessar arquivos fora do backup

// Define o diretório onde os backups estão armazenados
$backup_dir = realpath(__DIR__ . '/../../backups');
// realpath() converte caminho relativo em absoluto, garantindo segurança

// Combina diretório + nome do arquivo para obter caminho completo
$file_path = $backup_dir . '/' . $filename;

// Verifica se o arquivo realmente existe
if (!file_exists($file_path)) {
    http_response_code(404); // Define status HTTP 404: arquivo não encontrado
    echo "Arquivo não encontrado!"; // Mensagem simples de erro
    exit; // Para a execução do script
}

// Preparando cabeçalhos HTTP para forçar download do arquivo .sql
header('Content-Description: File Transfer'); // Descrição da transferência
header('Content-Type: application/sql'); // Tipo MIME indicando que é um SQL
header('Content-Disposition: attachment; filename="' . $filename . '"');
// attachment força download, filename define nome sugerido

header('Expires: 0'); // Não cachear
header('Cache-Control: must-revalidate'); // Controla cache
header('Pragma: public'); // Compatibilidade HTTP
header('Content-Length: ' . filesize($file_path)); // Tamanho do arquivo

// Lê o arquivo e envia para o navegador
readfile($file_path);
exit; // Encerra o script após enviar o arquivo
