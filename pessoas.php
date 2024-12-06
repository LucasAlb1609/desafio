<?php
include 'conexao.php';
include 'permissao1.php'; // Verificação de permissões

// Função para adicionar pessoa com cidade e CPF formatado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nome'], $_POST['sobrenome'], $_POST['cidade'], $_POST['cpf'], $_POST['roles'])) {
    $nome = $_POST['nome'];
    $sobrenome = $_POST['sobrenome'];
    $cidade = $_POST['cidade'];
    $cpf = $_POST['cpf'];
    $roles = $_POST['roles']; // Recebe um array de roles

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
            $cidadeId = $result->fetch_assoc()['id'];
        } else {
            $cidadeInsert = $conn->prepare("INSERT INTO cidades (nome) VALUES (?)");
            $cidadeInsert->bind_param("s", $cidade);
            $cidadeInsert->execute();
            $cidadeId = $conn->insert_id;
        }

        // Inserir a pessoa na tabela `pessoas`
        $pessoaInsert = $conn->prepare("INSERT INTO pessoas (nome, sobrenome, cpf, cidade_id) VALUES (?, ?, ?, ?)");
        $pessoaInsert->bind_param("sssi", $nome, $sobrenome, $cpf, $cidadeId);
        $pessoaInsert->execute();
        $pessoaId = $conn->insert_id;

        // Caso o administrador não tenha atribuído nenhum papel, atribui o papel com ID 3
        if (empty($roles)) {
            $roles = [3]; // Atribui o papel com ID 3 como padrão
        }

        // Inserir os papéis na tabela `pessoas_roles`
        $roleInsert = $conn->prepare("INSERT INTO pessoas_roles (pessoa_id, role_id) VALUES (?, ?)");
        foreach ($roles as $roleId) {
            $roleInsert->bind_param("ii", $pessoaId, $roleId);
            $roleInsert->execute();
        }

        echo "<p style='color: green;'>Pessoa cadastrada com sucesso com os papéis atribuídos!</p>";
    }
}


// Função para adicionar pessoa com cidade e CPF formatado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nome'], $_POST['sobrenome'], $_POST['cidade'], $_POST['cpf'])) {
    $nome = $_POST['nome'];
    $sobrenome = $_POST['sobrenome'];
    $cidade = $_POST['cidade'];
    $cpf = $_POST['cpf'];
    $roles = isset($_POST['roles']) ? $_POST['roles'] : []; // Recebe um array de roles, ou um array vazio se não selecionado

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
            $cidadeId = $result->fetch_assoc()['id'];
        } else {
            $cidadeInsert = $conn->prepare("INSERT INTO cidades (nome) VALUES (?)");
            $cidadeInsert->bind_param("s", $cidade);
            $cidadeInsert->execute();
            $cidadeId = $conn->insert_id;
        }

        // Inserir a pessoa na tabela `pessoas`
        $pessoaInsert = $conn->prepare("INSERT INTO pessoas (nome, sobrenome, cpf, cidade_id) VALUES (?, ?, ?, ?)");
        $pessoaInsert->bind_param("sssi", $nome, $sobrenome, $cpf, $cidadeId);
        $pessoaInsert->execute();
        $pessoaId = $conn->insert_id;

        // Caso o administrador não tenha atribuído nenhum papel, atribui o papel com ID 3
        if (empty($roles)) {
            $roles = [3]; // Atribui o papel com ID 3 como padrão
        }

        // Inserir os papéis na tabela `pessoas_roles`
        $roleInsert = $conn->prepare("INSERT INTO pessoas_roles (pessoa_id, role_id) VALUES (?, ?)");
        foreach ($roles as $roleId) {
            $roleInsert->bind_param("ii", $pessoaId, $roleId);
            $roleInsert->execute();
        }

        echo "<p style='color: green;'>Pessoa cadastrada com sucesso com os papéis atribuídos!</p>";
    }
}


// Função para excluir pessoa
if (isset($_GET['excluir_id'])) {
    $excluirId = $_GET['excluir_id'];

    // Excluir a pessoa e os papéis dela
    $excluirQuery = $conn->prepare("DELETE FROM pessoas WHERE id = ?");
    $excluirQuery->bind_param("i", $excluirId);
    $excluirQuery->execute();
    echo "<p style='color: green;'>Pessoa excluída com sucesso!</p>";
}

// Obter a lista de pessoas e cidades
$pessoasQuery = $conn->query("
    SELECT p.id, p.nome, p.sobrenome, p.cpf, c.nome AS cidade 
    FROM pessoas p 
    JOIN cidades c ON p.cidade_id = c.id
");

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
        Papel(s): 
        <select name="roles[]" multiple>
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
                <td>
                    <?php
                    // Buscar papéis associados a essa pessoa
                    $pessoaRolesQuery = $conn->prepare("SELECT r.nome FROM roles r 
                                                        JOIN pessoas_roles pr ON r.id = pr.role_id 
                                                        WHERE pr.pessoa_id = ?");
                    $pessoaRolesQuery->bind_param("i", $row['id']);
                    $pessoaRolesQuery->execute();
                    $rolesResult = $pessoaRolesQuery->get_result();
                    $roles = [];
                    while ($role = $rolesResult->fetch_assoc()) {
                        $roles[] = $role['nome'];
                    }
                    echo implode(', ', $roles);
                    ?>
                </td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="pessoa_id" value="<?= $row['id'] ?>">
                        Nome: <input type="text" name="novo_nome" value="<?= $row['nome'] ?>" required>
                        Sobrenome: <input type="text" name="novo_sobrenome" value="<?= $row['sobrenome'] ?>" required>
                        CPF: <input type="text" name="novo_cpf" value="<?= $row['cpf'] ?>" required>
                        Cidade: <input type="text" name="nova_cidade" value="<?= $row['cidade'] ?>" required>
                        Papel(s): 
                        <select name="novos_roles[]" multiple required>
                            <?php
                            $rolesQuery = $conn->query("SELECT id, nome FROM roles");
                            while ($role = $rolesQuery->fetch_assoc()) {
                                echo "<option value='" . $role['id'] . "'>" . $role['nome'] . "</option>";
                            }
                            ?>
                        </select>
                        <button type="submit" name="editar">Editar</button>
                    </form>
                    <a href="?excluir_id=<?= $row['id'] ?>" style="color: red;">Excluir</a>
                </td>
            </tr>
        <?php } ?>
    </table>
    
    <script>
        function formatCpf(input) {
            var value = input.value.replace(/\D/g, ''); // Remove tudo o que não for número
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
            }
            input.value = value;
        }
    </script>
</body>
</html>
