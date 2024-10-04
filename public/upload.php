<?php
$file = $_GET['file'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
        $destination = '../mnt/' . basename($_FILES['file']['name']);
        move_uploaded_file($_FILES['file']['tmp_name'], $destination);
        echo 'File uploaded successfully!';
    }
}

?>

<h1><?php echo $file ? "Edit $file" : 'Upload New File'; ?></h1>
<form action="" method="post" enctype="multipart/form-data">
    <input type="file" name="file" required>
    <button type="submit">Upload</button>
</form>

<?php if ($file): ?>
    <h2>Contents of <?php echo htmlspecialchars($file); ?></h2>
    <textarea><?php echo file_get_contents('../mnt/' . $file); ?></textarea>
<?php endif; ?>
