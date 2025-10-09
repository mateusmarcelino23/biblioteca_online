<?php
$conexao = "localhost";
$usuario = "root";
$senha = "";
$db = "biblioteca_online";

$conn = new mysqli($conexao, $usuario, $senha, $db);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");