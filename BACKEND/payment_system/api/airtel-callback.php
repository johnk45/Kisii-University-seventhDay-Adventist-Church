<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$rawData = file_get_contents('php://input');
$callbackData = json_decode($rawData, true);

// Log to file
$logFile = __DIR__ . '/../logs/airtel-callback-' . date('Y-m-d') . '.log';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $rawData . PHP_EOL, FILE_APPEND);

if (isset($callbackData['reference'])) {
    $reference = $callbackData['reference'];
    $statusCode = $callbackData['status']['code'] ?? '';
    $statusMessage = $callbackData['status']['message'] ?? '';
    
    // Escape data
    $reference = $db->escape($reference);
    $statusCode = $db->escape($statusCode);
    $statusMessage = $db->escape($statusMessage);
    $rawData = $db->escape($rawData);
    
    $status = ($statusCode == 200) ? 'success' : 'failed';
    
    $sql = "UPDATE transactions SET 
        status = '$status',
        result_code = '$statusCode',
        result_description = '$statusMessage',
        raw_callback_data = '$rawData',
        completed_at = NOW(),
        updated_at = NOW()
        WHERE merchant_request_id = '$reference' 
        AND status IN ('pending', 'initiated')";
    
    $db->query($sql);
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Updated: $reference -> $status" . PHP_EOL, FILE_APPEND);
}

// Return success to Airtel
header('Content-Type: application/json');
echo json_encode(['status' => 'OK']);
?>