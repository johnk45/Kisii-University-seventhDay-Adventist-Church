<?php
require_once __DIR__ . '/../config/credentials.php';

class AirtelService {
    private $accessToken = null;
    private $tokenExpiry = null;
    
    /**
     * Get Airtel API access token
     */
    public function getAccessToken() {
        if ($this->accessToken && $this->tokenExpiry > time()) {
            return $this->accessToken;
        }
        
        $url = AIRTEL_BASE_URL . '/auth/oauth2/token';
        
        $data = [
            'client_id' => AIRTEL_CLIENT_ID,
            'client_secret' => AIRTEL_CLIENT_SECRET,
            'grant_type' => 'client_credentials'
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, AIRTEL_ENV === 'production');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            error_log("Airtel Token Error: " . curl_error($ch));
            curl_close($ch);
            return null;
        }
        
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['access_token'])) {
                $this->accessToken = $data['access_token'];
                $this->tokenExpiry = time() + $data['expires_in'] - 60; // 60 seconds buffer
                
                error_log("Airtel Token Retrieved: " . $this->accessToken);
                return $this->accessToken;
            }
        }
        
        error_log("Airtel Token Failed: HTTP $httpCode - $response");
        return null;
    }
    
    /**
     * Initiate Airtel Money payment
     */
    public function initiatePayment($phone, $amount, $reference) {
        $phone = formatPhoneNumber($phone);
        
        $data = [
            'reference' => $reference,
            'subscriber' => [
                'country' => AIRTEL_COUNTRY,
                'currency' => 'KES',
                'msisdn' => $phone
            ],
            'transaction' => [
                'amount' => $amount,
                'country' => AIRTEL_COUNTRY,
                'currency' => 'KES',
                'id' => $reference
            ],
            'payment' => [
                'merchant_code' => AIRTEL_MERCHANT_CODE,
                'amount' => $amount,
                'country' => AIRTEL_COUNTRY,
                'currency' => 'KES',
                'redirect_url' => AIRTEL_CALLBACK_URL
            ]
        ];
        
        $token = $this->getAccessToken();
        if (!$token) {
            return ['error' => 'Failed to get access token'];
        }
        
        $url = AIRTEL_BASE_URL . '/merchant/v1/payments/';
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'X-Country: ' . AIRTEL_COUNTRY,
            'X-Currency: KES'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, AIRTEL_ENV === 'production');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($httpCode === 200 && isset($result['data']['transaction']['id'])) {
            return [
                'success' => true,
                'transaction_id' => $result['data']['transaction']['id'],
                'status' => $result['status']['code'] ?? 200
            ];
        } else {
            return [
                'success' => false,
                'error' => $result['status']['message'] ?? 'Airtel payment failed',
                'status_code' => $result['status']['code'] ?? $httpCode
            ];
        }
    }
}
?>