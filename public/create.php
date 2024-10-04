<?php
session_start();
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PHPPresentation\PHPPresentation;
use PhpOffice\PHPPresentation\IOFactory as PresentationIOFactory;

$mountPath = '../mnt';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create'])) {
    $fileName = basename($_POST['file_name']);
    $fileType = $_POST['file_type'];
    $content = $_POST['content'] ?? '';

    if ($fileType === 'spreadsheet') {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->setCellValue('A1', $content);
        $writer = new Xlsx($spreadsheet);
        $writer->save($mountPath . '/' . $fileName . '.xlsx');
    } elseif ($fileType === 'word') {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $section->addText($content);
        $writer = WordIOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($mountPath . '/' . $fileName . '.docx');
    } elseif ($fileType === 'presentation') {
        $presentation = new PHPPresentation();
        $slide = $presentation->getActiveSlide();
        $shape = $slide->createRichTextShape();
        $shape->createTextRun($content);
        $writer = PresentationIOFactory::createWriter($presentation, 'PowerPoint2007');
        $writer->save($mountPath . '/' . $fileName . '.pptx');
    }

    // Redirect to the file browser after creation
    header('Location: file_browser.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles.css">
    <title>Create File</title>
</head>
<body>
    <div class="container">
        <h1>Create a New File</h1>
        <form method="POST">
            <label for="file_name">File Name:</label>
            <input type="text" name="file_name" required placeholder="Enter file name">

            <label for="file_type">File Type:</label>
            <select name="file_type" required>
                <option value="spreadsheet">Spreadsheet (Excel)</option>
                <option value="word">Word Document</option>
                <option value="presentation">Presentation (PowerPoint)</option>
            </select>

            <label for="content">Content:</label>
            <textarea name="content" rows="10" cols="30" placeholder="Enter initial content here (optional)"></textarea>

            <button type="submit" name="create">Create File</button>
        </form>
        <a href="file_browser.php">Back to File Browser</a>
    </div>
    <script src="assets/script.js"></script>
</body>
</html>
