<?php
include 'conexao.php';

$cidadesQuery = $conn->query("SELECT id, nome FROM cidades");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Listagem por Cidade</title>
</head>
<body>
    <h2>Cidades e Seus Residentes</h2>
    <table border="1">
        <tr>
            <th>Cidade</th>
            <th>Residentes</th>
        </tr>
        <?php while ($cidade = $cidadesQuery->fetch_assoc()) { ?>
            <tr>
                <td><?= $cidade['nome'] ?></td>
                <td>
                    <?php
                    $pessoasQuery = $conn->prepare("SELECT nome, sobrenome FROM pessoas WHERE cidade_id = ?");
                    $pessoasQuery->bind_param("i", $cidade['id']);
                    $pessoasQuery->execute();
                    $pessoasResult = $pessoasQuery->get_result();

                    while ($pessoa = $pessoasResult->fetch_assoc()) {
                        echo $pessoa['nome'] . " " . $pessoa['sobrenome'] . "<br>";
                    }
                    ?>
                </td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
