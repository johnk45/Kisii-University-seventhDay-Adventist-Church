<?php
// Start output buffering to prevent any stray output
ob_start();

// Start session and include config
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// Only proceed if admin
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit();
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

// Set header for JSON response
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Validate required fields
    $required = ['title', 'category', 'author', 'date', 'summary'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Get form data
    $title = sanitize($_POST['title']);
    $category = sanitize($_POST['category']);
    $author = sanitize($_POST['author']);
    $date = sanitize($_POST['date']);
    $summary = sanitize($_POST['summary']);
    $total_amount = isset($_POST['amount']) ? sanitize($_POST['amount']) : null;
    $attendance = isset($_POST['attendance']) ? intval($_POST['attendance']) : null;
    $user_id = $_SESSION['user_id'];
    
    // Handle file upload
    $pdf_filename = null;
    
    if (!isset($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No PDF file uploaded or upload error occurred.');
    }
    
    $file = $_FILES['pdf'];
    
    // Validate file type
    $allowed_types = ['application/pdf'];
    $file_info = finfo_open(FILEINFO_MIME_TYPE);
    $file_type = finfo_file($file_info, $file['tmp_name']);
    finfo_close($file_info);
    
    if (!in_array($file_type, $allowed_types)) {
        throw new Exception('Only PDF files are allowed. File type detected: ' . $file_type);
    }
    
    // Validate file size (max 10MB)
    $max_size = 10 * 1024 * 1024; // 10MB in bytes
    if ($file['size'] > $max_size) {
        throw new Exception('File size must be less than 10MB');
    }
    
    // Validate file extension
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($file_extension !== 'pdf') {
        throw new Exception('File must have .pdf extension');
    }
    
    // Generate unique filename
    $pdf_filename = uniqid() . '_' . preg_replace('/[^A-Za-z0-9\.\-]/', '_', $file['name']);
    $upload_path = UPLOAD_DIR . $pdf_filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception('Failed to save uploaded file.');
    }
    
    // Insert report into database
    $sql = "INSERT INTO reports (title, category, summary, author, report_date, total_amount, attendance, pdf_filename, uploaded_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $title, $category, $summary, $author, $date, 
        $total_amount, $attendance, $pdf_filename, $user_id
    ]);
    
    $report_id = $pdo->lastInsertId();
    
    // Log activity
    $activityStmt = $pdo->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, ?, ?)");
    $activityStmt->execute([$user_id, 'report_upload', "Uploaded report: $title"]);
    
    $response['success'] = true;
    $response['message'] = 'Report uploaded successfully';
    $response['report_id'] = $report_id;
    $response['pdf_url'] = UPLOAD_DIR . $pdf_filename;
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    // If we saved a file but database insert failed, delete the file
    if (isset($upload_path) && file_exists($upload_path)) {
        unlink($upload_path);
    }
}

// Clear any output buffer and send JSON
ob_end_clean();
echo json_encode($response);
exit();
?>