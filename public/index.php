<?php
// Debug mode flag
$debug = true;
ini_set('memory_limit', '256M');
ob_clean();
flush();
ob_start();
// If debug mode is on, display errors
if ($debug) {
    error_reporting(E_ALL & ~E_DEPRECATED);  // Ignore deprecated warnings
    ini_set('display_errors', 1);
}

require dirname(__FILE__) . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpPresentation\PhpPresentation;

// Define base directory
$baseDir = __DIR__ . '/storage';
$currentDir = isset($_GET['dir']) ? $_GET['dir'] : $baseDir;
if (!is_dir($currentDir)) $currentDir = $baseDir;

// File and Folder Functions
function listDirectory($dir) {
    $items = scandir($dir);
    echo "<ul class='list-group'>";
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            echo "<li class='list-group-item'><strong>Folder:</strong> <a href='?dir=$path'>$item</a></li>";
        } else {
            echo "<li class='list-group-item'><strong>File:</strong> $item 
                  <a href='?delete=$path' class='btn btn-danger btn-sm'>Delete</a></li>";
        }
    }
    echo "</ul>";
}

if (isset($_POST['new_folder'])) {
    $newFolderPath = $currentDir . '/' . $_POST['folder_name'];
    if (!file_exists($newFolderPath)) {
        mkdir($newFolderPath, 0777, true);
    }
}

if (isset($_GET['delete'])) {
    $deletePath = $_GET['delete'];
    if (is_file($deletePath)) {
        unlink($deletePath);
    } elseif (is_dir($deletePath)) {
        rmdir($deletePath);
    }
}

// File upload handling
if (isset($_FILES['file'])) {
    $targetFile = $currentDir . '/' . basename($_FILES['file']['name']);
    move_uploaded_file($_FILES['file']['tmp_name'], $targetFile);
}

// Create PhpOffice files
function createSpreadsheet($dir) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Hello World!');
    
    $writer = new Xlsx($spreadsheet);
    $filename = $dir . '/hello_world.xlsx';
    $writer->save($filename);
    return $filename;
}

function createDocument($dir) {
    $phpWord = new PhpWord();
    $section = $phpWord->addSection();
    $section->addText('Hello World!');
    
    $filename = $dir . '/hello_world.docx';
    $phpWord->save($filename, 'Word2007');
    return $filename;
}

function createPresentation($dir) {
    $presentation = new PhpPresentation();
    $slide = $presentation->getActiveSlide();
    $shape = $slide->createRichTextShape();
    $shape->createTextRun('Hello World!');
    
    $filename = $dir . '/hello_world.pptx';
    $writer = \PhpOffice\PhpPresentation\IOFactory::createWriter($presentation, 'PowerPoint2007');
    $writer->save($filename);
    return $filename;
}

// Create files based on user action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $file = null;
    try {
        switch ($_POST['action']) {
            case 'spreadsheet':
                $file = createSpreadsheet($currentDir);
                break;
            case 'document':
                $file = createDocument($currentDir);
                break;
            case 'presentation':
                $file = createPresentation($currentDir);
                break;
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Debug information
if ($debug) {
    echo "Current directory: " . $currentDir . "<br>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <title>PHPOffice Suite</title>
</head>
<body class="container">
    <h1>PHPOffice Suite - File Explorer</h1>
    
    <!-- File Explorer -->
    <h2>Directory: <?php echo $currentDir; ?></h2>
    <?php listDirectory($currentDir); ?>
    
    <!-- Create Folder -->
    <form method="POST">
        <div class="form-group">
            <label for="folder_name">Create New Folder:</label>
            <input type="text" name="folder_name" class="form-control" required>
        </div>
        <button type="submit" name="new_folder" class="btn btn-primary">Create Folder</button>
    </form>

    <!-- Create Files with PhpOffice -->
    <h2>Create New File</h2>
    <form method="POST">
        <div class="btn-group" role="group">
            <button type="submit" name="action" value="spreadsheet" class="btn btn-success">Create Spreadsheet</button>
            <button type="submit" name="action" value="document" class="btn btn-primary">Create Document</button>
            <button type="submit" name="action" value="presentation" class="btn btn-info">Create Presentation</button>
        </div>
    </form>

    <!-- File Upload -->
    <h2>Upload File</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="file">Select file to upload:</label>
            <input type="file" name="file" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-warning">Upload File</button>
    </form>
</body>
</html>
