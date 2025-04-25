<?php
function load_env() {
    $env_file = __DIR__ . '/../../.env';
    if (!file_exists($env_file)) {
        die('Environment file (.env) not found');
    }
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}
load_env();