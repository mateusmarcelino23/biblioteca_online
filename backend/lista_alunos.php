<?php
session_start(); // Inicia a sessão do PHP para usar variáveis de sessão (como o ID do professor logado)

// Verifica se o professor está logado
if (!isset($_SESSION['professor_id'])) { // Se não existe a variável de sessão do professor
    header("Location: ../frontend/login.php"); // Redireciona para a página de login
    exit(); // Interrompe a execução do script, garantindo que nada abaixo seja executado
}

require '../config.php';
// Configuração da paginação
$por_pagina = 14;

// Pegando a página atual pela URL, padrão 1
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

// Calculando o OFFSET
$inicio = ($pagina - 1) * $por_pagina;

// Contando o total de registros
$total_resultados = $conn->query("SELECT COUNT(*) as total FROM alunos")->fetch_assoc()['total'];

// Calculando o total de páginas
$total_paginas = ceil($total_resultados / $por_pagina);

// Buscando os registros da página atual
$result = $conn->query("SELECT id, nome, serie, email FROM alunos LIMIT $inicio, $por_pagina");

// Verifica se a URL contém o parâmetro "deletar" (id do aluno a ser removido)
if (isset($_GET['deletar'])) {
    $aluno_id = $_GET['deletar']; // Armazena o ID do aluno passado na URL

    // Prepara a query SQL para deletar o aluno específico pelo ID
    $sql = "DELETE FROM alunos WHERE id = ?";
    $stmt = $conn->prepare($sql); // Prepara a query para evitar SQL Injection
    $stmt->bind_param("i", $aluno_id); // Vincula o ID do aluno como inteiro (i) ao statement

    // Executa a query e verifica se funcionou
    if ($stmt->execute()) {
        // Se a execução foi bem-sucedida, exibe mensagem de sucesso
        echo "<div class='alert alert-success fade show' role='alert'>
                Aluno deletado com sucesso!
              </div>";

        // Recalcular após deletar
        $total_resultados = $conn->query("SELECT COUNT(*) as total FROM alunos")->fetch_assoc()['total'];
        $total_paginas = ceil($total_resultados / $por_pagina);

        // Ajustar página se necessário
        if ($pagina > $total_paginas) {
            $pagina = $total_paginas > 0 ? $total_paginas : 1;
            $inicio = ($pagina - 1) * $por_pagina;
        }

        // Rebuscar registros
        $result = $conn->query("SELECT id, nome, serie, email FROM alunos LIMIT $inicio, $por_pagina");
    } else {
        // Se houve erro na execução, exibe mensagem de erro
        echo "<div class='alert alert-danger fade show' role='alert'>
                Erro ao deletar aluno!
              </div>";
    }

    $stmt->close(); // Fecha o statement para liberar memória e recursos
}
