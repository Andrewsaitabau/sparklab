<?php
// mpesa_config.php
class MpesaConfig {
    // Sandbox credentials (replace with live credentials for production)
    const CONSUMER_KEY = 'your_consumer_key';
    const CONSUMER_SECRET = 'your_consumer_secret';
    const BUSINESS_SHORT_CODE = '174379'; // Lipa Na M-Pesa Shortcode
    const PASSKEY = 'your_passkey';
    const TRANSACTION_TYPE = 'CustomerPayBillOnline';
    const CALLBACK_URL = 'https://yourdomain.com/mpesa_callback.php'; // Update with your domain
    
    // API Endpoints
    const SANDBOX_URL = 'https://sandbox.safaricom.co.ke';
    const PRODUCTION_URL = 'https://api.safaricom.co.ke';
    
    public static function getBaseUrl() {
        return self::SANDBOX_URL; // Change to PRODUCTION_URL for live environment
    }
    
    public static function generatePassword() {
        $timestamp = date('YmdHis');
        $password = base64_encode(self::BUSINESS_SHORT_CODE . self::PASSKEY . $timestamp);
        return [
            'password' => $password,
            'timestamp' => $timestamp
        ];
    }
    
    public static function getAccessToken() {
        $url = self::getBaseUrl() . '/oauth/v1/generate?grant_type=client_credentials';
        $credentials = base64_encode(self::CONSUMER_KEY . ':' . self::CONSUMER_SECRET);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        return $result['access_token'] ?? null;
    }
}
?>