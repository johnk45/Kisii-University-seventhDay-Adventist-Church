<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

// Get transaction ID from URL or parameter
$transactionId = isset($_GET['id']) ? $db->escape($_GET['id']) : '';

if (empty($transactionId)) {
    jsonResponse(false, 'Transaction ID is required', [], 400);
}

// Query database
$sql = "SELECT 
    transaction_id, order_reference, provider, amount, 
    phone_number, status, result_code, result_description,
    created_at, completed_at
    FROM transactions 
    WHERE transaction_id = '$transactionId' 
    OR checkout_request_id = '$transactionId'
    LIMIT 1";

$result = $db->query($sql);

if ($result && $result->num_rows > 0) {
    $transaction = $result->fetch_assoc();
    jsonResponse(true, 'Transaction found', $transaction);
} else {
    jsonResponse(false, 'Transaction not found', [], 404);
}
?>