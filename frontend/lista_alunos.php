<?php
include('../backend/lista_alunos.php');

?>



<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Alunos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/lista_alunos.css">
</head>

<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-users"></i> Lista de Alunos</h4>
                    <a href="cadastro_aluno.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-plus"></i> Cadastrar Aluno
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php
                // Exibindo a lista de alunos cadastrados
                if ($result->num_rows > 0) {
                    echo "<table class='table table-hover'>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nome</th>
                                    <th>Série</th>
                                    <th>Email</th>
                                    <th>Ação</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>";

                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>" . $row['id'] . "</td>
                            <td>" . $row['nome'] . "</td>
                            <td>" . $row['serie'] . "</td>
                            <td>" . $row['email'] . "</td>
                            <td>
                                <a href='?deletar=" . $row['id'] . "' class='btn btn-danger btn-sm'>
                                    <i class='fas fa-trash-alt'></i> Deletar
                                </a>
                            </td>
                            <td>
                                <a href='editar_aluno.php?id=" . $row['id'] . "' class='btn btn-primary btn-sm'>
                                    <i class='fas fa-edit'></i> Editar
                                </a>
                            </td>
                        </tr>";
                    }

                    echo "</tbody></table>";
                } else {
                    echo "<p>Nenhum aluno cadastrado.</p>";
                } ?>

                <div style="width: 100%;">
                    <?php
                    $limite_links = 5; // quantos links de página mostrar de cada vez
                    $inicio_loop = max(1, $pagina - floor($limite_links / 2));
                    $fim_loop = min($total_paginas, $inicio_loop + $limite_links - 1);

                    // Ajusta início se estivermos perto do final
                    if ($fim_loop - $inicio_loop + 1 < $limite_links) {
                        $inicio_loop = max(1, $fim_loop - $limite_links + 1);
                    }

                    echo "<nav aria-label='Page navigation'>";
                    echo "<ul class='pagination justify-content-center'>";

                    // Botão “Primeira”
                    $disabled = ($pagina == 1) ? 'disabled' : '';
                    echo "<li class='page-item $disabled'><a class='page-link' href='?pagina=1'>&laquo;&laquo;</a></li>";

                    // Botão “Anterior”
                    $prev = max(1, $pagina - 1);
                    $disabled = ($pagina == 1) ? 'disabled' : '';
                    echo "<li class='page-item $disabled'><a class='page-link' href='?pagina=$prev'>&laquo;</a></li>";

                    // Links de páginas do meio
                    for ($i = $inicio_loop; $i <= $fim_loop; $i++) {
                        $ativo = ($i == $pagina) ? 'active' : '';
                        echo "<li class='page-item $ativo'><a class='page-link' href='?pagina=$i'>$i</a></li>";
                    }

                    // Botão “Próxima”
                    $next = min($total_paginas, $pagina + 1);
                    $disabled = ($pagina == $total_paginas) ? 'disabled' : '';
                    echo "<li class='page-item $disabled'><a class='page-link' href='?pagina=$next'>&raquo;</a></li>";

                    // Botão “Última”
                    $disabled = ($pagina == $total_paginas) ? 'disabled' : '';
                    echo "<li class='page-item $disabled'><a class='page-link' href='?pagina=$total_paginas'>&raquo;&raquo;</a></li>";

                    echo "</ul>";
                    echo "</nav>";
                    ?>
                </div>



                <?php
                $conn->close();
                ?>

            </div>
        </div>
        <br>
        <a href="dashboard.php" class="btn btn-primary w-100" id="voltaDashboardId">Voltar para o Painel</a>
        <br>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>