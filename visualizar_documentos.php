<?php
include 'conexao.php';
include 'permissoes.php'; 
checarPermissoes($conn, [1, 2]);// Verifica se o usuário é de nível 1 ou 2

$result = $conn->query("
    SELECT d.id, p.cpf, p.nome, d.nome_arquivo, d.data_envio 
    FROM documentos d
    JOIN pessoas p ON d.pessoa_id = p.id
    ORDER BY d.data_envio DESC
");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Documentos Enviados</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <h2>Documentos Enviados</h2>
    <table>
        <thead>
            <tr>
                <th>CPF</th>
                <th>Nome</th>
                <th>Documento</th>
                <th>Data de Envio</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['cpf']) ?></td>
                    <td><?= htmlspecialchars($row['nome']) ?></td>
                    <td>
                        <a href="uploads/<?= htmlspecialchars($row['nome_arquivo']) ?>" target="_blank">
                            <?= htmlspecialchars($row['nome_arquivo']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($row['data_envio']) ?></td>
                    <td>
                        <a href="excluir_documento.php?id=<?= $row['id'] ?>" onclick="return confirm('Deseja realmente excluir este documento?')">Excluir</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>
