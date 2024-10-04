<?php
require DIR . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpPresentation\PhpPresentation;

function createSpreadsheet() {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Hello World!');
    
    $writer = new Xlsx($spreadsheet);
    $filename = 'hello_world.xlsx';
    $writer->save($filename);
    return $filename;
}

function createDocument() {
    $phpWord = new PhpWord();
    $section = $phpWord->addSection();
    $section->addText('Hello World!');
    
    $filename = 'hello_world.docx';
    $phpWord->save($filename, 'Word2007');
    return $filename;
}

function createPresentation() {
    $presentation = new PhpPresentation();
    $slide = $presentation->getActiveSlide();
    $shape = $slide->createRichTextShape();
    $shape->createTextRun('Hello World!');
    
    $filename = 'hello_world.pptx';
    $writer = \PhpOffice\PhpPresentation\IOFactory::createWriter($presentation, 'PowerPoint2007');
    $writer->save($filename);
    return $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'spreadsheet':
                $file = createSpreadsheet();
                break;
            case 'document':
                $file = createDocument();
                break;
            case 'presentation':
                $file = createPresentation();
                break;
        }
        if (isset($file)) {
            header("Content-Type: application/octet-stream");
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"" . $file . "\"");
            readfile($file);
            unlink($file);
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHPOffice Suite</title>
</head>
<body>
    <h1>PHPOffice Suite</h1>
    <form method="post">
        <button type="submit" name="action" value="spreadsheet">Create Spreadsheet</button>
        <button type="submit" name="action" value="document">Create Document</button>
        <button type="submit" name="action" value="presentation">Create Presentation</button>
    </form>
</body>
</html>
