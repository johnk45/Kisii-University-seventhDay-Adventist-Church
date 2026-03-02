<?php
// Helper functions

function formatPhoneNumber($phone) {
    $phone = preg_replace('/\D/', '', $phone);
    
    if (strlen($phone) === 9 && $phone[0] === '7') {
        return '254' . $phone;
    } elseif (strlen($phone) === 10 && $phone[0] === '0') {
        return '254' . substr($phone, 1);
    }
    
    return $phone;
}

function generateTransactionId() {
    return 'TXN' . date('YmdHis') . rand(1000, 9999);
}

function jsonResponse($success, $message = '', $data = [], $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json');
    
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data
    ];
    
    echo json_encode($response);
    exit;
}
?>