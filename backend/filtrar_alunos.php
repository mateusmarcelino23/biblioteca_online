<?php
// Inclui o arquivo de conexão com o banco
// Cria a variável $conn que será usada para consultas SQL
require '../config.php';
// --- Construção da query base ---
// Começamos com "WHERE 1=1" para facilitar a adição de filtros dinamicamente
$query = "SELECT id, nome, serie FROM alunos WHERE 1=1";
$params = array(); // Array que vai armazenar os parâmetros para prepared statement

// --- Filtro por nome ---
// Verifica se o parâmetro 'nome' foi enviado via GET
if (!empty($_GET['nome'])) {
    $query .= " AND nome LIKE ?"; // Adiciona condição LIKE para pesquisa parcial
    $params[] = "%" . $_GET['nome'] . "%"; // Adiciona o valor ao array de parâmetros
}

// --- Filtro por série ---
// Verifica se o parâmetro 'serie' foi enviado via GET
if (!empty($_GET['serie'])) {
    $query .= " AND serie = ?"; // Adiciona condição de igualdade para série
    $params[] = $_GET['serie']; // Adiciona o valor ao array de parâmetros
}

// --- Ordenação ---
// Ordena os resultados pelo nome em ordem alfabética
$query .= " ORDER BY nome ASC";

// --- Preparação e execução da consulta ---
// Cria um prepared statement para maior segurança (evita SQL Injection)
$stmt = $conn->prepare($query);

// --- Bind de parâmetros se houver filtros ---
// str_repeat('s', count($params)) cria uma string com 's' repetido para cada parâmetro (tipo string)
if (!empty($params)) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}

// Executa a consulta
$stmt->execute();

// Recupera os resultados como objeto MySQLi
$result = $stmt->get_result();

// --- Opção padrão no select ---
// Exibe primeiro item vazio para o usuário selecionar
echo '<option value="">Selecione o aluno</option>';

// --- Geração das opções do select ---
// Percorre os resultados e cria as tags <option> para cada aluno
if ($result->num_rows > 0) {
    while ($aluno = $result->fetch_assoc()) {
        echo sprintf(
            '<option value="%s">%s (%s)</option>',
            $aluno['id'], // Valor que será enviado ao selecionar
            htmlspecialchars($aluno['nome']), // Nome do aluno (HTML escapado)
            htmlspecialchars($aluno['serie']) // Série do aluno (HTML escapado)
        );
    }
}

// Fecha statement e conexão com o banco para liberar recursos
$stmt->close();
$conn->close();
