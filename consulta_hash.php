<?php
include 'conexao.php';
include 'permissao2.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hashCode = $_POST['hash_code'];

    // Consultar os dados da tabela vinculo usando o Hash Code (code_id)
    $stmt = $conn->prepare("SELECT v.data_inicio, v.data_final, v.code_id, p.nome, p.sobrenome, p.cpf
                            FROM vinculo v
                            JOIN pessoas p ON v.pessoa_id = p.id
                            WHERE v.code_id = ?");
    $stmt->bind_param("s", $hashCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Exibir os dados encontrados
        echo "<h2>Dados do Vínculo</h2>";
        echo "<table border='1'>
                <tr>
                    <th>Nome</th>
                    <th>Sobrenome</th>
                    <th>CPF</th>
                    <th>Data de Início</th>
                    <th>Data de Final</th>
                    <th>Hash Code (code_id)</th>
                </tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['nome']}</td>
                    <td>{$row['sobrenome']}</td>
                    <td>{$row['cpf']}</td>
                    <td>{$row['data_inicio']}</td>
                    <td>{$row['data_final']}</td>
                    <td>{$row['code_id']}</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>Nenhum vínculo encontrado para este Hash Code.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Consulta por Hash Code</title>
</head>
<body>
    <h1>Consulta por Hash Code</h1>
    <form action="consulta_hash.php" method="POST">
        <label for="hash_code">Digite o Hash Code (code_id):</label>
        <input type="text" id="hash_code" name="hash_code" required><br><br>
        <button type="submit">Consultar</button>
    </form>
</body>
</html>
