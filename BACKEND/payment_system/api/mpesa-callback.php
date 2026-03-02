<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

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
    $resultDesc = $stkCallback['ResultDescription'] ?? '';
    
    if (!empty($checkoutRequestId)) {
        // Update transaction in database
        $status = ($resultCode == 0) ? 'success' : 'failed';
        
        // Escape data
        $checkoutId = $db->escape($checkoutRequestId);
        $resultCode = $db->escape($resultCode);
        $resultDesc = $db->escape($resultDesc);
        $rawData = $db->escape($rawData);
        
        $sql = "UPDATE transactions SET 
            status = '$status',
            result_code = '$resultCode',
            result_description = '$resultDesc',
            raw_callback_data = '$rawData',
            completed_at = NOW(),
            updated_at = NOW()
            WHERE checkout_request_id = '$checkoutId' 
            AND status IN ('pending', 'initiated')";
        
        $db->query($sql);
        
        // TODO: Trigger order fulfillment here
        // You could call another endpoint or run a script
        
        // Log success
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Updated: $checkoutId -> $status" . PHP_EOL, FILE_APPEND);
    }
}

// Always return success to M-Pesa
header('Content-Type: application/json');
echo json_encode([
    'ResultCode' => 0,
    'ResultDesc' => 'Success'
]);
?>