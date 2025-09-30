--
-- Banco de dados: `mvc_biblioteca`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `professores`
--
CREATE TABLE `professores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `data_cadastro` datetime DEFAULT current_timestamp(),
  `ultimo_login` datetime DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `admin` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `cpf` (`cpf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `alunos`
--
CREATE TABLE `alunos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `serie` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `professor_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `professor_id` (`professor_id`),
  CONSTRAINT `alunos_ibfk_1` FOREIGN KEY (`professor_id`) REFERENCES `professores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `livros`
--
CREATE TABLE `livros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `autor` varchar(255) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `capa_url` varchar(700) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `ano_publicacao` varchar(4) NOT NULL,
  `genero` varchar(100) NOT NULL,
  `quantidade` int(100) NOT NULL,
  `preview_link` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `isbn` (`isbn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `emprestimos`
--
CREATE TABLE `emprestimos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aluno_id` int(11) DEFAULT NULL,
  `livro_id` int(11) DEFAULT NULL,
  `data_emprestimo` date NOT NULL,
  `data_devolucao` date DEFAULT NULL,
  `devolvido` varchar(50) NOT NULL,
  `professor_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `aluno_id` (`aluno_id`),
  KEY `livro_id` (`livro_id`),
  CONSTRAINT `emprestimos_ibfk_1` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `emprestimos_ibfk_2` FOREIGN KEY (`livro_id`) REFERENCES `livros` (`id`) ON DELETE SET NULL,
  CONSTRAINT `emprestimos_ibfk_3` FOREIGN KEY (`professor_id`) REFERENCES `professores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `admin_settings`
--
CREATE TABLE `admin_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `secret_code` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `backup_log`
--
CREATE TABLE `backup_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `arquivo` varchar(255) NOT NULL,
  `data_backup` datetime NOT NULL,
  `tamanho` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `automatico` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `backup_log_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `professores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `configuracoes`
--
CREATE TABLE `configuracoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dias_emprestimo` int(11) NOT NULL DEFAULT 7,
  `max_livros_aluno` int(11) NOT NULL DEFAULT 3,
  `max_renovacoes` int(11) NOT NULL DEFAULT 2,
  `multa_dia_atraso` decimal(10,2) NOT NULL DEFAULT 0.50,
  `backup_automatico` tinyint(1) NOT NULL DEFAULT 1,
  `email_notificacao` varchar(255) NOT NULL,
  `tema_padrao` enum('light','dark') NOT NULL DEFAULT 'light',
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Inserção de dados para a tabela `professores`
--
INSERT INTO `professores` (`id`, `nome`, `email`, `cpf`, `senha`, `data_cadastro`, `ultimo_login`, `ativo`, `admin`) VALUES
(1, 'alefteste1', 'alefteste1@gmail.com', '72316609082', '$2y$10$YuNDRe6vCVqyjfo/6soKHOyNUI2f2U2RYtJAj6kcAkdvF4H5zHxCG', '2025-04-26 18:36:20', '2025-09-03 08:01:23', 1, 0);

--
-- Fim
--
