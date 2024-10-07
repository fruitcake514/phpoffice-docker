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
$baseDir = __DIR__ . '/data';
$currentDir = isset($_GET['dir']) ? $_GET['dir'] : $baseDir;

if (!is_dir($currentDir)) $currentDir = $baseDir;
// Create PhpOffice files
function createSpreadsheet($dir) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Hello World!');
    
    $writer = new Xlsx($spreadsheet);
    $filename = $dir . '/hello_world_' . time() . '.xlsx';
    $writer->save($filename);
    return $filename;
}

function createDocument($dir) {
    $phpWord = new PhpWord();
    $section = $phpWord->addSection();
    $section->addText('Hello World!');
    
    $filename = $dir . '/hello_world_' . time() . '.docx';
    $phpWord->save($filename, 'Word2007');
    return $filename;
}

function createPresentation($dir) {
    $presentation = new PhpPresentation();
    $slide = $presentation->getActiveSlide();
    $shape = $slide->createRichTextShape();
    $shape->createTextRun('Hello World!');
    
    $filename = $dir . '/hello_world_' . time() . '.pptx';
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
        if ($file) {
            echo "<div class='alert alert-success'>File created successfully: " . basename($file) . "</div>";
        } else {
            echo "<div class='alert alert-danger'>Failed to create file.</div>";
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}
// File and Folder Functions
function listDirectory($dir) {
    $items = scandir($dir);
    echo "<ul class='list-group'>";
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if (is_dir($path)) {
            echo "<li class='list-group-item'><strong>Folder:</strong> <a href='?dir=$path'>$item</a></li>";
        } else {
            echo "<li class='list-group-item'><strong>File:</strong> $item ";
            if (in_array($extension, ['docx', 'xlsx', 'pptx'])) {
                echo "<a href='?edit=$path' class='btn btn-primary btn-sm'>Edit</a> ";
            }
            echo "<a href='?delete=$path' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this file?\");'>Delete</a></li>";
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

if (isset($_GET['edit'])) {
    $editPath = $_GET['edit'];
    $extension = pathinfo($editPath, PATHINFO_EXTENSION);
    
    switch ($extension) {
        case 'docx':
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($editPath);
            $sections = $phpWord->getSections();
            $content = '';
            foreach ($sections as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $content .= $element->getText() . "\n";
                    }
                }
            }
            break;
        
        case 'xlsx':
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($editPath);
            $content = '';
            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                foreach ($worksheet->getRowIterator() as $row) {
                    foreach ($row->getCellIterator() as $cell) {
                        $content .= $cell->getValue() . "\t";
                    }
                    $content .= "\n";
                }
            }
            break;
        
        case 'pptx':
            $presentation = \PhpOffice\PhpPresentation\IOFactory::load($editPath);
            $content = '';
            foreach ($presentation->getAllSlides() as $slide) {
                foreach ($slide->getShapeCollection() as $shape) {
                    if ($shape instanceof \PhpOffice\PhpPresentation\Shape\RichText) {
                        $content .= $shape->getText() . "\n";
                    }
                }
                $content .= "---\n";
            }
            break;
    }
}

// Save edited content
if (isset($_POST['save_file'])) {
    $savePath = $_POST['save_file'];
    $newContent = $_POST['file_content'];
    $extension = pathinfo($savePath, PATHINFO_EXTENSION);
    
    switch ($extension) {
        case 'docx':
            $phpWord = new PhpWord();
            $section = $phpWord->addSection();
            $lines = explode("\n", $newContent);
            foreach ($lines as $line) {
                $section->addText($line);
            }
            $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($savePath);
            break;
        
        case 'xlsx':
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $rows = explode("\n", $newContent);
            foreach ($rows as $rowIndex => $row) {
                $cells = explode("\t", $row);
                foreach ($cells as $columnIndex => $cell) {
                    $sheet->setCellValueByColumnAndRow($columnIndex + 1, $rowIndex + 1, $cell);
                }
            }
            $writer = new Xlsx($spreadsheet);
            $writer->save($savePath);
            break;
        
        case 'pptx':
            $presentation = new PhpPresentation();
            $slides = explode("---\n", $newContent);
            foreach ($slides as $slideContent) {
                $slide = $presentation->createSlide();
                $shape = $slide->createRichTextShape();
                $shape->createTextRun($slideContent);
            }
            $writer = \PhpOffice\PhpPresentation\IOFactory::createWriter($presentation, 'PowerPoint2007');
            $writer->save($savePath);
            break;
    }
    echo "<div class='alert alert-success'>File saved successfully.</div>";
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
        <!-- File Explorer -->
    <h2>Directory: <?php echo $currentDir; ?></h2>
    <?php listDirectory($currentDir); ?>
    
    <!-- File Editor -->
    <?php if (isset($content)): ?>
    <h2>Edit File: <?php echo basename($editPath); ?></h2>
    <form method="POST">
        <div class="form-group">
            <textarea name="file_content" class="form-control" rows="20"><?php echo htmlspecialchars($content); ?></textarea>
        </div>
        <input type="hidden" name="save_file" value="<?php echo $editPath; ?>">
        <button type="submit" class="btn btn-success">Save Changes</button>
    </form>
    <?php endif; ?>
    
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
