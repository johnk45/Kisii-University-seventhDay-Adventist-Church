<?php
session_start();
if(!isset($_SESSION['username'])){
    header('Location: login.php');
    exit;
}

// Read report metadata
$reports = [];
if(file_exists('reports.csv')){
    $fp = fopen('reports.csv', 'r');
    while(($data = fgetcsv($fp)) !== false){
        $reports[] = [
            'title' => $data[0],
            'file' => $data[1],
            'uploader' => $data[2],
            'date' => $data[3]
        ];
    }
    fclose($fp);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Church Reports</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between mb-4">
        <h1>Church Reports</h1>
        <div>
            Welcome, <?= htmlspecialchars($_SESSION['username']) ?> |
            <a href="logout.php" class="btn btn-sm btn-outline-secondary">Logout</a>
            <?php if($_SESSION['role'] === 'admin'): ?>
                <a href="admin.php" class="btn btn-sm btn-primary">Upload Report</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if(empty($reports)): ?>
        <p>No reports available.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach($reports as $report): ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($report['title']) ?></h5>
                            <p class="card-text">
                                Uploaded by: <?= htmlspecialchars($report['uploader']) ?><br>
                                Date: <?= date("F j, Y", strtotime($report['date'])) ?>
                            </p>
                            <a href="uploads/<?= $report['file'] ?>" class="btn btn-primary" target="_blank">View / Download</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
