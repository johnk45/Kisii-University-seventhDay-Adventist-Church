<?php
// Turn off error display for callbacks
ini_set('display_errors', 0);
error_reporting(0);

// Log the callback for debugging
$rawData = file_get_contents('php://input');
$logFile = 'logs/mpesa_callback_' . date('Y-m-d') . '.log';

// Create logs directory if it doesn't exist
if (!is_dir('logs')) {
    mkdir('logs', 0777, true);
}

// Log the callback
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Callback received: " . $rawData . PHP_EOL, FILE_APPEND);

// Decode the callback data
$callbackData = json_decode($rawData, true);

// Check if it's a valid M-Pesa callback
if (isset($callbackData['Body']['stkCallback'])) {
    $stkCallback = $callbackData['Body']['stkCallback'];
    $checkoutRequestID = $stkCallback['CheckoutRequestID'] ?? '';
    $resultCode = $stkCallback['ResultCode'] ?? '';
    $resultDesc = $stkCallback['ResultDesc'] ?? '';
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - CheckoutID: $checkoutRequestID, ResultCode: $resultCode" . PHP_EOL, FILE_APPEND);
    
    // Connect to database
    $conn = new mysqli('localhost', 'root', '', 'kusda_payments');
    
    if (!$conn->connect_error) {
        // Determine status
        $status = ($resultCode == 0) ? 'success' : 'failed';
        
        // Get metadata if payment was successful
        $amount = '';
        $mpesaReceipt = '';
        $phoneNumber = '';
        
        if ($resultCode == 0 && isset($stkCallback['CallbackMetadata']['Item'])) {
            foreach ($stkCallback['CallbackMetadata']['Item'] as $item) {
                if ($item['Name'] == 'Amount') {
                    $amount = $item['Value'];
                } elseif ($item['Name'] == 'MpesaReceiptNumber') {
                    $mpesaReceipt = $item['Value'];
                } elseif ($item['Name'] == 'PhoneNumber') {
                    $phoneNumber = $item['Value'];
                }
            }
        }
        
        // Update transaction in database
        $stmt = $conn->prepare("UPDATE donations SET 
            status = ?,
            result_code = ?,
            result_description = ?,
            raw_response = ?,
            completed_at = NOW()
            WHERE checkout_request_id = ?");
        
        $stmt->bind_param("sssss", $status, $resultCode, $resultDesc, $rawData, $checkoutRequestID);
        $stmt->execute();
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Database updated for $checkoutRequestID" . PHP_EOL, FILE_APPEND);
        $stmt->close();
        $conn->close();
    }
}

// Always respond with success to M-Pesa
header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Success']);
?>