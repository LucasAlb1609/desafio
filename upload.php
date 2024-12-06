<?php
include 'conexao.php';
include 'permissoes.php'; // Verifica se o usuário é de nível 3

$pessoaId = $_SESSION['user_id']; // ID do usuário logado

// Enviar documento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['documento'])) {
    $documento = $_FILES['documento'];

    // Verificar se é um PDF válido
    if ($documento['type'] === 'application/pdf') {
        $nomeArquivo = time() . "_" . basename($documento['name']);
        $caminho = "uploads/" . $nomeArquivo;

        if (move_uploaded_file($documento['tmp_name'], $caminho)) {
            // Inserir o registro no banco de dados
            $stmt = $conn->prepare("INSERT INTO documentos (pessoa_id, nome_arquivo) VALUES (?, ?)");
            $stmt->bind_param("is", $pessoaId, $nomeArquivo);
            $stmt->execute();

            echo "<p style='color: green;'>Documento enviado com sucesso!</p>";
        } else {
            echo "<p style='color: red;'>Erro ao salvar o documento.</p>";
        }
    } else {
        echo "<p style='color: red;'>Por favor, envie um arquivo no formato PDF.</p>";
    }
}

// Excluir documento
if (isset($_GET['excluir_id'])) {
    $docId = $_GET['excluir_id'];

    // Verificar se o documento pertence ao usuário
    $stmt = $conn->prepare("SELECT nome_arquivo FROM documentos WHERE id = ? AND pessoa_id = ?");
    $stmt->bind_param("ii", $docId, $pessoaId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $caminho = "uploads/" . $row['nome_arquivo'];

        // Deletar o registro do banco
        $deleteStmt = $conn->prepare("DELETE FROM documentos WHERE id = ?");
        $deleteStmt->bind_param("i", $docId);
        $deleteStmt->execute();

        // Deletar o arquivo físico
        if (file_exists($caminho)) {
            unlink($caminho);
        }

        echo "<p style='color: green;'>Documento excluído com sucesso!</p>";
    } else {
        echo "<p style='color: red;'>Documento não encontrado ou não pertence a você.</p>";
    }
}

// Obter os documentos enviados pelo usuário
$stmt = $conn->prepare("SELECT id, nome_arquivo, data_envio FROM documentos WHERE pessoa_id = ?");
$stmt->bind_param("i", $pessoaId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Enviar Documento</title>
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
    <h2>Enviar Documento</h2>
    <form method="POST" enctype="multipart/form-data">
        <label for="documento">Selecione um documento (PDF):</label>
        <input type="file" name="documento" id="documento" accept="application/pdf" required>
        <button type="submit">Enviar</button>
    </form>

    <h2>Seus Documentos</h2>
    <table>
        <thead>
            <tr>
                <th>Nome do Arquivo</th>
                <th>Data de Envio</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td>
                        <a href="uploads/<?= htmlspecialchars($row['nome_arquivo']) ?>" target="_blank">
                            <?= htmlspecialchars($row['nome_arquivo']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($row['data_envio']) ?></td>
                    <td>
                        <a href="?excluir_id=<?= $row['id'] ?>" onclick="return confirm('Deseja realmente excluir este documento?')">Excluir</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>
