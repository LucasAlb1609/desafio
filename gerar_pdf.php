<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/phpqrcode/qrlib.php';
include 'conexao.php';

$host = $_SERVER['HTTP_HOST'];
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use Mpdf\Mpdf;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vinculoId = $_POST['vinculo_id'];

    // Consultar dados do vínculo e da pessoa no banco de dados
    $stmt = $conn->prepare("
        SELECT v.code_id, v.data_inicio, v.data_final, 
               p.nome, p.sobrenome, p.cpf, c.nome AS cidade
        FROM vinculo v
        JOIN pessoas p ON v.pessoa_id = p.id
        JOIN cidades c ON p.cidade_id = c.id
        WHERE v.id = ?
    ");
    $stmt->bind_param("i", $vinculoId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $dados = $result->fetch_assoc();
        $nomeCompleto = $dados['nome'] . ' ' . $dados['sobrenome'];
        $cpf = $dados['cpf'];
        $cidade = $dados['cidade'];
        $codeId = $dados['code_id'];
        $dataInicio = $dados['data_inicio'];
        $dataFinal = $dados['data_final'];
        $dataAtual = date('d') . " de " . date('F') . " de " . date('Y');

        // URL de verificação e geração do QR Code
        $verificationUrl = "consulta_hash/$codeId";
        $absoluteUrl = "$scheme://$host/$verificationUrl";
        $qrCodePath = 'templates/qrcode.png';
        QRcode::png($absoluteUrl, $qrCodePath, QR_ECLEVEL_L, 3);

        // Carregar e preencher o template
        $templateProcessor = new TemplateProcessor(__DIR__ . '/templates/template.docx');
        $templateProcessor->setValue('NOME', $dados['nome']);
        $templateProcessor->setValue('SOBRENOME', $dados['sobrenome']);
        $templateProcessor->setValue('CIDADE', $cidade);
        $templateProcessor->setValue('CPF', $cpf);
        $templateProcessor->setValue('CODE_ID', $codeId);
        $templateProcessor->setValue('DATA_INICIO', $dataInicio);
        $templateProcessor->setValue('DATA_FINAL', $dataFinal);
        $templateProcessor->setValue('DATA', $dataAtual);

        $tempFile = tempnam(sys_get_temp_dir(), 'temp_doc') . '.docx';
        $templateProcessor->saveAs($tempFile);

        $phpWord = IOFactory::load($tempFile);
        $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');
        $htmlTempFile = tempnam(sys_get_temp_dir(), 'temp_html') . '.html';
        $htmlWriter->save($htmlTempFile);

        $htmlContent = file_get_contents($htmlTempFile);

        // Remove tags que possam causar problemas de layout
        $htmlContent = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $htmlContent); // Remove CSS interno
        $htmlContent = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $htmlContent); // Remove JavaScript
        $htmlContent = preg_replace('/<link\b[^>]*>/is', '', $htmlContent); // Remove links externos
        $htmlContent = preg_replace('/\{.*?\}/', '', $htmlContent); // Remove possíveis caracteres "{" indesejados

        $mpdf = new Mpdf();
        $mpdf->WriteHTML($htmlContent);
        $mpdf->Image($qrCodePath, 14, 232, 25, 25, 'png');

        // Adicionar o texto diretamente no PDF usando HTML
        $linkHtml = '
        <style>
            .custom-text {
                font-family: Arial;
                font-size: 9pt;
                position: absolute;
                left: 150px;
            }
            .code-text {
                top: 900px;
            }
            .validation-text {
                top: 916px;
            }
            .link-text {
                top: 932px;
            }
        </style>
        <div class="custom-text code-text">Código da declaração: ' . $codeId . '</div>
        <div class="custom-text validation-text">A presente declaração pode ter a sua validade comprovada acessando o QRCode à esquerda, ou,</div>
        <div class="custom-text link-text">acessando o endereço: <a href="' . $absoluteUrl . '">' . $absoluteUrl . '</a></div>
        ';
        
        $mpdf->WriteHTML($linkHtml);

        $mpdf->Output("Declaracao_$codeId.pdf", 'I');

        unlink($tempFile);
        unlink($htmlTempFile);
    } else {
        echo "Vínculo não encontrado.";
    }
}
?>
