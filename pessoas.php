<?php
include 'conexao.php';

// Função para adicionar pessoa e cidade sem duplicação
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nome'], $_POST['sobrenome'], $_POST['cidade'])) {
    $nome = $_POST['nome'];
    $sobrenome = $_POST['sobrenome'];
    $cidade = $_POST['cidade'];

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

    // Inserir a pessoa com o ID da cidade
    $pessoaInsert = $conn->prepare("INSERT INTO pessoas (nome, sobrenome, cidade_id) VALUES (?, ?, ?)");
    $pessoaInsert->bind_param("ssi", $nome, $sobrenome, $cidadeId);
    $pessoaInsert->execute();
}

// Função para atualizar nome, sobrenome ou cidade
if (isset($_POST['editar']) && isset($_POST['novo_nome'], $_POST['novo_sobrenome'], $_POST['nova_cidade'])) {
    $pessoaId = $_POST['pessoa_id'];
    $novoNome = $_POST['novo_nome'];
    $novoSobrenome = $_POST['novo_sobrenome'];
    $novaCidade = $_POST['nova_cidade'];

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
    $pessoaUpdate = $conn->prepare("UPDATE pessoas SET nome = ?, sobrenome = ?, cidade_id = ? WHERE id = ?");
    $pessoaUpdate->bind_param("ssii", $novoNome, $novoSobrenome, $cidadeId, $pessoaId);
    $pessoaUpdate->execute();
}

// Obter a lista de pessoas e cidades
$pessoasQuery = $conn->query("SELECT p.id, p.nome, p.sobrenome, c.nome AS cidade FROM pessoas p JOIN cidades c ON p.cidade_id = c.id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pessoas e Cidades</title>
</head>
<body>
    <h2>Adicionar Pessoa</h2>
    <form method="POST">
        Nome: <input type="text" name="nome" required>
        Sobrenome: <input type="text" name="sobrenome" required>
        Cidade: <input type="text" name="cidade" required>
        <button type="submit">Adicionar</button>
    </form>

    <h2>Pessoas Cadastradas</h2>
    <table border="1">
        <tr>
            <th>Nome</th>
            <th>Sobrenome</th>
            <th>Cidade</th>
            <th>Ações</th>
        </tr>
        <?php while ($row = $pessoasQuery->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['nome'] ?></td>
                <td><?= $row['sobrenome'] ?></td>
                <td><?= $row['cidade'] ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="pessoa_id" value="<?= $row['id'] ?>">
                        Novo Nome: <input type="text" name="novo_nome" value="<?= $row['nome'] ?>">
                        Novo Sobrenome: <input type="text" name="novo_sobrenome" value="<?= $row['sobrenome'] ?>">
                        Nova Cidade: <input type="text" name="nova_cidade" value="<?= $row['cidade'] ?>">
                        <button type="submit" name="editar">Editar</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
