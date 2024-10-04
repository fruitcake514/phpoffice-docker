<?php
session_start();

$mountPath = '../mnt'; // Adjust as necessary

function listFiles($dir) {
    $files = array_diff(scandir($dir), array('.', '..'));
    return $files;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_folder'])) {
    $newDir = $mountPath . '/' . basename($_POST['folder_name']);
    mkdir($newDir);
}

$currentDir = $mountPath;
$files = listFiles($currentDir);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles.css">
    <title>File Browser</title>
</head>
<body>
    <div class="container">
        <h1>File Browser</h1>
        <form method="POST">
            <input type="text" name="folder_name" placeholder="New Folder Name" required>
            <button type="submit" name="new_folder">Create Folder</button>
        </form>

        <h2>Files and Directories</h2>
        <a href="create.php">Create New File</a>
        <ul>            
            <?php foreach ($files as $file): ?>
                <li>
                    <?php if (is_dir($currentDir . '/' . $file)): ?>
                        <strong><?php echo $file; ?></strong>
                    <?php else: ?>
                        <a href="edit.php?file=<?php echo urlencode($file); ?>"><?php echo $file; ?></a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
      <script src="assets/script.js"></script>
</body>
</html>
