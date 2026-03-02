<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/MpesaService.php';
require_once __DIR__ . '/../includes/AirtelService.php';
require_once __DIR__ . '/../includes/helpers.php';

// Check rate limiting
if (!checkRateLimit('initiate_payment', 5, 60)) {
    jsonResponse(false, 'Too many requests. Please try again later.', [], 429);
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// Validate required fields
validateRequired(['provider', 'amount', 'phone_number', 'order_reference'], $input);

$provider = $db->escape($input['provider']);
$amount = floatval($input['amount']);
$phone = $db->escape(formatPhoneNumber($input['phone_number']));
$orderRef = $db->escape($input['order_reference']);
$transactionId = generateTransactionId();
$merchantRequestId = 'MRQ' . time() . rand(1000, 9999);

// Validate amount
if ($amount < 1) {
    jsonResponse(false, 'Amount must be at least 1 KES');
}

// Validate provider
if (!in_array($provider, ['mpesa', 'airtel'])) {
    jsonResponse(false, 'Invalid payment provider');
}

// Save transaction to database
$sql = "INSERT INTO transactions (
    transaction_id, order_reference, provider, amount, 
    phone_number, merchant_request_id, status
) VALUES (
    '$transactionId', '$orderRef', '$provider', $amount,
    '$phone', '$merchantRequestId', 'pending'
)";

if (!$db->query($sql)) {
    jsonResponse(false, 'Failed to create transaction');
}

// Initiate payment based on provider
if ($provider === 'mpesa') {
    $mpesa = new MpesaService();
    $result = $mpesa->stkPush($phone, $amount, $merchantRequestId);
} else {
    $airtel = new AirtelService();
    $result = $airtel->initiatePayment($phone, $amount, $merchantRequestId);
}

// Handle API response
if (isset($result['success']) && $result['success']) {
    // Update transaction with checkout ID
    $checkoutId = $db->escape($result['checkout_request_id'] ?? $result['transaction_id'] ?? '');
    $updateSql = "UPDATE transactions SET 
        checkout_request_id = '$checkoutId',
        status = 'initiated',
        updated_at = NOW()
        WHERE transaction_id = '$transactionId'";
    
    $db->query($updateSql);
    
    jsonResponse(true, 'Payment request sent to your phone', [
        'transaction_id' => $transactionId,
        'checkout_id' => $checkoutId,
        'message' => 'Please check your phone to complete the payment'
    ]);
} else {
    // Update transaction as failed
    $errorMsg = $db->escape($result['error'] ?? 'Payment initiation failed');
    $updateSql = "UPDATE transactions SET 
        status = 'failed',
        result_description = '$errorMsg',
        completed_at = NOW()
        WHERE transaction_id = '$transactionId'";
    
    $db->query($updateSql);
    
    jsonResponse(false, $errorMsg, ['result' => $result], 400);
}
?>