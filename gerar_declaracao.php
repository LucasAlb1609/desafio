<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerar Declaração</title>
</head>
<body>
    <h1>Gerar Declaração</h1>
    <form action="declaracao_novo.php" method="post">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required><br><br>
        <label for="sobrenome">Sobrenome:</label>
        <input type="text" id="sobrenome" name="sobrenome" required><br><br>
        <button type="submit">Gerar Declaração</button>
    </form>
</body>
</html>