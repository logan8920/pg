<?php

namespace App\Services;

class UserService
{
    public static function encrypt($data)
    {
        $method = env('ENC_TECQ', 'AES-128-CBC'); // e.g., AES-256-CBC
        $key = base64_decode(env('ENC_KEY'));

        $ivLength = openssl_cipher_iv_length($method);
        $iv = random_bytes($ivLength); // Use random_bytes() instead of openssl_random_pseudo_bytes()

        $encrypted = openssl_encrypt(json_encode($data), $method, $key, 0, $iv);
        if ($encrypted === false) {
            throw new \Exception("Encryption failed");
        }

        return base64_encode($iv . $encrypted);
    }

    public static function decrypt($encryptedData)
    {
        $method = env('ENC_TECQ', 'AES-128-CBC');
        $key = base64_decode(env('ENC_KEY'));

        $decoded = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length($method);

        if (strlen($decoded) < $ivLength) {
            throw new \Exception("Invalid encrypted data");
        }

        $iv = substr($decoded, 0, $ivLength);
        $encrypted = substr($decoded, $ivLength);

        $decrypted = openssl_decrypt($encrypted, $method, $key, 0, $iv);
        if ($decrypted === false) {
            throw new \Exception("Decryption failed");
        }

        return $decrypted;
    }

    public static function decryptCustom($ciphertextBase64, $key, $iv, $method = 'AES-256-CBC')
    {
        return openssl_decrypt(
            base64_decode($ciphertextBase64),
            $method,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    public static function encryptCustom(string $plaintext, string $base64Key, string $base64Iv): string
    {
        $key = base64_decode($base64Key);
        $iv = base64_decode($base64Iv);
        $encrypted = openssl_encrypt($plaintext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($encrypted);
    }
}
