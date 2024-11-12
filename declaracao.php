<?php
// Display all errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure the autoloader is included
require_once __DIR__ . '/vendor/autoload.php';  // Correct path to autoload.php

include 'conexao.php';  // Your database connection
use Mpdf\Mpdf;  // Correct namespace for Mpdf
use PhpOffice\PhpWord\TemplateProcessor;  // Keep using PhpWord

// Handle the POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $sobrenome = $_POST['sobrenome'];

    // Consult the database
    $stmt = $conn->prepare("SELECT p.nome, p.sobrenome, c.nome AS cidade FROM pessoas p JOIN cidades c ON p.cidade_id = c.id WHERE p.nome = ? AND p.sobrenome = ?");
    $stmt->bind_param("ss", $nome, $sobrenome);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $dados = $result->fetch_assoc();
        $nomeCompleto = $dados['nome'] . ' ' . $dados['sobrenome'];
        $cidade = $dados['cidade'];
        $dataAtual = date('d') . " de " . date('F') . " de " . date('Y');

        // Load the template
        $templateProcessor = new TemplateProcessor('C:/xampp/htdocs/desafio/templates/template.docx');

        // Replace placeholders in the template
        $templateProcessor->setValue('NOME_COMPLETO', $nomeCompleto);
        $templateProcessor->setValue('CIDADE', $cidade);
        $templateProcessor->setValue('DATA', $dataAtual);

        // Save the filled template to a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'temp_doc') . '.docx';
        $templateProcessor->saveAs($tempFile);

        // Load the .docx file and prepare it for conversion to PDF
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($tempFile);
        $pdfTempFile = tempnam(sys_get_temp_dir(), 'temp_pdf') . '.pdf';

        // Use mPDF to generate the PDF
        try {
            $mpdf = new \Mpdf\Mpdf();  // Using the fully qualified name for mPDF class
        } catch (\Mpdf\MpdfException $e) {
            echo 'Error initializing mPDF: ' . $e->getMessage();
            exit;
        }

        // Save the HTML content to the PDF
        $html = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
        $html->save($pdfTempFile);

        // Check if the PDF was generated successfully
        if (!file_exists($pdfTempFile)) {
            echo 'Error generating the PDF file';
            exit;
        }

        // Send the PDF to the browser
        header("Content-Type: application/pdf");
        header("Content-Disposition: inline; filename=declaracao.pdf");
        header("Content-Transfer-Encoding: binary");
        header("Accept-Ranges: bytes");

        readfile($pdfTempFile);

        // Clean up the temporary files
        unlink($tempFile);
        unlink($pdfTempFile);
        exit;
    } else {
        echo "<p style='color: red; font-weight: bold;'>Pessoa não encontrada no banco de dados.</p>";
    }
}
?>
