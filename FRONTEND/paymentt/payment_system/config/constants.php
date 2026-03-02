<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'payment_system');

// M-Pesa Configuration
define('MPESA_ENV', 'sandbox'); // sandbox or production
define('MPESA_CONSUMER_KEY', 'YOUR_CONSUMER_KEY');
define('MPESA_CONSUMER_SECRET', 'YOUR_CONSUMER_SECRET');
define('MPESA_SHORTCODE', '174379'); // Sandbox: 174379
define('MPESA_PASSKEY', 'YOUR_PASSKEY');
define('MPESA_CALLBACK_URL', 'https://yourdomain.com/api/mpesa-callback.php');

// Airtel Money Configuration
define('AIRTEL_ENV', 'sandbox');
define('AIRTEL_CLIENT_ID', 'YOUR_CLIENT_ID');
define('AIRTEL_CLIENT_SECRET', 'YOUR_CLIENT_SECRET');
define('AIRTEL_MERCHANT_CODE', 'YOUR_MERCHANT_CODE');
define('AIRTEL_COUNTRY', 'KE');
define('AIRTEL_CALLBACK_URL', 'https://yourdomain.com/api/airtel-callback.php');

// Base URLs
define('MPESA_BASE_URL', MPESA_ENV === 'sandbox' 
    ? 'https://sandbox.safaricom.co.ke' 
    : 'https://api.safaricom.co.ke');

define('AIRTEL_BASE_URL', AIRTEL_ENV === 'sandbox'
    ? 'https://openapiuat.airtel.africa'
    : 'https://openapi.airtel.africa');

// Security
define('SECRET_KEY', 'your-secret-key-here-change-this');
define('JWT_SECRET', 'jwt-secret-key-change-this');

// Enable error reporting for development
if (MPESA_ENV === 'sandbox') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
?>