<?php
// gera_dados_biblioteca.php
// Uso: php gera_dados_biblioteca.php caminho/para/existing.sql output_inserts.sql

$con = "localhost";
$user = "root";
$pass = "";
$db = "mvc_biblioteca";
$pdo = new PDO("mysql:host=$con;dbname=$db;charset=utf8", $user, $pass);

if ($argc < 3) {
  echo "Uso: php {$argv[0]} mvc_biblioteca.sql output_inserts.sql\n";
  exit(1);
}

$inputSqlFile = $argv[1];
$outputSqlFile = $argv[2];

// --- Configurações ---
$totalProfessores = 20;
$totalAlunos = 300;
$totalLivrosDesejados = 200;
$totalEmprestimos = 300;
$passwordPlain = 'alef1234';
$passwordHash = password_hash($passwordPlain, PASSWORD_BCRYPT); // bcrypt

// --- Funções utilitárias ---
function sql_escape($s)
{
  // Escapa para SQL padrão (duplica aspas simples)
  return str_replace("'", "''", $s);
}

function read_file_or_empty($path)
{
  if (!file_exists($path)) return '';
  return file_get_contents($path);
}

function find_max_id_in_inserts($sqlContent, $tableName)
{
  // Procura INSERT INTO `tableName` ( ... ) VALUES (...),(...),...
  // Extrai o primeiro column name being id if provided. But we'll look for explicit numeric ids in VALUES
  $pattern = "/INSERT\\s+INTO\\s+`?" . preg_quote($tableName, '/') . "`?\\s*\\(([^)]*)\\)\\s*VALUES\\s*(.*?);/is";
  $max = 0;
  if (preg_match_all($pattern, $sqlContent, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $m) {
      $cols = array_map('trim', explode(',', $m[1]));
      // normalize columns
      $cols = array_map(function ($c) {
        return trim($c, " `\n\r\t");
      }, $cols);
      $valuesPart = $m[2];
      // split by '),(' but must handle single row or multiple rows
      // normalize: remove starting/ending parentheses if needed
      $valuesPart = trim($valuesPart);
      // split rows by regex that finds '),(' at root level
      $rows = preg_split("/\\)\\s*,\\s*\\(/", trim($valuesPart, "(); \n\r\t"));
      foreach ($rows as $row) {
        // remove leading/trailing parens
        $row = trim($row);
        $row = trim($row, "()");
        // split values by commas but naive split may break on strings with commas
        // We'll parse respecting quotes
        $vals = parse_sql_values_row($row);
        // find index of id column if exists
        $idIndex = null;
        foreach ($cols as $i => $col) {
          if (strtolower($col) === 'id') {
            $idIndex = $i;
            break;
          }
        }
        if ($idIndex !== null && isset($vals[$idIndex])) {
          $val = $vals[$idIndex];
          // remove quotes and non-digits
          if (preg_match('/^\\s*([0-9]+)/', $val, $mm)) {
            $v = intval($mm[1]);
            if ($v > $max) $max = $v;
          }
        }
      }
    }
  }
  return $max;
}

function parse_sql_values_row($row)
{
  $vals = [];
  $len = strlen($row);
  $i = 0;
  $cur = '';
  $inString = false;
  $quoteChar = '';
  while ($i < $len) {
    $ch = $row[$i];
    if ($inString) {
      if ($ch === $quoteChar) {
        // check for doubled quote ''
        if ($i + 1 < $len && $row[$i + 1] === $quoteChar) {
          $cur .= $quoteChar; // escaped quote
          $i += 2;
          continue;
        } else {
          $inString = false;
          $cur .= $ch;
          $i++;
          continue;
        }
      } else {
        $cur .= $ch;
        $i++;
        continue;
      }
    } else {
      if ($ch === "'" || $ch === '"') {
        $inString = true;
        $quoteChar = $ch;
        $cur .= $ch;
        $i++;
        continue;
      } elseif ($ch === ',') {
        $vals[] = trim($cur);
        $cur = '';
        $i++;
        continue;
      } else {
        $cur .= $ch;
        $i++;
        continue;
      }
    }
  }
  if (strlen(trim($cur)) > 0) $vals[] = trim($cur);
  return $vals;
}

// CPF generator with valid check digits
function generate_cpf()
{
  $n = [];
  for ($i = 0; $i < 9; $i++) $n[$i] = rand(0, 9);
  // first digit
  $s = 0;
  for ($i = 0; $i < 9; $i++) $s += $n[$i] * (10 - $i);
  $r = $s % 11;
  $d1 = ($r < 2) ? 0 : 11 - $r;
  // second digit
  $s = 0;
  for ($i = 0; $i < 9; $i++) $s += $n[$i] * (11 - $i);
  $s += $d1 * 2;
  $r = $s % 11;
  $d2 = ($r < 2) ? 0 : 11 - $r;
  $cpf = implode('', $n) . $d1 . $d2;
  return $cpf;
}

function random_brazilian_name()
{
  $first = ["Ana", "Mariana", "João", "Lucas", "Pedro", "Mateus", "Marina", "Beatriz", "Gabriel", "Rafael", "Letícia", "Camila", "Felipe", "Thiago", "Bruno", "Gustavo", "Eduardo", "Carolina", "Sofia", "Laura", "Vinicius", "Marcos", "Daniel", "Vitor", "Diego"];
  $last = ["Silva", "Santos", "Oliveira", "Souza", "Pereira", "Lima", "Costa", "Rodrigues", "Almeida", "Nascimento", "Fernandes", "Ribeiro", "Carvalho", "Gomes", "Martins", "Araújo", "Barbosa", "Rocha", "Dias", "Moreira"];
  return $first[array_rand($first)] . ' ' . $last[array_rand($last)] . (rand(0, 10) === 0 ? ' ' . $last[array_rand($last)] : '');
}

function email_from_name_unique($name, &$usedEmails)
{
  $base = preg_replace('/[^a-z0-9]/i', '.', mb_strtolower($name));
  $base = preg_replace('/\\.+/', '.', $base);
  $base = trim($base, '.');
  $domain = ['gmail.com', 'hotmail.com', 'outlook.com', 'escola.edu.br', 'aluno.edu.br'][array_rand([0, 1, 2, 3, 4])];
  $email = $base . '@' . $domain;
  $try = 0;
  while (isset($usedEmails[$email])) {
    $try++;
    $email = $base . $try . '@' . $domain;
  }
  $usedEmails[$email] = true;
  return $email;
}

function fetch_books_from_google($needed, &$existingIsbns)
{
  $books = [];
  $startIndex = 0;
  $queries = ['livros infantis', 'livros juvenis', 'literatura brasileira', 'história do brasil', 'ciências para crianças', 'aventura infantil', 'contos de fadas', 'educação', 'geografia brasil', 'matemática divertida'];
  // try multiple queries to gather many results
  foreach ($queries as $q) {
    $maxResults = 40;
    for ($si = 0; $si < 200 && count($books) < $needed; $si += $maxResults) {
      $url = "https://www.googleapis.com/books/v1/volumes?q=" . urlencode($q) . "&langRestrict=pt-BR&startIndex=$si&maxResults=$maxResults";
      // simple curl
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 10);
      $resp = curl_exec($ch);
      $err = curl_error($ch);
      curl_close($ch);
      if (!$resp) break;
      $data = json_decode($resp, true);
      if (!isset($data['items'])) break;
      foreach ($data['items'] as $item) {
        $vol = $item['volumeInfo'] ?? [];
        $industry = $vol['industryIdentifiers'] ?? [];
        $isbn = null;
        foreach ($industry as $id) {
          if (isset($id['type']) && isset($id['identifier'])) {
            if (in_array($id['type'], ['ISBN_13', 'ISBN_10'])) {
              $isbn = preg_replace('/[^0-9Xx]/', '', $id['identifier']);
              break;
            }
          }
        }
        if (!$isbn) continue;
        if (isset($existingIsbns[$isbn])) continue;
        $title = $vol['title'] ?? 'Título desconhecido';
        $authors = isset($vol['authors']) ? implode(', ', $vol['authors']) : 'Autor desconhecido';
        $cover = $vol['imageLinks']['thumbnail'] ?? ($vol['imageLinks']['smallThumbnail'] ?? null);
        $desc = $vol['description'] ?? null;
        $categories = isset($vol['categories']) ? implode(', ', $vol['categories']) : null;
        $published = $vol['publishedDate'] ?? '';
        // try extract year
        preg_match('/(\\d{4})/', $published, $m);
        $year = $m[1] ?? date('Y');
        $preview = $vol['previewLink'] ?? null;
        $genre = $categories ? explode(',', $categories)[0] : 'Geral';
        $book = [
          'titulo' => $title,
          'autor' => $authors,
          'isbn' => $isbn,
          'capa_url' => $cover,
          'descricao' => $desc,
          'categoria' => $categories,
          'ano_publicacao' => $year,
          'genero' => $genre,
          'quantidade' => rand(1, 5),
          'preview_link' => $preview
        ];
        $books[] = $book;
        $existingIsbns[$isbn] = true;
        if (count($books) >= $needed) break 3;
      }
    }
  }
  return $books;
}

// --- Leitura do arquivo .sql existente para determinar IDs iniciais ---
$sqlContent = read_file_or_empty($inputSqlFile);

$max_prof = find_max_id_in_inserts($sqlContent, 'professores');
$max_alunos = find_max_id_in_inserts($sqlContent, 'alunos');
$max_livros = find_max_id_in_inserts($sqlContent, 'livros');
$max_emprestimos = find_max_id_in_inserts($sqlContent, 'emprestimos');

$next_prof_id = $max_prof + 1;
$next_aluno_id = $max_alunos + 1;
$next_livro_id = $max_livros + 1;
$next_emprestimo_id = $max_emprestimos + 1;

echo "IDs iniciais detectados:\n";
echo "professores next id = $next_prof_id\n";
echo "alunos next id = $next_aluno_id\n";
echo "livros next id = $next_livro_id\n";
echo "emprestimos next id = $next_emprestimo_id\n";

// --- Gerar professores ---
$usedEmails = [];
$usedCpfs = [];
$professores = [];

for ($i = 0; $i < $totalProfessores; $i++) {
  do {
    $nome = random_brazilian_name();
    $email = email_from_name_unique($nome, $usedEmails);
  } while (isset($usedEmails[$email]) === false ? false : false); // already handled in function

  // CPF unique
  $cpf = generate_cpf();
  while (isset($usedCpfs[$cpf])) $cpf = generate_cpf();
  $usedCpfs[$cpf] = true;

  $professores[] = [
    'id' => $next_prof_id++,
    'nome' => $nome,
    'email' => $email,
    'cpf' => $cpf,
    'senha' => $passwordHash,
    'data_cadastro' => date('Y-m-d H:i:s', strtotime('-' . rand(10, 400) . ' days')),
    'ultimo_login' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 9) . ' days')),
    'ativo' => 1,
    'admin' => 0
  ];
}

// --- Gerar alunos ---
$alunos = [];
$series1 = [];
$series3 = [];
// Turmas A-D
$turmas = ['A', 'B', 'C', 'D'];
for ($s = 0; $s < 4; $s++) $series1[] = "1 Ano " . $turmas[$s];
for ($s = 0; $s < 4; $s++) $series3[] = "3 Ano " . $turmas[$s];

// Split 150 first year and 150 third year
$perTurma1 = intdiv(150, count($series1)); // 37 each (approx)
$perTurma3 = intdiv(150, count($series3));

$alunoCount = 0;
for ($y = 0; $y < 150; $y++) {
  $nome = random_brazilian_name();
  $email = email_from_name_unique($nome, $usedEmails);
  // assign professor randomly
  $prof = $professores[array_rand($professores)];
  // assign serie distributing evenly
  $serie = $series1[$y % count($series1)];
  $alunos[] = [
    'id' => $next_aluno_id++,
    'nome' => $nome,
    'serie' => $serie,
    'email' => $email,
    'professor_id' => $prof['id']
  ];
  $alunoCount++;
}
for ($y = 0; $y < 150; $y++) {
  $nome = random_brazilian_name();
  $email = email_from_name_unique($nome, $usedEmails);
  $prof = $professores[array_rand($professores)];
  $serie = $series3[$y % count($series3)];
  $alunos[] = [
    'id' => $next_aluno_id++,
    'nome' => $nome,
    'serie' => $serie,
    'email' => $email,
    'professor_id' => $prof['id']
  ];
  $alunoCount++;
}

// --- Gerar livros (via Google Books API quando possível) ---
$existingIsbns = [];
// detect ISBNs present in input SQL to avoid duplicates
// quick regex to find isbn 'xxx' in INSERTs
if (preg_match_all("/INSERT\\s+INTO\\s+`?livros`?\\s*\\([^)]*\\)\\s*VALUES\\s*(.*?);/is", $sqlContent, $lmatches)) {
  foreach ($lmatches[1] as $valsBlock) {
    $rows = preg_split("/\\)\\s*,\\s*\\(/", trim($valsBlock, "(); \n\r\t"));
    foreach ($rows as $row) {
      $cols = null; // we don't know column order; simple: try find isbn pattern in the row
      if (preg_match("/'([0-9Xx-]{7,})'/", $row, $m)) {
        $maybe = preg_replace('/[^0-9Xx]/', '', $m[1]);
        if (strlen($maybe) >= 7) $existingIsbns[$maybe] = true;
      }
    }
  }
}

echo "Buscando livros na Google Books API (tentando obter $totalLivrosDesejados)...\n";
$books = [];
// attempt fetch (curl must be available)
$books = fetch_books_from_google($totalLivrosDesejados, $existingIsbns);

// fallback synthetic books if not enough
while (count($books) < $totalLivrosDesejados) {
  $i = count($books) + 1;
  $isbn = str_pad(rand(1000000000000, 9999999999999), 13, '0', STR_PAD_LEFT) . $i;
  if (isset($existingIsbns[$isbn])) continue;
  $existingIsbns[$isbn] = true;
  $books[] = [
    'titulo' => "Livro Sintético $i",
    'autor' => "Autor Sintético",
    'isbn' => $isbn,
    'capa_url' => null,
    'descricao' => "Descrição sintética para o livro $i.",
    'categoria' => "Geral",
    'ano_publicacao' => strval(rand(1990, 2025)),
    'genero' => "Geral",
    'quantidade' => rand(1, 5),
    'preview_link' => null
  ];
}

$livros = [];
foreach ($books as $b) {
  $livros[] = array_merge(['id' => $next_livro_id++], $b);
}

// --- Gerar emprestimos ---
$emprestimos = [];
$alunoIds = array_map(function ($a) {
  return $a['id'];
}, $alunos);
$livroIds = array_map(function ($l) {
  return $l['id'];
}, $livros);
$profIds = array_map(function ($p) {
  return $p['id'];
}, $professores);

for ($i = 0; $i < $totalEmprestimos; $i++) {
  $alunoId = $alunoIds[array_rand($alunoIds)];
  $livroId = $livroIds[array_rand($livroIds)];
  $profId = $profIds[array_rand($profIds)];
  // random borrow date within last 365 days
  $daysAgo = rand(0, 365);
  $dataEmp = date('Y-m-d', strtotime("-$daysAgo days"));
  // devolucao optional: 70% returned
  if (rand(1, 100) <= 70) {
    $loanDays = rand(1, 30);
    $dataDev = date('Y-m-d', strtotime("$dataEmp + $loanDays days"));
    $devolvido = 'sim';
  } else {
    $dataDev = 'NULL';
    $devolvido = 'nao';
  }
  $emprestimos[] = [
    'id' => $next_emprestimo_id++,
    'aluno_id' => $alunoId,
    'livro_id' => $livroId,
    'data_emprestimo' => $dataEmp,
    'data_devolucao' => $dataDev,
    'devolvido' => $devolvido,
    'professor_id' => $profId
  ];
}

try {
    // começa transação
    $pdo->beginTransaction();

    // --- Inserir professores (usando IDs gerados no script) ---
    $stmtProf = $pdo->prepare("
        INSERT INTO professores (id,nome,email,cpf,senha,data_cadastro,ultimo_login,ativo,admin)
        VALUES (:id,:nome,:email,:cpf,:senha,:data_cadastro,:ultimo_login,:ativo,:admin)
    ");
    foreach ($professores as $p) {
        $stmtProf->execute([
            ':id'=>$p['id'],
            ':nome'=>$p['nome'],
            ':email'=>$p['email'],
            ':cpf'=>$p['cpf'],
            ':senha'=>$p['senha'],
            ':data_cadastro'=>$p['data_cadastro'],
            ':ultimo_login'=>$p['ultimo_login'],
            ':ativo'=>$p['ativo'],
            ':admin'=>$p['admin']
        ]);
    }

    // --- Inserir alunos (garantir professor_id válido) ---
    $stmtAluno = $pdo->prepare("
        INSERT INTO alunos (id,nome,serie,email,professor_id)
        VALUES (:id,:nome,:serie,:email,:professor_id)
    ");
    $checkProf = $pdo->prepare("SELECT COUNT(*) FROM professores WHERE id = :id");
    foreach ($alunos as $a) {
        // checa professor
        $profId = $a['professor_id'];
        $checkProf->execute([':id'=>$profId]);
        if ($checkProf->fetchColumn() == 0) {
            throw new Exception("Professor id {$profId} inexistente para aluno {$a['nome']}");
        }
        $stmtAluno->execute([
            ':id'=>$a['id'],
            ':nome'=>$a['nome'],
            ':serie'=>$a['serie'],
            ':email'=>$a['email'],
            ':professor_id'=>$profId
        ]);
    }

    // --- Inserir livros ---
    $stmtLivro = $pdo->prepare("
        INSERT INTO livros (id,titulo,autor,isbn,capa_url,descricao,categoria,ano_publicacao,genero,quantidade,preview_link)
        VALUES (:id,:titulo,:autor,:isbn,:capa_url,:descricao,:categoria,:ano_publicacao,:genero,:quantidade,:preview_link)
    ");
    foreach ($livros as $l) {
        $stmtLivro->execute([
            ':id'=>$l['id'],
            ':titulo'=>$l['titulo'],
            ':autor'=>$l['autor'],
            ':isbn'=>$l['isbn'],
            ':capa_url'=>$l['capa_url'],
            ':descricao'=>$l['descricao'],
            ':categoria'=>$l['categoria'],
            ':ano_publicacao'=>$l['ano_publicacao'],
            ':genero'=>$l['genero'],
            ':quantidade'=>$l['quantidade'],
            ':preview_link'=>$l['preview_link']
        ]);
    }

    // --- Inserir emprestimos (checar aluno/livro/professor existem) ---
    $stmtEmp = $pdo->prepare("
        INSERT INTO emprestimos (id,aluno_id,livro_id,data_emprestimo,data_devolucao,devolvido,professor_id)
        VALUES (:id,:aluno_id,:livro_id,:data_emprestimo,:data_devolucao,:devolvido,:professor_id)
    ");
    $checkAluno = $pdo->prepare("SELECT COUNT(*) FROM alunos WHERE id = :id");
    $checkLivro = $pdo->prepare("SELECT COUNT(*) FROM livros WHERE id = :id");
    foreach ($emprestimos as $e) {
        if ($e['aluno_id'] !== null) {
            $checkAluno->execute([':id'=>$e['aluno_id']]);
            if ($checkAluno->fetchColumn() == 0) throw new Exception("Aluno id {$e['aluno_id']} inexistente (emprestimo {$e['id']})");
        }
        if ($e['livro_id'] !== null) {
            $checkLivro->execute([':id'=>$e['livro_id']]);
            if ($checkLivro->fetchColumn() == 0) throw new Exception("Livro id {$e['livro_id']} inexistente (emprestimo {$e['id']})");
        }
        // professor
        $checkProf->execute([':id'=>$e['professor_id']]);
        if ($checkProf->fetchColumn() == 0) throw new Exception("Professor id {$e['professor_id']} inexistente (emprestimo {$e['id']})");
        $stmtEmp->execute([
            ':id'=>$e['id'],
            ':aluno_id'=>$e['aluno_id'],
            ':livro_id'=>$e['livro_id'],
            ':data_emprestimo'=>$e['data_emprestimo'],
            ':data_devolucao'=>($e['data_devolucao']==='NULL' ? null : $e['data_devolucao']),
            ':devolvido'=>$e['devolvido'],
            ':professor_id'=>$e['professor_id']
        ]);
    }

    // tudo ok → confirma
    $pdo->commit();
    echo "Inserção concluída com sucesso (transaction commit).\n";
} catch (Exception $ex) {
    // desfaz tudo
    $pdo->rollBack();
    echo "Erro durante inserção, transação revertida: " . $ex->getMessage() . "\n";
    exit(1);
}

// --- Escrever arquivo SQL de saída ---
$out = fopen($outputSqlFile, 'w');
if (!$out) {
  echo "Erro ao criar $outputSqlFile\n";
  exit(1);
}

fwrite($out, "-- Arquivo gerado por gera_dados_biblioteca.php\n");
fwrite($out, "-- Gera $totalProfessores professores, $totalAlunos alunos, " . count($livros) . " livros, $totalEmprestimos empréstimos\n\n");

// Professores
fwrite($out, "-- Inserções para professores\n");
$chunk = [];
foreach ($professores as $p) {
  $vals = "(" .
    $p['id'] . ", '" .
    sql_escape($p['nome']) . "', '" .
    sql_escape($p['email']) . "', '" .
    sql_escape($p['cpf']) . "', '" .
    sql_escape($p['senha']) . "', '" .
    $p['data_cadastro'] . "', '" .
    $p['ultimo_login'] . "', " .
    $p['ativo'] . ", " .
    $p['admin'] . ")";
  $chunk[] = $vals;
  if (count($chunk) >= 50) {
    fwrite($out, "INSERT INTO `professores` (`id`,`nome`,`email`,`cpf`,`senha`,`data_cadastro`,`ultimo_login`,`ativo`,`admin`) VALUES\n" . implode(",\n", $chunk) . ";\n\n");
    $chunk = [];
  }
}
if (count($chunk) > 0) {
  fwrite($out, "INSERT INTO `professores` (`id`,`nome`,`email`,`cpf`,`senha`,`data_cadastro`,`ultimo_login`,`ativo`,`admin`) VALUES\n" . implode(",\n", $chunk) . ";\n\n");
}

// Alunos
fwrite($out, "-- Inserções para alunos\n");
$chunk = [];
foreach ($alunos as $a) {
  $profIdPart = $a['professor_id'] ? $a['professor_id'] : 'NULL';
  $vals = "(" .
    $a['id'] . ", '" .
    sql_escape($a['nome']) . "', '" .
    sql_escape($a['serie']) . "', '" .
    sql_escape($a['email']) . "', " .
    $profIdPart .
    ")";
  $chunk[] = $vals;
  if (count($chunk) >= 50) {
    fwrite($out, "INSERT INTO `alunos` (`id`,`nome`,`serie`,`email`,`professor_id`) VALUES\n" . implode(",\n", $chunk) . ";\n\n");
    $chunk = [];
  }
}
if (count($chunk) > 0) {
  fwrite($out, "INSERT INTO `alunos` (`id`,`nome`,`serie`,`email`,`professor_id`) VALUES\n" . implode(",\n", $chunk) . ";\n\n");
}

// Livros
fwrite($out, "-- Inserções para livros\n");
$chunk = [];
foreach ($livros as $l) {
  $vals = "(" .
    $l['id'] . ", '" .
    sql_escape($l['titulo']) . "', '" .
    sql_escape($l['autor']) . "', '" .
    sql_escape($l['isbn']) . "', " .
    ($l['capa_url'] ? ("'" . sql_escape($l['capa_url']) . "'") : "NULL") . ", " .
    ($l['descricao'] ? ("'" . sql_escape($l['descricao']) . "'") : "NULL") . ", " .
    ($l['categoria'] ? ("'" . sql_escape($l['categoria']) . "'") : "NULL") . ", " .
    "'" . sql_escape($l['ano_publicacao']) . "', '" .
    sql_escape($l['genero']) . "', " .
    intval($l['quantidade']) . ", " .
    ($l['preview_link'] ? ("'" . sql_escape($l['preview_link']) . "'") : "NULL") .
    ")";
  $chunk[] = $vals;
  if (count($chunk) >= 50) {
    fwrite($out, "INSERT INTO `livros` (`id`,`titulo`,`autor`,`isbn`,`capa_url`,`descricao`,`categoria`,`ano_publicacao`,`genero`,`quantidade`,`preview_link`) VALUES\n" . implode(",\n", $chunk) . ";\n\n");
    $chunk = [];
  }
}
if (count($chunk) > 0) {
  fwrite($out, "INSERT INTO `livros` (`id`,`titulo`,`autor`,`isbn`,`capa_url`,`descricao`,`categoria`,`ano_publicacao`,`genero`,`quantidade`,`preview_link`) VALUES\n" . implode(",\n", $chunk) . ";\n\n");
}

// Emprestimos
fwrite($out, "-- Inserções para emprestimos\n");
$chunk = [];
foreach ($emprestimos as $e) {
  $devPart = ($e['data_devolucao'] === 'NULL') ? "NULL" : ("'" . $e['data_devolucao'] . "'");
  $vals = "(" .
    $e['id'] . ", " .
    ($e['aluno_id'] ? $e['aluno_id'] : 'NULL') . ", " .
    ($e['livro_id'] ? $e['livro_id'] : 'NULL') . ", '" .
    $e['data_emprestimo'] . "', " .
    $devPart . ", '" .
    sql_escape($e['devolvido']) . "', " .
    ($e['professor_id'] ? $e['professor_id'] : 'NULL') .
    ")";
  $chunk[] = $vals;
  if (count($chunk) >= 50) {
    fwrite($out, "INSERT INTO `emprestimos` (`id`,`aluno_id`,`livro_id`,`data_emprestimo`,`data_devolucao`,`devolvido`,`professor_id`) VALUES\n" . implode(",\n", $chunk) . ";\n\n");
    $chunk = [];
  }
}
if (count($chunk) > 0) {
  fwrite($out, "INSERT INTO `emprestimos` (`id`,`aluno_id`,`livro_id`,`data_emprestimo`,`data_devolucao`,`devolvido`,`professor_id`) VALUES\n" . implode(",\n", $chunk) . ";\n\n");
}

fclose($out);

echo "Arquivo gerado: $outputSqlFile\n";
echo "Lembre-se: importe esse arquivo após garantir que sua base atual aceita os IDs usados (evite colisões se já houver muitos registros).\n";
echo "Senha dos professores (plaintext): $passwordPlain (armazenada como hash bcrypt no campo senha).\n";
