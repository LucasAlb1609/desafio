<?php
include 'conexao.php';
include 'permissao1.php'; // Verificação de permissões

// Função para adicionar pessoa com cidade e CPF formatado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nome'], $_POST['sobrenome'], $_POST['cidade'], $_POST['cpf'], $_POST['role'])) {
    $nome = $_POST['nome'];
    $sobrenome = $_POST['sobrenome'];
    $cidade = $_POST['cidade'];
    $cpf = $_POST['cpf'];
    $role = $_POST['role'];

    // Verificar se o CPF já existe
    $cpfQuery = $conn->prepare("SELECT id FROM pessoas WHERE cpf = ?");
    $cpfQuery->bind_param("s", $cpf);
    $cpfQuery->execute();
    $resultCpf = $cpfQuery->get_result();

    if ($resultCpf->num_rows > 0) {
        echo "<p style='color: red;'>CPF já cadastrado!</p>";
    } else {
        // Verificar se a cidade já existe
        $cidadeQuery = $conn->prepare("SELECT id FROM cidades WHERE nome = ?");
        $cidadeQuery->bind_param("s", $cidade);
        $cidadeQuery->execute();
        $result = $cidadeQuery->get_result();

        if ($result->num_rows > 0) {
            // Cidade existe, obter o ID
            $cidadeId = $result->fetch_assoc()['id'];
        } else {
            // Cidade não existe, inserir nova
            $cidadeInsert = $conn->prepare("INSERT INTO cidades (nome) VALUES (?)");
            $cidadeInsert->bind_param("s", $cidade);
            $cidadeInsert->execute();
            $cidadeId = $conn->insert_id;
        }

        // Inserir a pessoa com CPF formatado, ID da cidade e role_id
        $pessoaInsert = $conn->prepare("INSERT INTO pessoas (nome, sobrenome, cpf, cidade_id, role_id) VALUES (?, ?, ?, ?, ?)");
        $pessoaInsert->bind_param("ssssi", $nome, $sobrenome, $cpf, $cidadeId, $role);
        $pessoaInsert->execute();
        echo "<p style='color: green;'>Pessoa cadastrada com sucesso!</p>";
    }
}

// Função para atualizar pessoa
if (isset($_POST['editar']) && isset($_POST['novo_nome'], $_POST['novo_sobrenome'], $_POST['nova_cidade'], $_POST['novo_cpf'], $_POST['novo_role'])) {
    $pessoaId = $_POST['pessoa_id'];
    $novoNome = $_POST['novo_nome'];
    $novoSobrenome = $_POST['novo_sobrenome'];
    $novaCidade = $_POST['nova_cidade'];
    $novoCpf = $_POST['novo_cpf'];
    $novoRole = $_POST['novo_role'];

    // Atualizar a cidade
    $cidadeQuery = $conn->prepare("SELECT id FROM cidades WHERE nome = ?");
    $cidadeQuery->bind_param("s", $novaCidade);
    $cidadeQuery->execute();
    $result = $cidadeQuery->get_result();

    if ($result->num_rows > 0) {
        $cidadeId = $result->fetch_assoc()['id'];
    } else {
        $cidadeInsert = $conn->prepare("INSERT INTO cidades (nome) VALUES (?)");
        $cidadeInsert->bind_param("s", $novaCidade);
        $cidadeInsert->execute();
        $cidadeId = $conn->insert_id;
    }

    // Atualizar a pessoa
    $pessoaUpdate = $conn->prepare("UPDATE pessoas SET nome = ?, sobrenome = ?, cpf = ?, cidade_id = ?, role_id = ? WHERE id = ?");
    $pessoaUpdate->bind_param("sssiii", $novoNome, $novoSobrenome, $novoCpf, $cidadeId, $novoRole, $pessoaId);
    $pessoaUpdate->execute();
}

// Função para excluir pessoa
if (isset($_GET['excluir_id'])) {
    $excluirId = $_GET['excluir_id'];

    // Excluir a pessoa
    $excluirQuery = $conn->prepare("DELETE FROM pessoas WHERE id = ?");
    $excluirQuery->bind_param("i", $excluirId);
    $excluirQuery->execute();
    echo "<p style='color: green;'>Pessoa excluída com sucesso!</p>";
}

// Obter a lista de pessoas e cidades
$pessoasQuery = $conn->query("SELECT p.id, p.nome, p.sobrenome, p.cpf, c.nome AS cidade, r.nome AS papel 
                              FROM pessoas p 
                              JOIN cidades c ON p.cidade_id = c.id
                              JOIN roles r ON p.role_id = r.id");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pessoas e Cidades</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
        }
        input, select, button {
            margin-bottom: 10px;
            padding: 5px;
            width: 200px;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        form {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h2>Adicionar Pessoa</h2>
    <form method="POST">
        Nome: <input type="text" name="nome" required>
        Sobrenome: <input type="text" name="sobrenome" required>
        Cidade: <input type="text" name="cidade" required>
        CPF: <input type="text" name="cpf" oninput="formatCpf(this)" maxlength="14" required>
        Papel: 
        <select name="role" required>
            <?php
            // Exibir papéis (roles) para o dropdown
            $rolesQuery = $conn->query("SELECT id, nome FROM roles");
            while ($role = $rolesQuery->fetch_assoc()) {
                echo "<option value='" . $role['id'] . "'>" . $role['nome'] . "</option>";
            }
            ?>
        </select>
        <button type="submit">Adicionar</button>
    </form>

    <h2>Pessoas Cadastradas</h2>
    <table>
        <tr>
            <th>Nome</th>
            <th>Sobrenome</th>
            <th>CPF</th>
            <th>Cidade</th>
            <th>Papel</th>
            <th>Ações</th>
        </tr>
        <?php while ($row = $pessoasQuery->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['nome'] ?></td>
                <td><?= $row['sobrenome'] ?></td>
                <td><?= $row['cpf'] ?></td>
                <td><?= $row['cidade'] ?></td>
                <td><?= $row['papel'] ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="pessoa_id" value="<?= $row['id'] ?>">
                        Nome: <input type="text" name="novo_nome" value="<?= $row['nome'] ?>" required>
                        Sobrenome: <input type="text" name="novo_sobrenome" value="<?= $row['sobrenome'] ?>" required>
                        CPF: <input type="text" name="novo_cpf" value="<?= $row['cpf'] ?>" oninput="formatCpf(this)" maxlength="14" required>
                        Cidade: <input type="text" name="nova_cidade" value="<?= $row['cidade'] ?>" required>
                        Papel: 
                        <select name="novo_role" required>
                            <?php
                            // Exibir papéis (roles) para o dropdown
                            $rolesQuery = $conn->query("SELECT id, nome FROM roles");
                            while ($role = $rolesQuery->fetch_assoc()) {
                                $selected = ($role['id'] == $row['role_id']) ? 'selected' : '';
                                echo "<option value='" . $role['id'] . "' $selected>" . $role['nome'] . "</option>";
                            }
                            ?>
                        </select>
                        <button type="submit" name="editar">Editar</button>
                    </form>
                    <a href="pessoas.php?excluir_id=<?= $row['id'] ?>" onclick="return confirmDelete()">Excluir</a>
                </td>
            </tr>
        <?php } ?>
    </table>

    <script>
        // Função de confirmação de exclusão
        function confirmDelete() {
            return confirm('Tem certeza de que deseja excluir este usuário?');
        }

        // Formatação do CPF automaticamente
        function formatCpf(input) {
            let value = input.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            input.value = value;
        }
    </script>
</body>
</html>
