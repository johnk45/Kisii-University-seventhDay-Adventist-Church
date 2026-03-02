<?php
require_once 'config.php';
requireAdmin();

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $report_id = intval($_GET['id']);
    
    try {
        // Get filename first
        $sql = "SELECT pdf_filename FROM reports WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$report_id]);
        $report = $stmt->fetch();
        
        if ($report) {
            // Delete file if exists
            if ($report['pdf_filename'] && file_exists(UPLOAD_DIR . $report['pdf_filename'])) {
                unlink(UPLOAD_DIR . $report['pdf_filename']);
            }
            
            // Delete from database
            $deleteSql = "DELETE FROM reports WHERE id = ?";
            $deleteStmt = $pdo->prepare($deleteSql);
            $deleteStmt->execute([$report_id]);
            
            // Log activity
            $activityStmt = $pdo->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, ?, ?)");
            $activityStmt->execute([$_SESSION['user_id'], 'delete', "Deleted report ID: $report_id"]);
            
            echo json_encode(['success' => true, 'message' => 'Report deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Report not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No report ID specified']);
}
?>