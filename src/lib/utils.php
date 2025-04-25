<?php
function log_message($message, $level = 'INFO') {
    $log_file = __DIR__ . '/../../logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    file_put_contents($log_file, "[$timestamp] $level [$client_ip]: $message\n", FILE_APPEND);
}

function log_request($extra = '') {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
    $uri = $_SERVER['REQUEST_URI'] ?? 'UNKNOWN';
    $query = $_SERVER['QUERY_STRING'] ?? '';
    $headers = function_exists('getallheaders') ? getallheaders() : custom_getallheaders();
    $body = file_get_contents('php://input') ?: 'N/A';
    
    $log = "Request: $method $uri\n";
    if ($query) {
        $log .= "Query: $query\n";
    }
    $log .= "Headers: " . json_encode($headers) . "\n";
    $log .= "Body: $body\n";
    if ($extra) {
        $log .= "Extra: $extra\n";
    }
    $log .= "Environment: UPLOAD_DIR=" . ($_ENV['UPLOAD_DIR'] ?? 'not set') . ", SERVER_URL=" . ($_ENV['SERVER_URL'] ?? 'not set') . "\n";
    
    log_message($log, 'REQUEST');
}

function send_json_response($data, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    $response = json_encode($data);
    log_message("Response: $response (Status: $status)", 'RESPONSE');
    echo $response;
    exit;
}

function validate_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if (!function_exists('getallheaders')) {
    function custom_getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
} else {
    function custom_getallheaders() {
        return getallheaders();
    }
}