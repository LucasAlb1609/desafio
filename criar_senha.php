<?php
include 'conexao.php';

$erro = '';
$sucesso = '';

function gerarCodigoHash($senha, $cpf) {
    $dados = $senha . $cpf;
    return hash('sha256', $dados);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpf = $_POST['cpf'];
    $senha = $_POST['senha'];

    // Formata o CPF para o padrão armazenado na base de dados
    $cpf = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', preg_replace('/\D/', '', $cpf));

    $stmt = $conn->prepare("SELECT id, senha FROM pessoas WHERE cpf = ?");
    $stmt->bind_param('s', $cpf);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (!empty($user['senha'])) {
            $erro = "Usuário já possui senha cadastrada.";
        } else {
            $novoHash = gerarCodigoHash($senha, $cpf);
            $updateStmt = $conn->prepare("UPDATE pessoas SET senha = ? WHERE id = ?");
            $updateStmt->bind_param('si', $novoHash, $user['id']);
            if ($updateStmt->execute()) {
                $sucesso = "Senha criada com sucesso! Faça login.";
            } else {
                $erro = "Erro ao salvar a senha. Tente novamente.";
            }
        }
    } else {
        $erro = "CPF não encontrado.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Senha</title>
    <style>
        /* Reutilizando o CSS do login */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            max-width: 400px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .btn-login {
            margin-top: 10px;
            background-color: #007BFF;
        }
        .btn-login:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            font-size: 14px;
        }
        .success {
            color: green;
            font-size: 14px;
        }
    </style>
    <script>
        function formatCPF(input) {
            let value = input.value.replace(/\D/g, ''); // Remove caracteres não numéricos
            if (value.length > 11) value = value.slice(0, 11);
            input.value = value
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d{1,2})$/, '$1-$2'); // Formata o CPF
        }
    </script>
</head>
<body>
    <div class="login-container">
        <h2>Criar Senha</h2>
        <form method="POST" action="">
            <label for="cpf">CPF:</label>
            <input type="text" id="cpf" name="cpf" maxlength="14" oninput="formatCPF(this)" required>
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>
            <button type="submit">Criar Senha</button>
        </form>
        <?php if ($erro): ?>
            <p class="error"><?= htmlspecialchars($erro) ?></p>
        <?php elseif ($sucesso): ?>
            <p class="success"><?= htmlspecialchars($sucesso) ?></p>
        <?php endif; ?>
        <form action="index.php" method="GET" style="margin-top: 10px;">
            <button type="submit" class="btn-login">Voltar à tela de login</button>
        </form>
    </div>
</body>
</html>
