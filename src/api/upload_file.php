<?php
require_once __DIR__ . '/../lib/jwt.php';
require_once __DIR__ . '/../lib/file.php';
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

if (empty($_FILES['file'])) {
    send_json_response(['error' => 'No file uploaded'], 400);
}

$file_id = upload_file($_FILES['file'], $client_id);
send_json_response(['message' => 'File uploaded', 'file_id' => $file_id], 201);