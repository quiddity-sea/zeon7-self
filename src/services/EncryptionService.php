<?php

class EncryptionService {
    private string $cipher = "aes-256-cbc";
    private string $key;

    public function __construct() {
        // Load Encryption Key from ENV or generate a fallback (WARNING: Fallback is reset on session end)
        $this->key = $_ENV['APP_KEY'] ?? '';
        
        if (empty($this->key)) {
            // In a real scenario, we should throw an error if APP_KEY is missing.
            // For now, to prevent crash if not set, we'll log a warning or rely on the user to set it.
            // throw new Exception("APP_KEY is missing in .env");
        }
    }

    public function encrypt(string $data): array {
        if (empty($this->key)) throw new Exception("Encryption key not set.");
        
        $ivlen = openssl_cipher_iv_length($this->cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $encrypted = openssl_encrypt($data, $this->cipher, $this->key, 0, $iv);
        
        return [
            'data' => $encrypted,
            'iv' => base64_encode($iv)
        ];
    }

    public function decrypt(string $encryptedData, string $ivBase64): string {
        if (empty($this->key)) throw new Exception("Encryption key not set.");
        
        $iv = base64_decode($ivBase64);
        return openssl_decrypt($encryptedData, $this->cipher, $this->key, 0, $iv);
    }
}
