<?php
//get report api
require_once 'config.php';
requireLogin();

header('Content-Type: application/json');

try {
    // Get all reports
    $sql = "SELECT r.*, u.fullname as uploader_name 
            FROM reports r 
            LEFT JOIN users u ON r.uploaded_by = u.id 
            ORDER BY r.created_at DESC";
    $stmt = $pdo->query($sql);
    $reports = $stmt->fetchAll();
    
    // Format reports for frontend
    $formattedReports = array_map(function($report) {
        return [
            'id' => $report['id'],
            'title' => $report['title'],
            'category' => $report['category'],
            'summary' => $report['summary'],
            'author' => $report['author'],
            'date' => $report['report_date'],
            'views' => $report['views'],
            'downloads' => $report['downloads'],
            'comments' => $report['comments'],
            'totalAmount' => $report['total_amount'],
            'change' => $report['change_percent'],
            'totalAttendance' => $report['attendance'],
            'pdfUrl' => $report['pdf_filename'] ? (UPLOAD_DIR . $report['pdf_filename']) : null,
            'uploadedBy' => $report['uploader_name']
        ];
    }, $reports);
    
    echo json_encode(['success' => true, 'reports' => $formattedReports]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>