<?php
// Função que retorna os top alunos por sala, limitada pelo parâmetro $limite (padrão 3)
function getTopAlunosPorSala($limite = 3)
{
    // Inclui a conexão com o banco de dados
    require '../includes/conn.php';

    // Query SQL para contar o total de empréstimos de cada aluno
    // JOIN com a tabela alunos para pegar nome e série
    // GROUP BY aluno para agregar os empréstimos
    // ORDER BY série ASC (para organizar por sala) e total de empréstimos DESC (maior primeiro)
    $query = "
        SELECT 
            alunos.nome, 
            alunos.serie, 
            COUNT(emprestimos.id) AS total_emprestimos
        FROM emprestimos
        JOIN alunos ON emprestimos.aluno_id = alunos.id
        GROUP BY alunos.id, alunos.nome, alunos.serie
        ORDER BY alunos.serie ASC, total_emprestimos DESC
    ";

    // Executa a query
    $result = $conn->query($query);

    // Array que irá armazenar os top alunos por sala
    $alunos_por_sala = [];

    // Verifica se a query retornou resultados
    if ($result && $result->num_rows > 0) {
        // Itera sobre cada linha do resultado
        while ($row = $result->fetch_assoc()) {
            $sala = $row['serie']; // pega a série do aluno

            // Se ainda não existe essa sala no array, cria o índice
            if (!isset($alunos_por_sala[$sala])) {
                $alunos_por_sala[$sala] = [];
            }

            // Só adiciona alunos até o limite definido (ex: top 3)
            if (count($alunos_por_sala[$sala]) < $limite) {
                // Cria um array representando o aluno
                $aluno = [
                    'nome' => $row['nome'],                            // Nome do aluno
                    'total_emprestimos' => $row['total_emprestimos'],  // Total de empréstimos
                    'posicao' => count($alunos_por_sala[$sala]) + 1    // Posição dentro da sala
                ];
                // Adiciona o aluno ao array da sala
                $alunos_por_sala[$sala][] = $aluno;
            }
        }
    }

    // Retorna um array associativo com os top alunos por sala
    // Estrutura:
    // [
    //   '1º ano' => [ ['nome'=>'Aluno A','total_emprestimos'=>5,'posicao'=>1], ... ],
    //   '2º ano' => [ ... ],
    //   ...
    // ]
    return $alunos_por_sala;
}
