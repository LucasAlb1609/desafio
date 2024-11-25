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
    $cpf = $_POST['cpf'];

    // Consultar o banco de dados pela CPF
    $stmt = $conn->prepare("SELECT p.id, p.nome, p.sobrenome, p.cpf, c.nome AS cidade FROM pessoas p JOIN cidades c ON p.cidade_id = c.id WHERE p.cpf = ?");
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $dados = $result->fetch_assoc();
        $nomeCompleto = $dados['nome'] . ' ' . $dados['sobrenome'];
        $cpf = $dados['cpf']; // Captura o CPF
        $cidade = $dados['cidade'];
        $pessoa_id = $dados['id']; // ID da pessoa
        $dataAtual = date('d') . " de " . date('F') . " de " . date('Y');

        // Consultar o code_id (hash code) na tabela vinculo
        $stmt2 = $conn->prepare("SELECT code_id FROM vinculo WHERE pessoa_id = ? ORDER BY id DESC LIMIT 1");
        $stmt2->bind_param("i", $pessoa_id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        $codeId = '';
        if ($result2->num_rows > 0) {
            $row2 = $result2->fetch_assoc();
            $codeId = $row2['code_id']; // Captura o hash code
        } else {
            $codeId = 'Nenhum vínculo encontrado'; // Caso não exista vínculo
        }

        // Carregar o template .docx
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(__DIR__ . '/templates/template.docx');

        // Substituir os placeholders com os dados
        $templateProcessor->setValue('NOME', $dados['nome']);
        $templateProcessor->setValue('SOBRENOME', $dados['sobrenome']);
        $templateProcessor->setValue('CIDADE', $cidade);
        $templateProcessor->setValue('CPF', $cpf); // Substitui o placeholder ${CPF}
        $templateProcessor->setValue('CODE_ID', $codeId); // Substitui o placeholder ${CODE_ID}
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
