<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ============== DATABASE CONNECTION ==============
class Database {
    private $conn;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        $this->conn = new mysqli("localhost", "root", "", "payment_system");
        
        if ($this->conn->connect_error) {
            die("Database connection failed: " . $this->conn->connect_error);
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function escape($value) {
        return $this->conn->real_escape_string($value);
    }
    
    public function query($sql) {
        $result = $this->conn->query($sql);
        if (!$result) {
            error_log("Query Error: " . $this->conn->error);
            return false;
        }
        return $result;
    }
    
    public function insertId() {
        return $this->conn->insert_id;
    }
}

// Initialize database
$db = new Database();

// ============== HELPER FUNCTIONS ==============
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

function jsonResponse($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// ============== HANDLE API REQUESTS ==============
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    $action = $_GET['action'] ?? $input['action'] ?? '';
    
    if ($action === 'initiate') {
        // Validate required fields
        $required = ['amount', 'phone_number', 'provider', 'order_reference'];
        $missing = [];
        
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            jsonResponse(false, 'Missing: ' . implode(', ', $missing));
        }
        
        $amount = floatval($input['amount']);
        $phone = formatPhoneNumber($input['phone_number']);
        $provider = $db->escape($input['provider']);
        $orderRef = $db->escape($input['order_reference']);
        
        // Validate
        if ($amount < 1) {
            jsonResponse(false, 'Amount must be at least 1 KES');
        }
        
        if (!in_array($provider, ['mpesa', 'airtel'])) {
            jsonResponse(false, 'Invalid payment provider');
        }
        
        // Generate IDs
        $transactionId = generateTransactionId();
        $merchantId = 'MRQ' . time() . rand(1000, 9999);
        
        // Save to database
        $sql = "INSERT INTO transactions (
            transaction_id, order_reference, provider, amount, 
            phone_number, merchant_request_id, status
        ) VALUES (
            '$transactionId', '$orderRef', '$provider', $amount,
            '$phone', '$merchantId', 'initiated'
        )";
        
        if ($db->query($sql)) {
            jsonResponse(true, 'Payment initiated successfully!', [
                'transaction_id' => $transactionId,
                'demo_message' => '✅ Payment request saved to database. In production, STK Push would be sent to your phone.',
                'details' => [
                    'amount' => $amount,
                    'phone' => $phone,
                    'provider' => $provider,
                    'transaction_id' => $transactionId
                ]
            ]);
        } else {
            jsonResponse(false, 'Failed to save transaction to database');
        }
    }
    
    elseif ($action === 'check_status') {
        $transactionId = $input['transaction_id'] ?? '';
        
        if (empty($transactionId)) {
            jsonResponse(false, 'Transaction ID is required');
        }
        
        $sql = "SELECT * FROM transactions WHERE transaction_id = '" . $db->escape($transactionId) . "'";
        $result = $db->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $transaction = $result->fetch_assoc();
            
            // DEMO: Auto-complete after 10 seconds
            $created = strtotime($transaction['created_at']);
            $now = time();
            
            if ($transaction['status'] === 'initiated' && ($now - $created) > 10) {
                // Update to success
                $updateSql = "UPDATE transactions SET status = 'success', completed_at = NOW() 
                             WHERE transaction_id = '" . $db->escape($transactionId) . "'";
                $db->query($updateSql);
                $transaction['status'] = 'success';
                $transaction['completed_at'] = date('Y-m-d H:i:s');
            }
            
            jsonResponse(true, 'Transaction status retrieved', $transaction);
        } else {
            jsonResponse(false, 'Transaction not found');
        }
    }
    
    else {
        // No action specified, continue to frontend
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment System - Working Version</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
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
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .header p {
            opacity: 0.9;
            font-size: 16px;
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
        input, select {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        input:focus, select:focus {
            border-color: #6a11cb;
            outline: none;
            box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
        }
        .phone-note {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }
        .btn {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
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
            box-shadow: 0 10px 25px rgba(106, 17, 203, 0.4);
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
        .demo-info {
            background: #f8f9fa;
            border-left: 4px solid #6a11cb;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        .demo-info h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .demo-info p {
            margin-bottom: 8px;
            color: #555;
        }
        .loader {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #6a11cb;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .countdown {
            font-weight: bold;
            margin-top: 15px;
            padding: 10px;
            background: rgba(0,0,0,0.05);
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💰 Payment Gateway</h1>
            <p>M-Pesa & Airtel Money Integration</p>
        </div>
        
        <div class="content">
            <div id="paymentForm">
                <div class="form-group">
                    <label for="amount">Amount (KES)</label>
                    <input type="number" id="amount" value="100" min="1" step="1" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" placeholder="07XX XXX XXX" value="254712345678" required>
                    <div class="phone-note">Format: 07XX XXX XXX or 2547XXXXXXXX</div>
                </div>
                
                <div class="form-group">
                    <label for="provider">Payment Method</label>
                    <select id="provider" required>
                        <option value="mpesa">M-Pesa</option>
                        <option value="airtel">Airtel Money</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="orderRef">Order Reference</label>
                    <input type="text" id="orderRef" value="ORDER-<?php echo time(); ?>" readonly>
                </div>
                
                <button class="btn" onclick="initiatePayment()" id="payBtn">
                    💳 Pay Now
                </button>
            </div>
            
            <div id="statusMessage" class="status-box status-info">
                <div id="statusText">Ready to process payment...</div>
                <div id="statusDetails" class="transaction-details" style="display: none;"></div>
                <div id="countdown" class="countdown" style="display: none;"></div>
            </div>
            
            <div class="demo-info">
                <h3>📱 How This Works:</h3>
                <p>1. Click "Pay Now" - Transaction saved to database</p>
                <p>2. Status updates to "initiated"</p>
                <p>3. After 10 seconds, payment auto-completes (demo)</p>
                <p>4. Real API would send STK Push to your phone</p>
                <p style="margin-top: 15px; font-weight: bold; color: #6a11cb;">
                    ✅ Database is now connected!
                </p>
            </div>
        </div>
    </div>

    <script>
        let paymentInterval = null;
        
        async function initiatePayment() {
            const amount = document.getElementById('amount').value;
            const phone = document.getElementById('phone').value;
            const provider = document.getElementById('provider').value;
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
            document.getElementById('statusText').innerHTML = '<span class="loader"></span> Initiating payment...';
            document.getElementById('statusDetails').style.display = 'none';
            
            // Make API call
            try {
                const response = await fetch('index.php?action=initiate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        amount: amount,
                        phone_number: phone,
                        provider: provider,
                        order_reference: orderRef
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Success - show transaction details
                    statusBox.className = 'status-box status-success';
                    document.getElementById('statusText').innerHTML = '✅ Payment Request Sent!';
                    
                    const details = `
                        <strong>Transaction Details:</strong><br>
                        Transaction ID: ${data.data.transaction_id}<br>
                        Amount: KES ${data.details.amount}<br>
                        Phone: ${data.details.phone}<br>
                        Method: ${data.details.provider}<br>
                        Status: INITIATED<br>
                        <small style="color: #666;">${data.data.demo_message}</small>
                    `;
                    
                    document.getElementById('statusDetails').innerHTML = details;
                    document.getElementById('statusDetails').style.display = 'block';
                    
                    // Start polling for status
                    startStatusPolling(data.data.transaction_id);
                    startCountdown();
                } else {
                    // Error
                    statusBox.className = 'status-box status-error';
                    document.getElementById('statusText').innerHTML = `❌ ${data.message}`;
                    btn.disabled = false;
                    btn.innerHTML = '💳 Try Again';
                }
            } catch (error) {
                statusBox.className = 'status-box status-error';
                document.getElementById('statusText').innerHTML = `❌ Network Error: ${error.message}`;
                btn.disabled = false;
                btn.innerHTML = '💳 Try Again';
            }
        }
        
        function startStatusPolling(transactionId) {
            if (paymentInterval) clearInterval(paymentInterval);
            
            paymentInterval = setInterval(async () => {
                try {
                    const response = await fetch('index.php?action=check_status', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            transaction_id: transactionId
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        const status = data.data.status;
                        
                        if (status === 'success') {
                            // Payment completed
                            clearInterval(paymentInterval);
                            document.getElementById('statusText').innerHTML = '🎉 Payment Successful!';
                            document.getElementById('statusDetails').innerHTML += 
                                '<br><strong style="color: green;">✅ PAYMENT COMPLETED!</strong>';
                            document.getElementById('countdown').style.display = 'none';
                            
                            // Enable button for new payment
                            setTimeout(() => {
                                resetForm();
                            }, 3000);
                        }
                        // If still processing, status will update automatically
                    }
                } catch (error) {
                    console.error('Polling error:', error);
                }
            }, 2000); // Check every 2 seconds
        }
        
        function startCountdown() {
            let timeLeft = 10;
            const countdownEl = document.getElementById('countdown');
            countdownEl.style.display = 'block';
            countdownEl.innerHTML = `⏰ Demo: Payment will auto-complete in <span id="time">${timeLeft}</span> seconds`;
            
            const interval = setInterval(() => {
                timeLeft--;
                if (timeLeft >= 0) {
                    document.getElementById('time').textContent = timeLeft;
                } else {
                    clearInterval(interval);
                    countdownEl.innerHTML = '⏳ Finalizing payment...';
                }
            }, 1000);
        }
        
        function resetForm() {
            const btn = document.getElementById('payBtn');
            btn.disabled = false;
            btn.innerHTML = '💳 Make Another Payment';
            document.getElementById('orderRef').value = 'ORDER-' + Date.now();
            
            // Reset form
            document.getElementById('paymentForm').style.opacity = '1';
            document.getElementById('statusMessage').style.display = 'none';
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('orderRef').value = 'ORDER-' + Date.now();
        });
    </script>
</body>
</html>