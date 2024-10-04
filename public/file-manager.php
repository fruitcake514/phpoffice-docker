<?php
$files = scandir('../mnt');
echo '<h1>File Manager</h1>';
echo '<ul>';
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        echo '<li><a href="upload.php?file=' . urlencode($file) . '">' . htmlspecialchars($file) . '</a></li>';
    }
}
echo '</ul>';
echo '<a href="upload.php">Upload New File</a>';
