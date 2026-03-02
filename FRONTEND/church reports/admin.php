<?php
session_start();
if(!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin'){
    header('Location: login.php');
    exit;
}

$message = '';
if(isset($_POST['upload'])) {
    $title = $_POST['title'];
    $file = $_FILES['file'];
    $uploadDir = "uploads/";
    $uploadFile = $uploadDir . basename($file['name']);

    $allowed = ['pdf', 'docx'];
    $ext = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));

    if(in_array($ext, $allowed)) {
        if(move_uploaded_file($file['tmp_name'], $uploadFile)) {
            // Save report metadata to CSV
            $data = [
                $title,
                $file['name'],
                $_SESSION['username'],
                date('Y-m-d H:i:s')
            ];
            $fp = fopen('reports.csv', 'a');
            fputcsv($fp, $data);
            fclose($fp);

            $message = "Report uploaded successfully!";
        } else {
            $message = "Failed to upload report.";
        }
    } else {
        $message = "Invalid file type. Only PDF and DOCX allowed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upload Church Reports</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4">Upload Church Reports</h1>
    <?php if($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Report Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Select Report (PDF/DOCX)</label>
            <input type="file" name="file" class="form-control" required>
        </div>
        <button type="submit" name="upload" class="btn btn-primary">Upload</button>
    </form>
    <a href="index.php" class="btn btn-secondary mt-3">Back to Reports</a>
</div>
</body>
</html>
