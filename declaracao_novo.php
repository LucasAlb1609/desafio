<?php
require 'C:\xampp\htdocs\desafio\\vendor\autoload.php';
require_once __DIR__ . '/vendor/autoload.php';
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
        $templateProcessor = new TemplateProcessor('C:\\xampp\\htdocs\\desafio\\templates\\template.docx');

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

        // Definir o cabeçalho e rodapé diretamente usando o HTML gerado do Word
        $headerHtml = '
                    <table width="100%" style="border-collapse: collapse;">
                        <tr>
                            <td style="width: 20%; text-align: left;">
                                <img src="c:/xampp/htdocs/logo_ead.png" alt="Header Image EAD" style="height: 50px; width: auto;" />
                            </td>
                            <td style="width: 60%; text-align: center;">
                                <strong>Universidade Federal Rural de Pernambuco - UFRPE</strong><br>
                                Unidade Acadêmica de Educação a Distância e Tecnologia
                            </td>
                            <td style="width: 20%; text-align: right;">
                                <img src="c:/xampp/htdocs/logo_edu.png" alt="Header Image EDU" style="height: 50px; width: auto;" />
                            </td>
                        </tr>
                    </table>';


        $footerHtml = '<div style="text-align: center;">
        <p>Unidade Acadêmica de Educação a Distância e Tecnologia
        <p>Rua Dom Manoel de Medeiros, s/n, Dois Irmãos - CEP: 52171-900 - Recife/ PE
        <p>Tel.: (81) 3320.6103 - www.ead.ufrpe.br 
        </div>';

        $mpdf->SetHTMLHeader($headerHtml);
        $mpdf->SetHTMLFooter($footerHtml);
        
        // Escrever conteúdo HTML para o PDF
        $mpdf->WriteHTML($htmlContent);

        // Definir o caminho do arquivo PDF a ser salvo
        $pdfFilePath = 'C:\\xampp\\htdocs\\desafio\\downloads\\declaracao_' . uniqid() . '.pdf';
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
    <h1>Gerar Declaração</h1>
    <form action="declaracao_novo.php" method="post">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required><br><br>
        <label for="sobrenome">Sobrenome:</label>
        <input type="text" id="sobrenome" name="sobrenome" required><br><br>
        <button type="submit">Gerar Declaração</button>
    </form>
</body>
</html>
