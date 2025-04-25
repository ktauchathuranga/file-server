<?php
require_once __DIR__ . '/../lib/jwt.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/utils.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$client_name = validate_input($input['client_name'] ?? '');
$client_secret = $input['client_secret'] ?? '';

if (!$client_name || !$client_secret) {
    send_json_response(['error' => 'Missing client_name or client_secret'], 400);
}

$client = get_client_by_credentials($client_name, $client_secret);
if (!$client || !password_verify($client_secret, $client['client_secret'])) {
    send_json_response(['error' => 'Invalid credentials'], 401);
}

$jwt = generate_jwt($client['id']);
send_json_response(['token' => $jwt]);