<?php
// Inclui o backend que contém a lógica de edição do aluno
include('../backend/editar_aluno.php');
?>

<!DOCTYPE html>
<html lang="pt-br" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Aluno - Sistema de Gestão de Biblioteca</title>

    <!-- CSS do Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS do Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Favicon -->
    <link rel="icon" href="favicon/favicon-32x32.png" type="image/x-icon">
    <!-- Fonte Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS personalizado de cadastro/edição de aluno -->
    <link rel="stylesheet" href="assets/css/cadastro_aluno.css">
    <!-- CSS de toast -->
    <link rel="stylesheet" href="assets/css/toast.css">
</head>

<body>
    <!-- Botão de alternar tema -->
    <button class="theme-toggle" id="themeToggle">
        <i class="fas fa-moon" id="themeIcon"></i>
    </button>

    <!-- Cabeçalho da página -->
    <div class="dashboard-header text-center">
        <div class="container">
            <h1 class="mb-2">
                <i class="fas fa-user-graduate"></i> Editar Aluno
            </h1>
        </div>
    </div>

    <!-- Toast de notificação -->
    <?php if ($toast): ?>
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
            <div class="toast align-items-center text-bg-<?php echo $toast['type'] === 'success' ? 'success' : 'danger'; ?> border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <?php echo htmlspecialchars($toast['message']); ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
                </div>
                <div class="toast-progress"></div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Container principal -->
    <div class="container">
        <!-- Card do formulário -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4>
                        <i class="fas fa-user-edit"></i> Atualizar Aluno
                    </h4>
                </div>
            </div>
            <div class="card-body">
                <!-- Formulário de edição -->
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="nome" class="form-label">
                            <i class="fas fa-user"></i> Nome Completo
                        </label>
                        <input type="text" class="form-control" id="nome" name="nome" required
                            value="<?= htmlspecialchars($aluno['nome']) ?>" placeholder="Digite o nome completo">
                    </div>

                    <div class="mb-3">
                        <label for="serie" class="form-label">
                            <i class="fas fa-graduation-cap"></i> Série
                        </label>
                        <input type="text" class="form-control" id="serie" name="serie" required
                            value="<?= htmlspecialchars($aluno['serie']) ?>" placeholder="Digite a série">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email" class="form-control" id="email" name="email" required
                            value="<?= htmlspecialchars($aluno['email']) ?>" placeholder="Digite o email">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> Atualizar Aluno
                    </button>
                </form>
            </div>
        </div>

        <!-- Botão de voltar para o painel -->
        <a href="lista_alunos.php" class="btn btn-primary w-100 mt-3">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <!-- Scripts JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/cadastro_aluno.js"></script>
    <script src="assets/js/toast.js"></script>
</body>

</html>