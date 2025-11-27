<?php
// Inclui o arquivo backend para processar o cadastro e exclusão
include('../backend/cadastro_professor.php');

// Inicia a sessão para acessar mensagens de toast
// session_start();

// Verifica se existe mensagem de toast na sessão e a armazena em $toast, depois remove da sessão
$toast = null;
if (isset($_SESSION['toast'])) {
    $toast = $_SESSION['toast'];
    unset($_SESSION['toast']);
}
?>

<!DOCTYPE html>
<html lang="pt" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Cadastro de Professor - Sistema de Gestão</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<style>
    .toast {
        position: relative;
        overflow: hidden;
    }

    .toast-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 4px;
        width: 100%;
        background-color: rgba(255, 255, 255, 0.7);
        animation: toast-progress-bar 3s linear forwards;
        /* mesma duração do toast (3s) */
    }

    @keyframes toast-progress-bar {
        from {
            width: 100%;
        }

        to {
            width: 0%;
        }
    }

    /* Reset and Base Styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    :root {
        --primary-orange: #ff6b35;
        --primary-orange-dark: #e55a2b;
        --primary-orange-light: #ff8c5f;
        --white: #ffffff;
        --off-white: #fafafa;
        --gray-light: #f5f5f5;
        --gray-medium: #e0e0e0;
        --gray-dark: #666666;
        --text-dark: #333333;
        --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
        --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.12);
        --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.16);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, var(--off-white) 0%, var(--white) 100%);
        color: var(--text-dark);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        line-height: 1.6;
    }

    /* Dark Theme */
    body.dark-theme {
        width: 100%;
        --white: #1a1a1a;
        --off-white: #242424;
        --gray-light: #2a2a2a;
        --gray-medium: #404040;
        --gray-dark: #b0b0b0;
        --text-dark: #e0e0e0;
        background: linear-gradient(135deg, #1a1a1a 0%, #242424 100%);
    }

    /* Theme Toggle Button */
    .theme-toggle {
        position: fixed;
        top: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: var(--white);
        border: 2px solid var(--gray-medium);
        color: var(--primary-orange);
        font-size: 20px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
        box-shadow: var(--shadow-md);
        z-index: 1000;
    }

    .theme-toggle:hover {
        transform: scale(1.1);
        box-shadow: var(--shadow-lg);
    }

    /* Main Container */
    .main-container {
        width: 100%;
        max-width: 600px;
        animation: fadeInUp 0.6s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Form Wrapper */
    .form-wrapper {
        background: var(--white);
        border-radius: 24px;
        box-shadow: var(--shadow-lg);
        overflow: hidden;
    }

    /* Form Header */
    .form-header {
        background: linear-gradient(135deg, var(--primary-orange) 0%, var(--primary-orange-light) 100%);
        color: var(--white);
        padding: 40px 30px;
        text-align: center;
    }

    .icon-wrapper {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 36px;
        backdrop-filter: blur(10px);
    }

    .form-header h1 {
        font-size: 28px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .form-header p {
        font-size: 14px;
        opacity: 0.95;
        font-weight: 300;
    }

    /* Form */
    .professor-form {
        padding: 40px 30px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 8px;
    }

    .form-group label i {
        color: var(--primary-orange);
        margin-right: 8px;
        width: 16px;
    }

    .form-group input {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid var(--gray-medium);
        border-radius: 12px;
        font-size: 15px;
        color: var(--text-dark);
        background: var(--off-white);
        transition: var(--transition);
        outline: none;
    }

    .form-group input:focus {
        border-color: var(--primary-orange);
        background: var(--white);
        box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
    }

    .form-group input::placeholder {
        color: var(--gray-dark);
    }

    /* Password Wrapper */
    .password-wrapper {
        position: relative;
    }

    .password-wrapper input {
        padding-right: 50px;
    }

    .toggle-password {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--gray-dark);
        cursor: pointer;
        padding: 8px;
        font-size: 16px;
        transition: var(--transition);
    }

    .toggle-password:hover {
        color: var(--primary-orange);
    }

    /* Form Hint */
    .form-hint {
        display: block;
        margin-top: 8px;
        font-size: 13px;
        color: var(--gray-dark);
    }

    .form-hint i {
        color: var(--primary-orange);
        margin-right: 6px;
    }

    /* Submit Button */
    .btn-submit {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, var(--primary-orange) 0%, var(--primary-orange-light) 100%);
        color: var(--white);
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        box-shadow: var(--shadow-md);
        margin-top: 8px;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .btn-submit:active {
        transform: translateY(0);
    }

    .btn-submit i {
        margin-right: 8px;
    }

    /* Back Button */
    .btn-back {
        display: block;
        width: calc(100% - 60px);
        margin: 0 30px 30px;
        padding: 14px;
        background: var(--off-white);
        color: var(--primary-orange);
        border: 2px solid var(--gray-medium);
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        transition: var(--transition);
    }

    .btn-back:hover {
        background: var(--gray-light);
        border-color: var(--primary-orange);
    }

    .btn-back i {
        margin-right: 8px;
    }

    /* Toast Notification */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 2000;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .toast {
        min-width: 300px;
        background: var(--white);
        border-radius: 12px;
        box-shadow: var(--shadow-lg);
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideInRight 0.4s ease-out;
        position: relative;
        overflow: hidden;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100%);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .toast.success {
        border-left: 4px solid #10b981;
    }

    .toast.error {
        border-left: 4px solid #ef4444;
    }

    .toast-icon {
        font-size: 20px;
        flex-shrink: 0;
    }

    .toast.success .toast-icon {
        color: #10b981;
    }

    .toast.error .toast-icon {
        color: #ef4444;
    }

    .toast-message {
        flex: 1;
        font-size: 14px;
        color: var(--text-dark);
    }

    .toast-close {
        background: none;
        border: none;
        color: var(--gray-dark);
        cursor: pointer;
        padding: 4px;
        font-size: 16px;
        transition: var(--transition);
    }

    .toast-close:hover {
        color: var(--text-dark);
    }

    .toast-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        background: var(--primary-orange);
        animation: toastProgress 3s linear forwards;
    }

    @keyframes toastProgress {
        from {
            width: 100%;
        }

        to {
            width: 0%;
        }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        body {
            padding: 10px;
        }

        .theme-toggle {
            width: 45px;
            height: 45px;
            top: 15px;
            right: 15px;
            font-size: 18px;
        }

        .form-header {
            padding: 30px 20px;
        }

        .icon-wrapper {
            width: 70px;
            height: 70px;
            font-size: 32px;
        }

        .form-header h1 {
            font-size: 24px;
        }

        .form-header p {
            font-size: 13px;
        }

        .professor-form {
            padding: 30px 20px;
        }

        .btn-back {
            width: calc(100% - 40px);
            margin: 0 20px 20px;
        }

        .toast-container {
            right: 10px;
            left: 10px;
            top: 15px;
        }

        .toast {
            min-width: auto;
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .form-header h1 {
            font-size: 22px;
        }

        .form-group input {
            padding: 12px 14px;
            font-size: 14px;
        }

        .btn-submit {
            padding: 14px;
            font-size: 15px;
        }
    }

    /* Loading State */
    .btn-submit.loading {
        pointer-events: none;
        opacity: 0.7;
    }

    .btn-submit.loading::after {
        content: "";
        width: 16px;
        height: 16px;
        border: 2px solid var(--white);
        border-top-color: transparent;
        border-radius: 50%;
        display: inline-block;
        margin-left: 8px;
        animation: spin 0.6s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>

<body>
    <!-- Theme Toggle Button -->
    <button class="theme-toggle" id="themeToggle" aria-label="Alternar tema">
        <i class="fas fa-moon" id="themeIcon"></i>
    </button>

    <!-- Toast Notification -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Main Container -->
    <div class="main-container">
        <div class="form-wrapper">
            <!-- Header -->
            <div class="form-header">
                <div class="icon-wrapper">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <h1>Cadastro de Professor</h1>
                <p>Preencha os dados para registrar um novo professor</p>
            </div>

            <!-- Form -->
            <form id="professorForm" class="professor-form">
                <div class="form-group">
                    <label for="nome">
                        <i class="fas fa-user"></i>
                        Nome Completo
                    </label>
                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        placeholder="Digite o nome completo"
                        required>
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="exemplo@escola.com"
                        required>
                </div>

                <div class="form-group">
                    <label for="cpf">
                        <i class="fas fa-id-card"></i>
                        CPF
                    </label>
                    <input
                        type="text"
                        id="cpf"
                        name="cpf"
                        placeholder="000.000.000-00"
                        maxlength="14"
                        required>
                </div>

                <div class="form-group">
                    <label for="senha">
                        <i class="fas fa-lock"></i>
                        Senha
                    </label>
                    <div class="password-wrapper">
                        <input
                            type="password"
                            id="senha"
                            name="senha"
                            placeholder="Digite a senha"
                            required>
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small class="form-hint">
                        <i class="fas fa-info-circle"></i>
                        A senha deve ter no mínimo 8 caracteres
                    </small>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Cadastrar Professor
                </button>
            </form>

            <!-- Back Button -->
            <a href="dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Voltar para o Painel
            </a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/cadastro_professor.js"></script>

    <script>
        // Inicializa os toasts do Bootstrap para exibir as mensagens
        document.addEventListener("DOMContentLoaded", function() {
            const toastElList = [].slice.call(document.querySelectorAll('.toast'));
            toastElList.forEach(function(toastEl) {
                const toast = new bootstrap.Toast(toastEl, {
                    delay: 3000
                });
                toast.show();
            });
        });

        // Theme Toggle
        const themeToggle = document.getElementById("themeToggle")
        const themeIcon = document.getElementById("themeIcon")
        const body = document.body

        // Load saved theme
        const savedTheme = localStorage.getItem("theme") || "light"
        if (savedTheme === "dark") {
            body.classList.add("dark-theme")
            themeIcon.classList.replace("fa-moon", "fa-sun")
        }

        themeToggle.addEventListener("click", () => {
            body.classList.toggle("dark-theme")
            const isDark = body.classList.contains("dark-theme")

            if (isDark) {
                themeIcon.classList.replace("fa-moon", "fa-sun")
                localStorage.setItem("theme", "dark")
            } else {
                themeIcon.classList.replace("fa-sun", "fa-moon")
                localStorage.setItem("theme", "light")
            }
        })

        // CPF Mask
        const cpfInput = document.getElementById("cpf")
        cpfInput.addEventListener("input", (e) => {
            let value = e.target.value.replace(/\D/g, "")

            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, "$1.$2")
                value = value.replace(/(\d{3})(\d)/, "$1.$2")
                value = value.replace(/(\d{3})(\d{1,2})$/, "$1-$2")
            }

            e.target.value = value
        })

        // Password Toggle
        const togglePassword = document.getElementById("togglePassword")
        const senhaInput = document.getElementById("senha")

        togglePassword.addEventListener("click", () => {
            const type = senhaInput.type === "password" ? "text" : "password"
            senhaInput.type = type

            const icon = togglePassword.querySelector("i")
            icon.classList.toggle("fa-eye")
            icon.classList.toggle("fa-eye-slash")
        })

        // Toast Notification Function
        function showToast(message, type = "success") {
            const toastContainer = document.getElementById("toastContainer")

            const toast = document.createElement("div")
            toast.className = `toast ${type}`

            const iconClass = type === "success" ? "fa-check-circle" : "fa-exclamation-circle"

            toast.innerHTML = `
        <i class="fas ${iconClass} toast-icon"></i>
        <span class="toast-message">${message}</span>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
        <div class="toast-progress"></div>
    `

            toastContainer.appendChild(toast)

            // Auto remove after 3 seconds
            setTimeout(() => {
                toast.style.animation = "slideInRight 0.4s ease-out reverse"
                setTimeout(() => toast.remove(), 400)
            }, 3000)
        }

        // Form Validation and Submit
        const professorForm = document.getElementById("professorForm")

        professorForm.addEventListener("submit", async (e) => {
            e.preventDefault()

            const submitBtn = professorForm.querySelector(".btn-submit")
            const formData = new FormData(professorForm)

            // Validate password length
            const senha = formData.get("senha")
            if (senha.length < 8) {
                showToast("A senha deve ter no mínimo 8 caracteres", "error")
                return
            }

            // Validate CPF format
            const cpf = formData.get("cpf").replace(/\D/g, "")
            if (cpf.length !== 11) {
                showToast("CPF inválido. Digite 11 dígitos", "error")
                return
            }

            // Add loading state
            submitBtn.classList.add("loading")
            submitBtn.disabled = true

            // Simulate API call (replace with actual backend call)
            try {
                // Example: await fetch('/backend/cadastro_professor.php', {
                //     method: 'POST',
                //     body: formData
                // });

                // Simulate delay
                await new Promise((resolve) => setTimeout(resolve, 1500))

                // Success
                showToast("Professor cadastrado com sucesso!", "success")
                professorForm.reset()
            } catch (error) {
                showToast("Erro ao cadastrar professor. Tente novamente.", "error")
            } finally {
                submitBtn.classList.remove("loading")
                submitBtn.disabled = false
            }
        })

        // Input Focus Animation
        const inputs = document.querySelectorAll(".form-group input")
        inputs.forEach((input) => {
            input.addEventListener("focus", () => {
                input.parentElement.style.transform = "scale(1.01)"
            })

            input.addEventListener("blur", () => {
                input.parentElement.style.transform = "scale(1)"
            })
        })
    </script>

</body>

</html>