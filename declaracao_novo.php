<?php
$autoloadPath = __DIR__ . '/vendor/autoload.php';

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    die('Erro: O arquivo autoload.php não foi encontrado. Execute "composer install" para instalar as dependências.');
}

include 'conexao.php';

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use Mpdf\Mpdf;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $sobrenome = $_POST['sobrenome'];

    // Consultar o banco de dados
    $stmt = $conn->prepare("SELECT p.nome, p.sobrenome, c.nome AS cidade FROM pessoas p JOIN cidades c ON p.cidade_id = c.id WHERE p.nome = ? AND p.sobrenome = ?");
    $stmt->bind_param("ss", $nome, $sobrenome);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $dados = $result->fetch_assoc();
        $nomeCompleto = $dados['nome'] . ' ' . $dados['sobrenome'];
        $cidade = $dados['cidade'];
        $dataAtual = date('d') . " de " . date('F') . " de " . date('Y');

        // Carregar o template .docx
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(__DIR__ . '/templates/template.docx');

        // Substituir os placeholders com os dados
        $templateProcessor->setValue('NOME', $nome);
        $templateProcessor->setValue('SOBRENOME', $sobrenome);
        $templateProcessor->setValue('CIDADE', $cidade);
        $templateProcessor->setValue('DATA', $dataAtual);

        // Salvar o arquivo preenchido em formato .docx temporário
        $tempFile = tempnam(sys_get_temp_dir(), 'temp_doc') . '.docx';
        $templateProcessor->saveAs($tempFile);

        // Gerar HTML a partir do .docx
        $phpWord = IOFactory::load($tempFile);
        $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');
        $htmlTempFile = tempnam(sys_get_temp_dir(), 'temp_html') . '.html';
        $htmlWriter->save($htmlTempFile);
        
        // Carregar o conteúdo HTML
        $htmlContent = file_get_contents($htmlTempFile);

        // Remove tags que possam causar problemas de layout
        $htmlContent = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $htmlContent); // Remove CSS interno
        $htmlContent = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $htmlContent); // Remove JavaScript
        $htmlContent = preg_replace('/<link\b[^>]*>/is', '', $htmlContent); // Remove links externos
        $htmlContent = preg_replace('/\{.*?\}/', '', $htmlContent); // Remove possíveis caracteres "{" indesejados

        // Inicializar o mPDF
        $mpdf = new Mpdf();
        
        // Escrever conteúdo HTML para o PDF
        $mpdf->WriteHTML($htmlContent);

        // Definir o fuso horário do Brasil (horário de Brasília)
        date_default_timezone_set('America/Sao_Paulo');

        // Definir a data e hora atual no formato desejado
        $dateTime = date('Ymd_His');

        // Gerar o caminho do arquivo PDF com data e hora no nome
        $pdfFilePath = __DIR__ . DIRECTORY_SEPARATOR . 'downloads' . DIRECTORY_SEPARATOR . 'declaracao_' . $dateTime . '.pdf';

        $mpdf->Output($pdfFilePath, 'F');

        // Remover arquivos temporários (o template e o HTML gerado)
        unlink($tempFile);
        unlink($htmlTempFile);

        // Gerar o link para o download
        echo "<p>Declaração gerada com sucesso. <a href='downloads/" . basename($pdfFilePath) . "' download>Baixar PDF</a></p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>Pessoa não encontrada no banco de dados.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerar Declaração</title>
</head>
<body>
    <a href="index.php">Voltar para tela inicial</a>
</body>
</html>