<?php
$env = parse_ini_file(__DIR__ . '/.env');

$host = $env['DB_HOST'];
$db   = $env['DB_NAME'];
$user = $env['DB_USER'];
$pass = $env['DB_PASS'];

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Falha na conexão: " . $conn->connect_error);
}