<?php
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/utils.php';

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function generate_jwt($client_id) {
    $header = base64url_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64url_encode(json_encode([
        'iss' => 'file-serving-server',
        'sub' => $client_id,
        'iat' => time(),
        'exp' => time() + 3600
    ]));
    $secret = $_ENV['JWT_SECRET'];
    $signature = base64url_encode(hash_hmac('sha256', "$header.$payload", $secret, true));
    return "$header.$payload.$signature";
}

function verify_jwt($jwt) {
    if (!$jwt) return false;
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) return false;

    list($header, $payload, $signature) = $parts;
    $secret = $_ENV['JWT_SECRET'];
    $expected_signature = base64url_encode(hash_hmac('sha256', "$header.$payload", $secret, true));

    if ($signature !== $expected_signature) {
        log_message("Invalid JWT signature");
        return false;
    }

    $payload_data = json_decode(base64url_decode($payload), true);
    if (!$payload_data || !isset($payload_data['exp']) || $payload_data['exp'] < time()) {
        log_message("JWT expired or invalid");
        return false;
    }

    return $payload_data['sub'];
}