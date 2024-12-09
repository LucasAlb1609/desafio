<?php
include 'conexao.php';
include 'permissoes.php';

// Verificar permissões (somente nível 1 pode acessar)
checarPermissoes($conn, [1]);

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomePapel = trim($_POST['nome_papel']);

    // Verificar se o nome do papel foi preenchido
    if (empty($nomePapel)) {
        echo "<p style='color: red;'>O nome do papel não pode estar vazio.</p>";
    } else {
        // Inserir o papel na tabela roles
        $stmt = $conn->prepare("INSERT INTO roles (nome) VALUES (?)");
        $stmt->bind_param("s", $nomePapel);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Papel '$nomePapel' criado com sucesso!</p>";
        } else {
            echo "<p style='color: red;'>Erro ao criar o papel. Tente novamente.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Novo Papel</title>
</head>
<body>
    <h1>Criar Novo Papel</h1>
    <form method="post" action="criar_papel.php">
        <label for="nome_papel">Nome do Papel:</label>
        <input type="text" id="nome_papel" name="nome_papel" required>
        <button type="submit">Criar Papel</button>
    </form>
    <a href="menu.php">Voltar Ao Menu Inicial</a>
</body>
</html>
