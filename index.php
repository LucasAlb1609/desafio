<?php
include 'conexao.php';

$erro = '';

function gerarCodigoHash($senha, $cpf) {
    $dados = $senha . $cpf;
    return hash('sha256', $dados);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpf = $_POST['cpf'];
    $senha = $_POST['senha'];

    // Formata o CPF para o padrão armazenado na base de dados
    $cpf = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', preg_replace('/\D/', '', $cpf));

    $stmt = $conn->prepare("SELECT p.id, p.senha, pr.role_id, p.nome FROM pessoas p
                        LEFT JOIN pessoas_roles pr ON p.id = pr.pessoa_id
                        WHERE p.cpf = ?");
    $stmt->bind_param('s', $cpf);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $hashSalvo = $user['senha'];

        if (empty($hashSalvo)) {
            $erro = "CPF cadastrado, mas sem senha. Clique em 'Criar Senha'.";
        } else {
            $hashInserido = gerarCodigoHash($senha, $cpf);
            if ($hashInserido === $hashSalvo) {
                // Adicionando informações na sessão
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role_id'] = $user['role_id'];  // Armazenando o role_id na sessão
                $_SESSION['nome'] = $user['nome'];

                // Redireciona para o menu principal
                header("Location: menu.php");
                exit;
            } else {
                $erro = "Senha incorreta.";
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
    <title>Login</title>
    <script>
        function formatCPF(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            input.value = value
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        }
    </script>
    <style>
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
        .btn-cadastro {
            margin-top: 10px;
            padding: 8px;
            font-size: 14px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-cadastro:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            font-size: 14px;
        }
        .cadastro-legenda {
            margin-top: 5px;
            font-size: 12px;
            color: #555;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form method="POST" action="">
            <label for="cpf">CPF:</label>
            <input type="text" id="cpf" name="cpf" maxlength="14" oninput="formatCPF(this)" required>
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>
            <button type="submit">Entrar</button>
            <?php if ($erro): ?>
                <p class="error"><?= htmlspecialchars($erro) ?></p>
            <?php endif; ?>
        </form>
        <form action="criar_senha.php" method="GET">
            <button type="submit" class="btn-cadastro">Criar Senha</button>
        </form>
        <p class="cadastro-legenda">Clique aqui para criar uma senha.</p>
    </div>
</body>
</html>
