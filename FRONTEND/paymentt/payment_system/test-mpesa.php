<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Your M-Pesa credentials
define('MPESA_CONSUMER_KEY', 'P4z1zKYQUErsqsKpxhZ5mbQogNC6YrFeGFkBl3Msf5ynSYzy');
define('MPESA_CONSUMER_SECRET', 'FjSWKheFagnnBtodAU3qka6AW2dMexvGsNEPqZZFU1pKNIL5n2V6LMSBqpCXehmY');
define('MPESA_SHORTCODE', '174379');
define('MPESA_PASSKEY', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919');

// Sandbox URL
$base_url = 'https://sandbox.safaricom.co.ke';

echo "<h1>🔍 Testing M-Pesa Credentials</h1>";

// Test 1: Get Access Token
echo "<h2>1. Testing Access Token</h2>";
$url = $base_url . '/oauth/v1/generate?grant_type=client_credentials';

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Authorization: Basic ' . base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET),
        'Content-Type: application/json'
    ),
));

$response = curl_exec($curl);
$err = curl_error($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($err) {
    echo "<p style='color:red'>❌ CURL Error: " . $err . "</p>";
} else {
    echo "<p>HTTP Code: " . $http_code . "</p>";
    echo "<pre>Response: " . htmlspecialchars($response) . "</pre>";
    
    $data = json_decode($response, true);
    if (isset($data['access_token'])) {
        echo "<p style='color:green'>✅ SUCCESS: Got access token: " . substr($data['access_token'], 0, 20) . "...</p>";
        $access_token = $data['access_token'];
        
        // Test 2: Test STK Push
        echo "<h2>2. Testing STK Push</h2>";
        
        $phone = "254708374149"; // Test phone from Safaricom
        $amount = "1"; // 1 KES
        $timestamp = date('YmdHis');
        $password = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);
        
        $stk_data = array(
            'BusinessShortCode' => MPESA_SHORTCODE,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => MPESA_SHORTCODE,
            'PhoneNumber' => $phone,
            'CallBackURL' => 'https://webhook.site/your-unique-url', // Temporary for testing
            'AccountReference' => 'Test123',
            'TransactionDesc' => 'Test Payment'
        );
        
        $stk_url = $base_url . '/mpesa/stkpush/v1/processrequest';
        
        $curl2 = curl_init();
        curl_setopt_array($curl2, array(
            CURLOPT_URL => $stk_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($stk_data),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $access_token,
                'Content-Type: application/json'
            ),
        ));
        
        $stk_response = curl_exec($curl2);
        $stk_err = curl_error($curl2);
        $stk_http_code = curl_getinfo($curl2, CURLINFO_HTTP_CODE);
        curl_close($curl2);
        
        if ($stk_err) {
            echo "<p style='color:red'>❌ STK Push CURL Error: " . $stk_err . "</p>";
        } else {
            echo "<p>STK Push HTTP Code: " . $stk_http_code . "</p>";
            echo "<pre>STK Response: " . htmlspecialchars($stk_response) . "</pre>";
            
            $stk_data = json_decode($stk_response, true);
            if (isset($stk_data['ResponseCode']) && $stk_data['ResponseCode'] == '0') {
                echo "<p style='color:green'>✅ STK Push SUCCESS! Check your phone for prompt.</p>";
                echo "<p>MerchantRequestID: " . $stk_data['MerchantRequestID'] . "</p>";
                echo "<p>CheckoutRequestID: " . $stk_data['CheckoutRequestID'] . "</p>";
                echo "<p>CustomerMessage: " . $stk_data['CustomerMessage'] . "</p>";
            } else {
                echo "<p style='color:red'>❌ STK Push Failed</p>";
                if (isset($stk_data['errorMessage'])) {
                    echo "<p>Error: " . $stk_data['errorMessage'] . "</p>";
                }
            }
        }
    } else {
        echo "<p style='color:red'>❌ FAILED: Could not get access token</p>";
        if (isset($data['errorMessage'])) {
            echo "<p>Error: " . $data['errorMessage'] . "</p>";
        }
    }
}

echo "<hr>";
echo "<h2>📋 Credentials Summary</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Key</th><th>Value</th><th>Status</th></tr>";
echo "<tr><td>Consumer Key</td><td>" . substr(MPESA_CONSUMER_KEY, 0, 20) . "...</td><td>✅ Loaded</td></tr>";
echo "<tr><td>Consumer Secret</td><td>" . substr(MPESA_CONSUMER_SECRET, 0, 20) . "...</td><td>✅ Loaded</td></tr>";
echo "<tr><td>Shortcode</td><td>" . MPESA_SHORTCODE . "</td><td>✅ Loaded</td></tr>";
echo "<tr><td>Passkey</td><td>" . substr(MPESA_PASSKEY, 0, 20) . "...</td><td>✅ Loaded</td></tr>";
echo "</table>";

echo "<h2>📞 Test Phone Numbers (Sandbox)</h2>";
echo "<ul>";
echo "<li>254708374149 (Primary test number)</li>";
echo "<li>254700000000 (Test number 2)</li>";
echo "<li>254711111111 (Test number 3)</li>";
echo "</ul>";
echo "<p><strong>Note:</strong> Use PIN: <code>174379</code> when prompted on phone</p>";
?>