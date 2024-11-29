<?php
// Conexão com o banco de dados
$host = 'localhost'; // Ajuste conforme necessário
$user = 'root';      // Usuário do banco
$password = '';      // Senha do banco
$database = 'minha_database';

$conn = new mysqli($host, $user, $password, $database);

// Verifica se a conexão foi bem-sucedida
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpf = $_POST['cpf'];

    // Mantém o CPF formatado para a consulta
    $stmt = $conn->prepare("SELECT id FROM pessoas WHERE cpf = ?");
    $stmt->bind_param('s', $cpf);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // CPF encontrado, redireciona para a página menu.php
        header("Location: menu.php");
        exit;
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
        input[type="text"] {
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
        <h2>Login</h2>
        <form method="POST" action="">
            <label for="cpf">CPF:</label>
            <input type="text" id="cpf" name="cpf" maxlength="14" oninput="formatCPF(this)" required>
            <button type="submit">Entrar</button>
            <?php if ($erro): ?>
                <p class="error"><?= htmlspecialchars($erro) ?></p>
            <?php endif; ?>
        </form>
        <form action="pessoas.php" method="GET">
            <button type="submit" class="btn-cadastro">Cadastro</button>
        </form>
        <p class="cadastro-legenda">Novo usuário? Clique aqui para se cadastrar.</p>
    </div>
</body>
</html>
