<?php
// Conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$database = "minha_database";

$conn = new mysqli($servername, $username, $password, $database);

// Verifica conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$resultado = null;
$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpf = $_POST['cpf'];

    // Validação básica de CPF (formato com pontos e traço)
    if (!preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', $cpf)) {
        $erro = "Formato de CPF inválido. Use o formato 000.000.000-00.";
    } else {
        // Escapa o CPF para evitar SQL Injection
        $cpf = $conn->real_escape_string($cpf);

        // Busca o ID da pessoa com o CPF fornecido
        $queryPessoa = "SELECT id FROM pessoas WHERE cpf = '$cpf'";
        $resultPessoa = $conn->query($queryPessoa);

        if ($resultPessoa->num_rows > 0) {
            $pessoa = $resultPessoa->fetch_assoc();
            $pessoaId = $pessoa['id'];

            // Busca na tabela vinculo os dados correspondentes ao pessoa_id
            $queryVinculo = "SELECT id, data_Inicio, data_Final, code_Id FROM vinculo WHERE pessoa_id = $pessoaId";
            $resultVinculo = $conn->query($queryVinculo);

            if ($resultVinculo->num_rows > 0) {
                $resultado = $resultVinculo->fetch_all(MYSQLI_ASSOC);
            } else {
                $erro = "Nenhum vínculo encontrado para o CPF informado.";
            }
        } else {
            $erro = "CPF não encontrado na tabela pessoas.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta por CPF</title>
</head>
<body>
    <h1>Consulta por CPF</h1>
    <form method="post" action="">
        <label for="cpf">Digite o CPF:</label>
        <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" required>
        <button type="submit">Buscar</button>
    </form>

    <?php if ($erro): ?>
        <p style="color: red;"><?php echo $erro; ?></p>
    <?php elseif ($resultado): ?>
        <h2>Resultados:</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>ID do Vínculo</th>
                    <th>Data de Início</th>
                    <th>Data Final</th>
                    <th>Hash Code</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultado as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['data_Inicio']); ?></td>
                        <td><?php echo htmlspecialchars($row['data_Final']); ?></td>
                        <td><?php echo htmlspecialchars($row['code_Id']); ?></td>
                        <td>
                            <form action="gerar_pdf.php" method="post" target="_blank">
                                <input type="hidden" name="vinculo_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                <button type="submit">Imprimir PDF</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</body>
</html>