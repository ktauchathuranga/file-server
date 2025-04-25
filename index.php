<?php
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($request_uri) {
    case '/api/signup':
        require_once __DIR__ . '/src/api/signup.php';
        break;
    case '/api/login':
        require_once __DIR__ . '/src/api/login.php';
        break;
    case '/api/upload_file':
        require_once __DIR__ . '/src/api/upload_file.php';
        break;
    case '/api/request_file':
        require_once __DIR__ . '/src/api/request_file.php';
        break;
    case '/api/serve_file':
        require_once __DIR__ . '/src/api/serve_file.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
}