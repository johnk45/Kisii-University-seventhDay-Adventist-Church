<?php
// M-Pesa Credentials
define('MPESA_CONSUMER_KEY','P4z1zKYQUErsqsKpxhZ5mbQogNC6YrFeGFkBl3Msf5ynSYzy');
define('MPESA_CONSUMER_SECRET', 'FjSWKheFagnnBtodAU3qka6AW2dMexvGsNEPqZZFU1pKNIL5n2V6LMSBqpCXehmY');
define('MPESA_SHORTCODE', '174379'); // Paybill or Till Number
define('MPESA_PASSKEY', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919');
define('MPESA_CALLBACK_URL', 'https://yourdomain.com/api/mpesa-callback.php'); // Update with your domain

// Airtel Money Credentials
define('AIRTEL_CLIENT_ID', 'your_client_id_here');
define('AIRTEL_CLIENT_SECRET', 'your_client_secret_here');
define('AIRTEL_MERCHANT_CODE', 'your_merchant_code_here');
define('AIRTEL_COUNTRY', 'KE'); // Country code, e.g., KE for Kenya
define('AIRTEL_CALLBACK_URL', 'https://yourdomain.com/api/airtel-callback.php'); // Update with your domain

// Environment: sandbox or production
define('MPESA_ENV', 'sandbox'); // Change to 'production' when ready
define('AIRTEL_ENV', 'sandbox'); // Change to 'production' when ready

// Base URLs
if (MPESA_ENV === 'sandbox') {
    define('MPESA_BASE_URL', 'https://sandbox.safaricom.co.ke');
} else {
    define('MPESA_BASE_URL', 'https://api.safaricom.co.ke');
}

if (AIRTEL_ENV === 'sandbox') {
    define('AIRTEL_BASE_URL', 'https://openapiuat.airtel.africa');
} else {
    define('AIRTEL_BASE_URL', 'https://openapi.airtel.africa');
}
?>