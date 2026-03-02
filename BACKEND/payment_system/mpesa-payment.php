<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ==================== M-PESA CONFIGURATION ====================
define('MPESA_CONSUMER_KEY', 'P4z1zKYQUErsqsKpxhZ5mbQogNC6YrFeGFkBl3Msf5ynSYzy');
define('MPESA_CONSUMER_SECRET', 'FjSWKheFagnnBtodAU3qka6AW2dMexvGsNEPqZZFU1pKNIL5n2V6LMSBqpCXehmY');
define('MPESA_SHORTCODE', '174379');
define('MPESA_PASSKEY', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919');
define('MPESA_BASE_URL', 'https://sandbox.safaricom.co.ke');
// Note: For callback, we need a public URL. For testing, you can use ngrok or test without callback.

// ==================== DATABASE SETUP ====================
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'payment_system';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create table if not exists
$sql = "CREATE TABLE IF NOT EXISTS mpesa_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id VARCHAR(50) UNIQUE,
    order_reference VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    checkout_request_id VARCHAR(100),
    merchant_request_id VARCHAR(100),
    result_code VARCHAR(50),
    result_description TEXT,
    raw_response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$conn->query($sql);

// ==================== M-PESA SERVICE CLASS ====================
class MpesaPayment {
    private $access_token = null;
    
    // Get access token
    private function getAccessToken() {
        $url = MPESA_BASE_URL . '/oauth/v1/generate?grant_type=client_credentials';
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic ' . base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET)
            ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        $data = json_decode($response, true);
        if (isset($data['access_token'])) {
            $this->access_token = $data['access_token'];
            return $this->access_token;
        }
        return null;
    }
    
    // Format phone number
    private function formatPhone($phone) {
        $phone = preg_replace('/\D/', '', $phone);
        
        if (substr($phone, 0, 1) == '0') {
            $phone = '254' . substr($phone, 1);
        } elseif (strlen($phone) == 9 && substr($phone, 0, 1) == '7') {
            $phone = '254' . $phone;
        }
        
        return $phone;
    }
    
    // Initiate STK Push
    public function stkPush($phone, $amount, $reference) {
        $phone = $this->formatPhone($phone);
        $timestamp = date('YmdHis');
        $password = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);
        
        $data = array(
            'BusinessShortCode' => MPESA_SHORTCODE,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => MPESA_SHORTCODE,
            'PhoneNumber' => $phone,
            'CallBackURL' => 'https://webhook.site/your-unique-url', // Change this for production
            'AccountReference' => substr($reference, 0, 12),
            'TransactionDesc' => 'Payment for ' . substr($reference, 0, 13)
        );
        
        $token = $this->getAccessToken();
        if (!$token) {
            return array('success' => false, 'error' => 'Failed to get access token');
        }
        
        $url = MPESA_BASE_URL . '/mpesa/stkpush/v1/processrequest';
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json'
            ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        
        // Log the response
        $this->logApiCall('STK Push', $data, $response);
        
        if ($err) {
            return array('success' => false, 'error' => 'CURL Error: ' . $err);
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
            return array(
                'success' => true,
                'checkout_request_id' => $result['CheckoutRequestID'],
                'merchant_request_id' => $result['MerchantRequestID'],
                'customer_message' => $result['CustomerMessage'],
                'raw_response' => $result
            );
        } else {
            return array(
                'success' => false,
                'error' => $result['errorMessage'] ?? $result['ResponseDescription'] ?? 'STK Push failed',
                'raw_response' => $result
            );
        }
    }
    
    // Log API calls
    private function logApiCall($endpoint, $request, $response) {
        $logData = array(
            'timestamp' => date('Y-m-d H:i:s'),
            'endpoint' => $endpoint,
            'request' => $request,
            'response' => $response
        );
        
        // Create logs directory if not exists
        if (!is_dir('logs')) {
            mkdir('logs', 0777, true);
        }
        
        $logFile = 'logs/mpesa_api_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, json_encode($logData) . PHP_EOL, FILE_APPEND);
    }
}

// ==================== HANDLE API REQUESTS ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    
    if ($action === 'initiate') {
        // Validate input
        $amount = floatval($_POST['amount'] ?? 0);
        $phone = $_POST['phone_number'] ?? '';
        $orderRef = $_POST['order_reference'] ?? 'ORDER-' . time();
        
        if ($amount < 1) {
            echo json_encode(array('success' => false, 'message' => 'Amount must be at least 1 KES'));
            exit;
        }
        
        if (empty($phone) || strlen($phone) < 10) {
            echo json_encode(array('success' => false, 'message' => 'Valid phone number required'));
            exit;
        }
        
        // Generate transaction ID
        $transactionId = 'TXN' . date('YmdHis') . rand(1000, 9999);
        
        // Save to database
        $stmt = $conn->prepare("INSERT INTO mpesa_transactions 
            (transaction_id, order_reference, amount, phone_number, status) 
            VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("ssds", $transactionId, $orderRef, $amount, $phone);
        
        if (!$stmt->execute()) {
            echo json_encode(array('success' => false, 'message' => 'Database error'));
            exit;
        }
        
        // Initiate M-Pesa payment
        $mpesa = new MpesaPayment();
        $result = $mpesa->stkPush($phone, $amount, $transactionId);
        
        if ($result['success']) {
            // Update transaction with checkout ID
            $updateStmt = $conn->prepare("UPDATE mpesa_transactions SET 
                checkout_request_id = ?,
                merchant_request_id = ?,
                status = 'initiated',
                raw_response = ?
                WHERE transaction_id = ?");
            $rawResponse = json_encode($result['raw_response']);
            $updateStmt->bind_param("ssss", 
                $result['checkout_request_id'],
                $result['merchant_request_id'],
                $rawResponse,
                $transactionId
            );
            $updateStmt->execute();
            
            echo json_encode(array(
                'success' => true,
                'message' => $result['customer_message'],
                'data' => array(
                    'transaction_id' => $transactionId,
                    'checkout_id' => $result['checkout_request_id'],
                    'amount' => $amount,
                    'phone' => $phone
                )
            ));
        } else {
            // Update as failed
            $updateStmt = $conn->prepare("UPDATE mpesa_transactions SET 
                status = 'failed',
                result_description = ?
                WHERE transaction_id = ?");
            $updateStmt->bind_param("ss", $result['error'], $transactionId);
            $updateStmt->execute();
            
            echo json_encode(array(
                'success' => false,
                'message' => $result['error']
            ));
        }
        exit;
    }
}

// ==================== FRONTEND HTML ====================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-Pesa Payment System</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        input:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 18px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }
        .status-box {
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
            display: none;
        }
        .status-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .status-processing {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .transaction-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .test-info {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        .test-info h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .test-info p {
            margin-bottom: 8px;
            color: #555;
        }
        .loader {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💰 M-Pesa Payment</h1>
            <p>Test with Safaricom Sandbox</p>
        </div>
        
        <div class="content">
            <form id="paymentForm">
                <div class="form-group">
                    <label for="amount">Amount (KES)</label>
                    <input type="number" id="amount" value="1" min="1" step="1" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" placeholder="254708374149" value="254708374149" required>
                    <small style="color: #666;">Use test number: 254708374149</small>
                </div>
                
                <div class="form-group">
                    <label for="orderRef">Order Reference</label>
                    <input type="text" id="orderRef" value="ORDER-<?php echo time(); ?>" readonly>
                </div>
                
                <button type="button" class="btn" onclick="initiatePayment()" id="payBtn">
                    💳 Pay with M-Pesa
                </button>
            </form>
            
            <div id="statusMessage" class="status-box status-info">
                <div id="statusText">Ready to process payment...</div>
                <div id="statusDetails" class="transaction-details" style="display: none;"></div>
            </div>
            
            <div class="test-info">
                <h3>📱 How to Test:</h3>
                <p>1. Amount: Enter 1 KES (minimum)</p>
                <p>2. Phone: Use <strong>254708374149</strong> (test number)</p>
                <p>3. Click "Pay with M-Pesa"</p>
                <p>4. Check the test phone for STK Push</p>
                <p>5. Use PIN: <strong>174379</strong> when prompted</p>
                <p style="margin-top: 15px; color: #667eea; font-weight: bold;">
                    ✅ Using Sandbox Credentials
                </p>
            </div>
        </div>
    </div>

    <script>
        async function initiatePayment() {
            const amount = document.getElementById('amount').value;
            const phone = document.getElementById('phone').value;
            const orderRef = document.getElementById('orderRef').value;
            
            // Validation
            if (!amount || amount < 1) {
                alert('Please enter a valid amount (minimum 1 KES)');
                return;
            }
            
            if (!phone || phone.length < 10) {
                alert('Please enter a valid phone number');
                return;
            }
            
            // Disable button and show processing
            const btn = document.getElementById('payBtn');
            btn.disabled = true;
            btn.innerHTML = '⏳ Processing...';
            
            const statusBox = document.getElementById('statusMessage');
            statusBox.style.display = 'block';
            statusBox.className = 'status-box status-processing';
            document.getElementById('statusText').innerHTML = '<span class="loader"></span> Connecting to M-Pesa...';
            document.getElementById('statusDetails').style.display = 'none';
            
            // Create form data
            const formData = new FormData();
            formData.append('action', 'initiate');
            formData.append('amount', amount);
            formData.append('phone_number', phone);
            formData.append('order_reference', orderRef);
            
            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Success
                    statusBox.className = 'status-box status-success';
                    document.getElementById('statusText').innerHTML = '✅ ' + data.message;
                    
                    const details = `
                        <strong>Transaction Details:</strong><br>
                        Transaction ID: ${data.data.transaction_id}<br>
                        Amount: KES ${data.data.amount}<br>
                        Phone: ${data.data.phone}<br>
                        Checkout ID: ${data.data.checkout_id}<br>
                        <br>
                        <strong>Next Steps:</strong><br>
                        1. Check your phone for M-Pesa prompt<br>
                        2. Enter PIN: <strong>174379</strong><br>
                        3. Wait for confirmation
                    `;
                    
                    document.getElementById('statusDetails').innerHTML = details;
                    document.getElementById('statusDetails').style.display = 'block';
                    
                    // Reset button after 5 seconds
                    setTimeout(() => {
                        btn.disabled = false;
                        btn.innerHTML = '💳 Make Another Payment';
                        document.getElementById('orderRef').value = 'ORDER-' + Date.now();
                    }, 5000);
                } else {
                    // Error
                    statusBox.className = 'status-box status-error';
                    document.getElementById('statusText').innerHTML = '❌ ' + data.message;
                    btn.disabled = false;
                    btn.innerHTML = '💳 Try Again';
                }
            } catch (error) {
                statusBox.className = 'status-box status-error';
                document.getElementById('statusText').innerHTML = '❌ Network Error: ' + error.message;
                btn.disabled = false;
                btn.innerHTML = '💳 Try Again';
            }
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('orderRef').value = 'ORDER-' + Date.now();
        });
    </script>
</body>
</html>