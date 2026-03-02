<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json');

$transactionId = $_GET['id'] ?? '';

if (empty($transactionId)) {
    jsonResponse(false, 'Transaction ID is required', [], 400);
}

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT * FROM transactions WHERE transaction_id = ?");
$stmt->bind_param("s", $transactionId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $transaction = $result->fetch_assoc();
    jsonResponse(true, 'Transaction found', $transaction);
} else {
    jsonResponse(false, 'Transaction not found', [], 404);
}
?>