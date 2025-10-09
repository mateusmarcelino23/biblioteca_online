<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro de Livro</title>
  <link rel="stylesheet" href="styles.css">
</head>
<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    background-color: #f5f5f5;
    color: #333;
    line-height: 1.6;
  }

  .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
  }

  /* Header Styles */
  .header {
    background: linear-gradient(135deg, #5b73e8 0%, #4c63d2 100%);
    border-radius: 16px;
    padding: 24px 32px;
    margin-bottom: 32px;
    box-shadow: 0 4px 20px rgba(91, 115, 232, 0.3);
  }

  .header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .header-title {
    display: flex;
    align-items: center;
    gap: 12px;
    color: white;
    font-size: 24px;
    font-weight: 600;
  }

  .book-icon {
    width: 28px;
    height: 28px;
    color: white;
  }

  .view-list-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: white;
    color: #5b73e8;
    border: none;
    padding: 12px 20px;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .view-list-btn:hover {
    background: #f8f9ff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  .list-icon {
    width: 20px;
    height: 20px;
  }

  /* Main Content */
  .main-content {
    background: white;
    border-radius: 16px;
    padding: 40px;
    box-shadow: 0 2px 16px rgba(0, 0, 0, 0.08);
  }

  .book-form {
    max-width: 800px;
    margin: 0 auto;
  }

  /* Form Groups */
  .form-group {
    margin-bottom: 32px;
  }

  .form-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 12px;
  }

  .field-icon {
    width: 20px;
    height: 20px;
    color: #666;
  }

  .form-input {
    width: 100%;
    padding: 16px 20px;
    border: 2px solid #e1e5e9;
    border-radius: 12px;
    font-size: 16px;
    color: #333;
    background: white;
    transition: all 0.2s ease;
  }

  .form-input:focus {
    outline: none;
    border-color: #5b73e8;
    box-shadow: 0 0 0 3px rgba(91, 115, 232, 0.1);
  }

  .form-input::placeholder {
    color: #5b73e8;
    opacity: 0.7;
  }

  /* Submit Button */
  .submit-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    background: linear-gradient(135deg, #5b73e8 0%, #4c63d2 100%);
    color: white;
    border: none;
    padding: 18px 32px;
    border-radius: 12px;
    font-size: 18px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 16px rgba(91, 115, 232, 0.3);
    margin-top: 16px;
  }

  .submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(91, 115, 232, 0.4);
  }

  .submit-btn:active {
    transform: translateY(0);
  }

  .submit-icon {
    width: 24px;
    height: 24px;
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .container {
      padding: 16px;
    }

    .header {
      padding: 20px;
    }

    .header-content {
      flex-direction: column;
      gap: 16px;
      text-align: center;
    }

    .header-title {
      font-size: 20px;
    }

    .main-content {
      padding: 24px;
    }

    .form-group {
      margin-bottom: 24px;
    }

    .form-input {
      padding: 14px 16px;
    }

    .submit-btn {
      padding: 16px 24px;
      font-size: 16px;
    }
  }

  @media (max-width: 480px) {
    .view-list-btn {
      padding: 10px 16px;
      font-size: 14px;
    }

    .form-input {
      padding: 12px 16px;
      font-size: 14px;
    }

    .submit-btn {
      padding: 14px 20px;
      font-size: 16px;
    }
  }
</style>

<body>
  <div class="container">
    <header class="header">
      <div class="header-content">
        <div class="header-title">
          <svg class="book-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
          </svg>
          Novo Livro
        </div>
        <a href="visualizar_livros.php" class="view-list-btn">
          <svg class="list-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="8" y1="6" x2="21" y2="6"></line>
            <line x1="8" y1="12" x2="21" y2="12"></line>
            <line x1="8" y1="18" x2="21" y2="18"></line>
            <line x1="3" y1="6" x2="3.01" y2="6"></line>
            <line x1="3" y1="12" x2="3.01" y2="12"></line>
            <line x1="3" y1="18" x2="3.01" y2="18"></line>
          </svg>
          Ver Lista de Livros
        </a>
      </div>
    </header>

    <main class="main-content">
      <form class="book-form" action="../backend/cadastro_livro_manual.php" method="POST">
        <div class="form-group">
          <label for="titulo" class="form-label">
            <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
              <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
            </svg>
            Título
          </label>
          <input
            type="text"
            id="titulo"
            name="titulo"
            placeholder="Digite o título do livro"
            class="form-input"
            required>
        </div>

        <div class="form-group">
          <label for="autor" class="form-label">
            <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
            Autor
          </label>
          <input
            type="text"
            id="autor"
            name="autor"
            placeholder="Digite o nome do autor"
            class="form-input">
        </div>

        <div class="form-group">
          <label for="isbn" class="form-label">
            <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
              <line x1="8" y1="21" x2="16" y2="21"></line>
              <line x1="12" y1="17" x2="12" y2="21"></line>
            </svg>
            ISBN
          </label>
          <input
            type="text"
            id="isbn"
            name="isbn"
            placeholder="Digite o ISBN"
            class="form-input">
        </div>

        <div class="form-group">
          <label for="genero" class="form-label">
            <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
            </svg>
            Gênero
          </label>
          <input
            type="text"
            id="genero"
            name="genero"
            placeholder="Digite o gênero do livro"
            class="form-input">
        </div>

        <div class="form-group">
          <label for="ano_publicacao" class="form-label">
            <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            Ano de Publicação
          </label>
          <input
            type="text"
            id="ano_publicacao"
            name="ano_publicacao"
            placeholder="Digite o ano de publicação"
            class="form-input">
        </div>
        <div>
          <input type="number" name="qntd" placeholder="quantidade">
        </div>

        <button type="submit" class="submit-btn">
          <svg class="submit-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
          </svg>
          Cadastrar Livro
        </button>
      </form>
    </main>
  </div>
</body>

</html>
