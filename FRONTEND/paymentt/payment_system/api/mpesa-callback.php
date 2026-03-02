<?php
require_once __DIR__ . '/../config/database.php';

// Log the raw callback
$rawData = file_get_contents('php://input');
$callbackData = json_decode($rawData, true);

// Log to file for debugging
$logFile = __DIR__ . '/../logs/mpesa-callback-' . date('Y-m-d') . '.log';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $rawData . PHP_EOL, FILE_APPEND);

if (isset($callbackData['Body']['stkCallback'])) {
    $stkCallback = $callbackData['Body']['stkCallback'];
    $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? '';
    $resultCode = $stkCallback['ResultCode'] ?? '';
    $resultDesc = $stkCallback['ResultDesc'] ?? '';
    
    if (!empty($checkoutRequestId)) {
        $db = new Database();
        $conn = $db->getConnection();
        
        $status = ($resultCode == 0) ? 'success' : 'failed';
        
        $stmt = $conn->prepare("UPDATE transactions SET 
            status = ?,
            result_code = ?,
            result_description = ?,
            raw_callback_data = ?,
            completed_at = NOW()
            WHERE checkout_request_id = ? 
            AND status IN ('pending', 'initiated')");
        
        $stmt->bind_param("sssss", $status, $resultCode, $resultDesc, $rawData, $checkoutRequestId);
        $stmt->execute();
        
        // Log success
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Updated: $checkoutRequestId -> $status" . PHP_EOL, FILE_APPEND);
    }
}

// Always return success to M-Pesa
header('Content-Type: application/json');
echo json_encode([
    'ResultCode' => 0,
    'ResultDesc' => 'Success'
]);
?>