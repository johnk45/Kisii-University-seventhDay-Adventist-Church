<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Generate unique transaction ID
 */
function generateTransactionId() {
    return 'TXN' . date('YmdHis') . rand(1000, 9999);
}

/**
 * Format phone number to 2547XXXXXXXX
 */
function formatPhoneNumber($phone) {
    $phone = preg_replace('/\D/', '', $phone);
    
    if (strlen($phone) === 9 && $phone[0] === '7') {
        return '254' . $phone;
    } elseif (strlen($phone) === 10 && $phone[0] === '0') {
        return '254' . substr($phone, 1);
    }
    
    return $phone;
}

/**
 * JSON response helper
 */
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

/**
 * Log API requests
 */
function logApiRequest($endpoint, $request, $response) {
    global $db;
    
    $request = $db->escape(json_encode($request));
    $response = $db->escape(json_encode($response));
    $endpoint = $db->escape($endpoint);
    
    $sql = "INSERT INTO api_logs (endpoint, request, response) 
            VALUES ('$endpoint', '$request', '$response')";
    
    return $db->query($sql);
}

/**
 * Validate required fields
 */
function validateRequired($fields, $data) {
    $missing = [];
    
    foreach ($fields as $field) {
        if (empty($data[$field])) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        jsonResponse(false, 'Missing required fields: ' . implode(', ', $missing), [], 400);
    }
}

/**
 * Get client IP address
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Rate limiting
 */
function checkRateLimit($key, $limit = 10, $window = 60) {
    $ip = getClientIP();
    $redisKey = "rate_limit:$key:$ip";
    
    // In production, use Redis. For MySQL:
    $current = time();
    $windowStart = $current - $window;
    
    // Simple file-based rate limiting for demo
    $filename = __DIR__ . "/../logs/ratelimit_{$key}_{$ip}.txt";
    
    if (file_exists($filename)) {
        $data = json_decode(file_get_contents($filename), true);
        
        if ($data['timestamp'] > $windowStart) {
            if ($data['count'] >= $limit) {
                return false;
            }
            $data['count']++;
        } else {
            $data = ['count' => 1, 'timestamp' => $current];
        }
    } else {
        $data = ['count' => 1, 'timestamp' => $current];
    }
    
    file_put_contents($filename, json_encode($data));
    return true;
}
?>