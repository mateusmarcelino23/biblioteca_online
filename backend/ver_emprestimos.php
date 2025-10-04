<?php
require '../config.php';
// Consulta para obter todos os livros e suas quantidades
$sql = "SELECT titulo, quantidade FROM livros";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table class='table table-bordered'>";
    echo "<thead><tr><th>Título</th><th>Quantidade</th></tr></thead>";
    echo "<tbody>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row['titulo'] . "</td><td>" . $row['quantidade'] . "</td></tr>";
    }
    echo "</tbody></table>";
} else {
    echo "Nenhum livro encontrado!";
}

$conn->close();
?>
