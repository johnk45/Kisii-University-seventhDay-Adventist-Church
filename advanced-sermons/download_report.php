<?php
//report download handler
require_once 'config.php';
requireLogin();

if (isset($_GET['id'])) {
    $report_id = intval($_GET['id']);
    
    // Get report details
    $sql = "SELECT * FROM reports WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$report_id]);
    $report = $stmt->fetch();
    
    if (!$report || !$report['pdf_filename']) {
        die('Report not found');
    }
    
    $filepath = UPLOAD_DIR . $report['pdf_filename'];
    
    if (!file_exists($filepath)) {
        die('File not found');
    }
    
    // Increment download count
    $updateSql = "UPDATE reports SET downloads = downloads + 1 WHERE id = ?";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([$report_id]);
    
    // Log activity
    $activityStmt = $pdo->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, ?, ?)");
    $activityStmt->execute([$_SESSION['user_id'], 'download', "Downloaded report: {$report['title']}"]);
    
    // Force download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);
    exit();
}
?>