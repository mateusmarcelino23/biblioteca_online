<?php
// Inicia a sessão PHP
// Necessário para acessar informações do usuário logado
session_start();

// --- Verifica se o professor está logado ---
// Se não estiver logado, redireciona para a página de login
if (!isset($_SESSION['professor_id'])) {
    header("Location: ../frontend/login.php"); // Redirecionamento
    exit(); // Interrompe execução do script
}

// --- Conexão com o banco de dados ---
// Inclui arquivo que cria a conexão $conn
include('../includes/conn.php');

// --- Consultas para Dashboard ---
// Cada consulta busca uma estatística diferente para mostrar no dashboard

// 1️⃣ Total de alunos cadastrados
$result_students = $conn->query("SELECT COUNT(*) as total FROM alunos");
// fetch_assoc() transforma o resultado em array associativo
$totalAlunos = $result_students->fetch_assoc()['total'];

// 2️⃣ Total de livros cadastrados
$result_books = $conn->query("SELECT COUNT(*) as total FROM livros");
$totalLivros = $result_books->fetch_assoc()['total'];

// 3️⃣ Total de empréstimos ativos (não devolvidos)
$result_loans = $conn->query("SELECT COUNT(*) as total FROM emprestimos WHERE devolvido = '0'");
// devolvido = '0' significa que o livro ainda não foi devolvido
$totalEmprestimos = $result_loans->fetch_assoc()['total'];

// 4️⃣ Total de devoluções pendentes
// Aqui se verifica se a data_devolucao é NULL, ou seja, ainda não foi registrada
$result_pending_returns = $conn->query("SELECT COUNT(*) as total FROM emprestimos WHERE data_devolucao IS NULL");
$totalDevolucoesPendentes = $result_pending_returns->fetch_assoc()['total'];

// Agora você pode usar as variáveis:
// $totalAlunos, $totalLivros, $totalEmprestimos, $totalDevolucoesPendentes
// para exibir os números no dashboard
