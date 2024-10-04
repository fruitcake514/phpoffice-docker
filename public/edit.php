<?php
session_start();
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PHPPresentation\IOFactory as PresentationIOFactory;
use PhpOffice\PHPPresentation\PHPPresentation;

$mountPath = '../mnt';

$file = $_GET['file'] ?? null;
$filePath = $mountPath . '/' . $file;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save'])) {
    $newFileName = $_POST['file_name'];
    $fileType = $_POST['file_type'];
    
    if ($fileType === 'spreadsheet') {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->setCellValue('A1', $_POST['content']);
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($mountPath . '/' . $newFileName);
    } elseif ($fileType === 'word') {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $section->addText($_POST['content']);
        $writer = WordIOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($mountPath . '/' . $newFileName);
    } elseif ($fileType === 'presentation') {
        $presentation = new PHPPresentation();
        $slide = $presentation->getActiveSlide();
        $shape = $slide->createRichTextShape();
        $shape->createTextRun($_POST['content']);
        $writer = PresentationIOFactory::createWriter($presentation, 'PowerPoint2007');
        $writer->save($mountPath . '/' . $newFileName);
    }

    header('Location: file_browser.php');
}

$content = '';
$fileType = '';

if ($file) {
    $fileType = pathinfo($file, PATHINFO_EXTENSION);
    
    if ($fileType === 'xlsx') {
        $spreadsheet = IOFactory::load($filePath);
        $content = $spreadsheet->getActiveSheet()->getCell('A1')->getValue();
    } elseif ($fileType === 'docx') {
        $phpWord = WordIOFactory::load($filePath);
        $content = $phpWord->getSections()[0]->getElements()[0]->getText();
    } elseif ($fileType === 'pptx') {
        $presentation = PresentationIOFactory::load($filePath);
        $content = $presentation->getSlides()[0]->getShapeCount(); // Get content based on your need
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles.css">
    <title>Edit File</title>
</head>
<body>
    <div class="container">
        <h1>Edit File: <?php echo htmlspecialchars($file); ?></h1>
        <form method="POST">
            <input type="text" name="file_name" value="<?php echo htmlspecialchars($file); ?>" required>
            <input type="hidden" name="file_type" value="<?php echo htmlspecialchars($fileType); ?>">
            <textarea name="content" rows="10" cols="30" required><?php echo htmlspecialchars($content); ?></textarea>
            <button type="submit" name="save">Save</button>
        </form>
        <a href="file_browser.php">Back to File Browser</a>
    </div>
      <script src="assets/script.js"></script>
</body>
</html>
