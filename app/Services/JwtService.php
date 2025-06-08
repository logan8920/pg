<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    protected string $algorithm = 'HS256';

    public function generateToken(array $data, string $key, int $expiryInSeconds = 3600): string
    {
        $issuedAt = time();
        $data['iat'] = $issuedAt;
        $data['exp'] = $issuedAt + $expiryInSeconds;
        return JWT::encode($data, base64_decode($key), $this->algorithm);
    }

    public function decodeToken(string $token, string $key): object
    {
        return JWT::decode(
            $token,
            new Key(
                base64_decode($key),
                $this->algorithm
            )
        );
    }
}
