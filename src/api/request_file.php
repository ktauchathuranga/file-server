<?php
require_once __DIR__ . '/../lib/jwt.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/utils.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(['error' => 'Method not allowed'], 405);
}

$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (!preg_match('/Bearer\s+(\S+)/', $auth_header, $matches)) {
    send_json_response(['error' => 'Missing or invalid token'], 401);
}

$client_id = verify_jwt($matches[1]);
if (!$client_id) {
    send_json_response(['error' => 'Unauthorized'], 401);
}

$input = json_decode(file_get_contents('php://input'), true);
$file_id = validate_input($input['file_id'] ?? '');

if (!$file_id) {
    send_json_response(['error' => 'Missing file_id'], 400);
}

$file = get_file_by_id($file_id);
if (!$file) {
    send_json_response(['error' => 'File not found'], 404);
}

$token = create_one_time_link($file_id);
$url = "http://localhost:8080/api/serve_file?token=$token";
send_json_response(['url' => $url]);