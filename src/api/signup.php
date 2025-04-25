<?php
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

if (strlen($client_name) < 3 || strlen($client_secret) < 8) {
    send_json_response(['error' => 'client_name must be at least 3 chars, client_secret at least 8'], 400);
}

$client_id = create_client($client_name, $client_secret);
if (!$client_id) {
    send_json_response(['error' => 'Client name already exists or creation failed'], 409);
}

send_json_response(['message' => 'Client created', 'client_id' => $client_id], 201);