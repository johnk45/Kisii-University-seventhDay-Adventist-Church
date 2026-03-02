<?php
require_once __DIR__ . '/../config/database.php';

$rawData = file_get_contents('php://input');
$callbackData = json_decode($rawData, true);

// Log to file
$logFile = __DIR__ . '/../logs/airtel-callback-' . date('Y-m-d') . '.log';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $rawData . PHP_EOL, FILE_APPEND);

if (isset($callbackData['reference'])) {
    $reference = $callbackData['reference'];
    $statusCode = $callbackData['status']['code'] ?? '';
    $statusMessage = $callbackData['status']['message'] ?? '';
    
    $db = new Database();
    $conn = $db->getConnection();
    
    $status = ($statusCode == 200) ? 'success' : 'failed';
    
    $stmt = $conn->prepare("UPDATE transactions SET 
        status = ?,
        result_code = ?,
        result_description = ?,
        raw_callback_data = ?,
        completed_at = NOW()
        WHERE merchant_request_id = ? 
        AND status IN ('pending', 'initiated')");
    
    $stmt->bind_param("sssss", $status, $statusCode, $statusMessage, $rawData, $reference);
    $stmt->execute();
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Updated: $reference -> $status" . PHP_EOL, FILE_APPEND);
}

// Return success to Airtel
header('Content-Type: application/json');
echo json_encode(['status' => 'OK']);
?>