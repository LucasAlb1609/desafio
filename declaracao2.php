<?php
require 'C:\xampp\htdocs\desafio\\vendor\autoload.php';
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
        $templateProcessor->setValue('NOME_COMPLETO', $nomeCompleto);
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

        // Inicializar o mPDF
        $mpdf = new Mpdf();

        // Carregar o conteúdo HTML
        $htmlContent = file_get_contents($htmlTempFile);

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

