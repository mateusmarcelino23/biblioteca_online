#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
gera_alunos_1000.py
Gera um arquivo SQL com 1000 inserts para a tabela `alunos`.
Cria também a tabela se não existir. Charset utf8mb4.
"""

import random
import unicodedata
import os

NUM_ALUNOS = 1000
OUT_SQL_FILE = "alunos_1000.sql"
BATCH_SIZE = 200  # quantos VALUES por INSERT (ajustável)

# listas simples de nomes (incluí alguns com acento)
primeiros = [
    "João", "Maria", "Ana", "Pedro", "Lucas", "Gabriel", "Rafael", "Beatriz",
    "Camila", "Bruno", "Thiago", "Mariana", "Felipe", "Eduardo", "Ricardo",
    "Guilherme", "Vitória", "Larissa", "Amanda", "Daniela", "Sofia", "Matheus",
    "Igor", "Vitor", "Henrique", "Fernando", "Murilo", "Lívia", "Paulo",
    "Isabela", "Carolina", "André", "Clara", "Nicolas", "Bruna", "Sérgio",
    "Lorena", "Diego", "Júlia", "Marcos", "Bianca", "Helena", "Otávio",
    "Caio", "Fabiana", "Alexandre", "Nicole", "Ruan", "Patrícia", "Gustavo",
    "Emanuel", "Renata", "Vânia", "Samuel", "Olívia", "Laura", "Nina", "Ivo",
    "Luan", "Cecília"
]

sobrenomes = [
    "Silva", "Souza", "Lima", "Costa", "Oliveira", "Pereira", "Almeida",
    "Rocha", "Fernandes", "Carvalho", "Santos", "Ribeiro", "Martins", "Gomes",
    "Alves", "Melo", "Castro", "Nunes", "Pinto", "Freitas", "Azevedo",
    "Lopes", "Duarte", "Correia", "Barbosa", "Teixeira", "Moura", "Araújo",
    "Viana", "Mota", "Campos", "Braga", "Santana", "Faria", "Ferreira",
    "Pires", "Branco", "Cardoso", "Moreira", "Nogueira", "Mendes"
]

# séries: números 1..3 e letras A..F (ex.: 3D, 1A, 2C)
numeros_serie = ["1", "2", "3"]
letras_serie = ["A", "B", "C", "D", "E", "F", "G"]

def slugify_email(s):
    """Remove acentos, espaços extras e retorna minúsculo para formar email."""
    s_norm = unicodedata.normalize('NFKD', s)
    s_ascii = "".join(c for c in s_norm if not unicodedata.combining(c))
    s_ascii = s_ascii.lower()
    s_ascii = s_ascii.replace(" ", ".")
    # remove caracteres indesejados
    allowed = "abcdefghijklmnopqrstuvwxyz0123456789._"
    s_clean = "".join(c for c in s_ascii if c in allowed)
    # collapse dots
    while ".." in s_clean:
        s_clean = s_clean.replace("..", ".")
    s_clean = s_clean.strip(".")
    if not s_clean:
        s_clean = "usuario"
    return s_clean

def gerar_nome():
    p = random.choice(primeiros)
    s = random.choice(sobrenomes)
    # às vezes usar um sobrenome composto
    if random.random() < 0.18:
        s2 = random.choice(sobrenomes)
        if s2 != s:
            s = s + " " + s2
    return f"{p} {s}"

def gerar_email(nome, idx):
    base = slugify_email(nome)
    # adiciona um número para evitar colisões
    return f"{base}{idx}@aluno.educacao.sp.gov.br"

def gerar_serie():
    return random.choice(numeros_serie) + random.choice(letras_serie)

def escape_sql(s):
    return s.replace("\\", "\\\\").replace("'", "''")

def gerar_inserts(num_alunos, out_file, batch_size=200):
    lines = []
    # cabeçalho: drop/create table
    header = """-- Arquivo gerado automaticamente: alunos (%d registros)
DROP TABLE IF EXISTS alunos;
CREATE TABLE alunos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  serie VARCHAR(4) NOT NULL,
  email VARCHAR(100) NOT NULL,
  professor_id INT NOT NULL
) CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

""" % (num_alunos,)
    lines.append(header)

    values_batch = []
    for i in range(1, num_alunos + 1):
        nome = gerar_nome()
        serie = gerar_serie()
        email = gerar_email(nome, i)
        professor_id = 1
        nome_esc = escape_sql(nome)
        email_esc = escape_sql(email)
        serie_esc = escape_sql(serie)
        values_batch.append("('%s','%s','%s',%d)" % (nome_esc, serie_esc, email_esc, professor_id))

        if (i % batch_size) == 0 or i == num_alunos:
            insert_stmt = "INSERT INTO alunos (nome, serie, email, professor_id) VALUES\n"
            insert_stmt += ",\n".join(values_batch) + ";\n\n"
            lines.append(insert_stmt)
            values_batch = []

    # grava no arquivo
    with open(out_file, "w", encoding="utf-8") as f:
        f.writelines(lines)

    print(f"Arquivo gerado: {os.path.abspath(out_file)} (contendo {num_alunos} registros)")

if __name__ == "__main__":
    gerar_inserts(NUM_ALUNOS, OUT_SQL_FILE, BATCH_SIZE)

    # --- Opcional: execução direta no MySQL ---
    # Se quiser que o script já conecte e injete diretamente no banco, instale:
    #   pip install mysql-connector-python
    # e descomente a seção abaixo, preenchendo os dados de conexão.
    #
    # OBS: deixei desativado por padrão para não depender de libs externas.
    #
    # import mysql.connector
    # db_config = {
    #     "host": "localhost",
    #     "user": "root",
    #     "password": "",
    #     "database": "mvc_biblioteca",
    #     "charset": "utf8mb4",
    # }
    # conn = mysql.connector.connect(**db_config)
    # cur = conn.cursor()
    # with open(OUT_SQL_FILE, "r", encoding="utf-8") as f:
    #     sql_script = f.read()
    # for stmt in sql_script.split(";"):
    #     stmt = stmt.strip()
    #     if stmt:
    #         cur.execute(stmt + ";")
    # conn.commit()
    # cur.close()
    # conn.close()
    # print("Dados inseridos diretamente no banco.")
