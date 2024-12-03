<?php
// Conexão com o banco de dados
include 'conexao.php';
include 'permissao1.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Receber os dados do formulário
    $data_inicio = $_POST['data_inicio'];
    $pessoa_id = $_POST['pessoa_id'];
    $periodo = $_POST['periodo'];

    // Calcular a data_final com base na data_inicio e no período selecionado
    $data_inicio_obj = new DateTime($data_inicio);
    $data_inicio_obj->modify("+$periodo year");
    $data_final = $data_inicio_obj->format('Y-m-d');

    // Consultar a pessoa para obter o CPF
    $stmt2 = $conn->prepare("SELECT cpf FROM pessoas WHERE id = ?");
    $stmt2->bind_param("i", $pessoa_id);  // Usar o ID da pessoa fornecido no formulário
    $stmt2->execute();
    $result = $stmt2->get_result();

    if ($result->num_rows > 0) {
        // Obter o CPF da pessoa
        $row = $result->fetch_assoc();
        $cpf = $row['cpf'];

        echo "CPF: " . $cpf;

        // Gerar o código hash
        $codigoHash = gerarCodigoHash();

        echo "Hash: " . $codigoHash;

        // Inserir dados na tabela vinculo
        $stmt = $conn->prepare("INSERT INTO vinculo (data_inicio, data_final, pessoa_id, code_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $data_inicio, $data_final, $pessoa_id, $codigoHash);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Vínculo cadastrado com sucesso!</p>";
        } else {
            echo "<p style='color: red;'>Erro ao cadastrar vínculo.</p>";
        }
    } else {
        echo "Pessoa não encontrada.";
    }
}

function gerarCodigoHash() {
    $shortCode = date('Ymd') . substr(md5(uniqid()), 0, 12);

    return $shortCode;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Vínculos</title>
    <script>
        // Função para atualizar a data final automaticamente
        function calcularDataFinal() {
            var dataInicio = document.getElementById('data_inicio').value;
            var periodo = document.getElementById('periodo').value;

            if (dataInicio && periodo) {
                var dataInicioObj = new Date(dataInicio);
                dataInicioObj.setFullYear(dataInicioObj.getFullYear() + parseInt(periodo));

                var dia = ("0" + dataInicioObj.getDate()).slice(-2);
                var mes = ("0" + (dataInicioObj.getMonth() + 1)).slice(-2);
                var ano = dataInicioObj.getFullYear();

                document.getElementById('data_final').value = ano + '-' + mes + '-' + dia;
            }
        }

    </script>
</head>
<body>
    <h1>Cadastro de Vínculos</h1>
    <form action="vinculo.php" method="POST">
        <label for="pessoa_id">Pessoa:</label>
        <select name="pessoa_id" id="pessoa_id" required>
            <option value="">Selecione a pessoa</option>
            <?php
            // Consultar todas as pessoas no banco de dados
            $result = $conn->query("SELECT id, nome, sobrenome FROM pessoas");
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['nome']} {$row['sobrenome']}</option>";
            }
            ?>
        </select><br><br>

        <label for="data_inicio">Data de Início:</label>
        <input type="date" name="data_inicio" id="data_inicio" required onchange="calcularDataFinal()">

        <label for="periodo">Período de Vínculo (em anos):</label>
        <select name="periodo" id="periodo" required onchange="calcularDataFinal()">
            <option value="">Selecione o período</option>
            <option value="1">1 ano</option>
            <option value="2">2 anos</option>
            <option value="3">3 anos</option>
            <option value="4">4 anos</option>
        </select><br><br>

        <label for="data_final">Data de Final:</label>
        <input type="date" name="data_final" id="data_final" readonly><br><br>

        <button type="submit">Cadastrar Vínculo</button>
    </form>
</body>
</html>
