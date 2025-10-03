<?php
// Inicie a sessão e conexão com DB
session_start();
include "../../includes/conn.php"; 

// Se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['professor_id'])) {
  $professor_id = intval($_POST['professor_id']); // garante número inteiro

  try {
    $stmt = $pdo->prepare("UPDATE professores SET admin = 1 WHERE id = ?");
    $stmt->execute([$professor_id]);
    $msg = "Professor promovido a administrador!";
  } catch (Exception $e) {
    $msg = "Erro: " . $e->getMessage();
  }
}
$conn->close();