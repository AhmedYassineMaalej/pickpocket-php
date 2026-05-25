<?php

namespace App\Helpers;

use Exception;

class JWT
{
    public static function issue_jwt($username, $user_id)
    {
        $secret = $_ENV['JWT_SECRET'] ?? 'my_key_for_jwt_signing';
        $payload = [
            'user_id' => $user_id,
            'user' => $username,
            'iat' => time(),
            'exp' => time() + 3600,
        ];
        return self::generateJWT($payload, $secret);
    }


    public static function verify_jwt($token)
    {
        $secret = $_ENV['JWT_SECRET'] ?? 'my_key_for_jwt_signing';
        try {
            return self::decode_jwt($token, $secret);
        } catch (Exception $e) {
            return false;
        }
    }


    public static function generateJWT($payload, $secret)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));

        // Create Signature using HMAC SHA256
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }


    public static function isLoggedIn(): bool
    {
        if (isset($_COOKIE['JWT'])) {
            $payload = self::verify_jwt($_COOKIE['JWT']);
            return $payload !== null;
        }
        return false;
    }

    public static function decode_jwt($token, $secret)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null; // must be format : header.payload.signature otherwise reject !!
        }

        [$header64, $payload64, $signature64] = $parts;


        $signature = str_replace(['-', '_'], ['+', '/'], $signature64);
        $expectedSignature = hash_hmac('sha256', $header64 . "." . $payload64, $secret, true);
        $expectedSignature64 = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($expectedSignature));

        if ($signature64 !== $expectedSignature64) {
            error_log("JWT Warning: Signature mismatch!");
            setcookie("JWT", "", time() - 3600, "/"); // Clear the cookie
            return null;
        }

        // 2. Decode the Payload
        $jsonPayload = base64_decode(str_replace(['-', '_'], ['+', '/'], $payload64));
        $payload = json_decode($jsonPayload, true);

        // 3. Check Expiration (exp)
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            error_log("JWT Warning: Token expired.");
            return null;
        }

        return $payload;
    }


    /**
    * Only call this function after checking the User is logged in
    * via `JWT::isLoggedIn()`
    */
    public static function getUserId(): int
    {
        $secret = $_ENV['JWT_SECRET'] ?? 'my_key_for_jwt_signing';
        $payload = self::decode_jwt($_COOKIE['JWT'], $secret);
        return $payload['user_id'];
    }
}
