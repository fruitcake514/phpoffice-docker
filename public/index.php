<?php
// Debug mode flag
$debug = true;

// If debug mode is on, display errors
if ($debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

require dirname(__FILE__) . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpPresentation\PhpPresentation;

// Create a temporary directory
$tempDir = sys_get_temp_dir() . '/phpoffice_' . uniqid();
mkdir($tempDir, 0777, true);

function createSpreadsheet($tempDir) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Hello World!');
    
    $writer = new Xlsx($spreadsheet);
    $filename = $tempDir . '/hello_world.xlsx';
    $writer->save($filename);
    return $filename;
}

function createDocument($tempDir) {
    $phpWord = new PhpWord();
    $section = $phpWord->addSection();
    $section->addText('Hello World!');
    
    $filename = $tempDir . '/hello_world.docx';
    $phpWord->save($filename, 'Word2007');
    return $filename;
}

function createPresentation($tempDir) {
    $presentation = new PhpPresentation();
    $slide = $presentation->getActiveSlide();
    $shape = $slide->createRichTextShape();
    $shape->createTextRun('Hello World!');
    
    $filename = $tempDir . '/hello_world.pptx';
    $writer = \PhpOffice\PhpPresentation\IOFactory::createWriter($presentation, 'PowerPoint2007');
    $writer->save($filename);
    return $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $file = null;
        try {
            switch ($_POST['action']) {
                case 'spreadsheet':
                    $file = createSpreadsheet($tempDir);
                    break;
                case 'document':
                    $file = createDocument($tempDir);
                    break;
                case 'presentation':
                    $file = createPresentation($tempDir);
                    break;
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "<br>";
            echo "File: " . $e->getFile() . "<br>";
            echo "Line: " . $e->getLine() . "<br>";
            echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>";
            exit;
        }
        
        if (isset($file) && file_exists($file)) {
            header("Content-Type: application/octet-stream");
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"" . basename($file) . "\"");
            readfile($file);
            unlink($file);
            exit;
        } else {
            echo "Error: File not created or does not exist.";
            exit;
        }
    }
}

// Debug information
if ($debug) {
    echo "Current file: " . __FILE__ . "<br>";
    echo "Current directory: " . getcwd() . "<br>";
    echo "Current user: " . exec('whoami') . "<br>";
    echo "Temporary directory: " . $tempDir . "<br>";
    echo "Temporary directory permissions: " . substr(sprintf('%o', fileperms($tempDir)), -4) . "<br>";
    echo "Parent directory contents:<br>";
    print_r(scandir(dirname(__FILE__) . '/..'));
    echo "<br>Vendor directory contents:<br>";
    print_r(scandir(dirname(__FILE__) . '/../vendor'));
    echo "<br>Temp directory contents:<br>";
    print_r(scandir($tempDir));
    echo "<br>";
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
