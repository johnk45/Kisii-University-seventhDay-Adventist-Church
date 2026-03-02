<?php
require_once __DIR__ . '/../config/credentials.php';

class MpesaService {
    private $accessToken = null;
    private $tokenExpiry = null;
    
    /**
     * Get M-Pesa API access token
     */
    public function getAccessToken() {
        // Check if token is still valid (expires in 1 hour)
        if ($this->accessToken && $this->tokenExpiry > time()) {
            return $this->accessToken;
        }
        
        $url = MPESA_BASE_URL . '/oauth/v1/generate?grant_type=client_credentials';
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET)
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, MPESA_ENV === 'production');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            error_log("M-Pesa Token Error: " . curl_error($ch));
            curl_close($ch);
            return null;
        }
        
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['access_token'])) {
                $this->accessToken = $data['access_token'];
                $this->tokenExpiry = time() + 3500; // 3500 seconds (about 58 minutes)
                
                // Log the token retrieval
                error_log("M-Pesa Token Retrieved: " . $this->accessToken);
                return $this->accessToken;
            }
        }
        
        error_log("M-Pesa Token Failed: HTTP $httpCode - $response");
        return null;
    }
    
    /**
     * Initiate STK Push
     */
    public function stkPush($phone, $amount, $reference, $description = 'Payment') {
        $phone = formatPhoneNumber($phone);
        $timestamp = date('YmdHis');
        $password = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);
        
        $data = [
            'BusinessShortCode' => MPESA_SHORTCODE,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => MPESA_SHORTCODE,
            'PhoneNumber' => $phone,
            'CallBackURL' => MPESA_CALLBACK_URL,
            'AccountReference' => $reference,
            'TransactionDesc' => $description
        ];
        
        $token = $this->getAccessToken();
        if (!$token) {
            return ['error' => 'Failed to get access token'];
        }
        
        $url = MPESA_BASE_URL . '/mpesa/stkpush/v1/processrequest';
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, MPESA_ENV === 'production');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($httpCode === 200 && isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
            return [
                'success' => true,
                'checkout_request_id' => $result['CheckoutRequestID'],
                'merchant_request_id' => $result['MerchantRequestID'],
                'response_description' => $result['ResponseDescription']
            ];
        } else {
            return [
                'success' => false,
                'error' => $result['errorMessage'] ?? $result['ResponseDescription'] ?? 'STK Push failed',
                'response_code' => $result['ResponseCode'] ?? 'N/A'
            ];
        }
    }
}
?>