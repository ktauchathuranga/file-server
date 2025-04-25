<?php
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/utils.php';
require_once __DIR__ . '/../lib/file.php'; // This contains your serve_file function

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$token = isset($_GET['token']) ? $_GET['token'] : '';
if (empty($token)) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => 'Missing token']);
    exit;
}

log_message("serve_file.php called with token: $token");
log_message("Current working directory: " . getcwd());
log_message("UPLOAD_DIR: " . $_ENV['UPLOAD_DIR']);

// Use your existing get_file_by_token function to get file information
$file = get_file_by_token($token);
if (!$file) {
    log_message("Invalid or expired token: $token", 'ERROR');
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'Invalid or expired token']);
    exit;
}

delete_one_time_link($token);

serve_file($file['file_path'], $file['file_name'], $file['mime_type']);