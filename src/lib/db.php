<?php
require_once __DIR__ . '/../config/database.php';

function create_client($client_name, $client_secret) {
    log_message("Creating client: $client_name");
    $db = new Database();
    $conn = $db->getConnection();
    $hashed_secret = password_hash($client_secret, PASSWORD_BCRYPT);
    try {
        $stmt = $conn->prepare("INSERT INTO api_clients (client_name, client_secret) VALUES (?, ?)");
        $stmt->execute([$client_name, $hashed_secret]);
        $client_id = $conn->lastInsertId();
        log_message("Client created, id: $client_id");
        return $client_id;
    } catch (PDOException $e) {
        log_message("Client creation failed: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

function get_client_by_credentials($client_name, $client_secret) {
    log_message("Retrieving client: $client_name");
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->prepare("SELECT id, client_secret FROM api_clients WHERE client_name = ?");
    $stmt->execute([$client_name]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    log_message("Client retrieval result: " . ($client ? "found, id: {$client['id']}" : "not found"));
    return $client;
}

function save_file_metadata($file_name, $file_path, $mime_type, $size, $client_id) {
    log_message("Saving file metadata: name=$file_name, path=$file_path, mime=$mime_type, size=$size, client_id=$client_id");
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->prepare(
        "INSERT INTO files (file_name, file_path, mime_type, size, uploaded_by) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$file_name, $file_path, $mime_type, $size, $client_id]);
    $file_id = $conn->lastInsertId();
    log_message("File metadata saved, file_id: $file_id");
    return $file_id;
}

function get_file_by_id($file_id) {
    log_message("Retrieving file by id: $file_id");
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->prepare("SELECT file_name, file_path, mime_type FROM files WHERE id = ?");
    $stmt->execute([$file_id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);
    log_message("File retrieval result: " . ($file ? "file_name={$file['file_name']}, file_path={$file['file_path']}, mime_type={$file['mime_type']}" : "not found"));
    return $file;
}

function create_one_time_link($file_id, $expires_minutes = 30) {
    log_message("Creating one-time link for file_id: $file_id, expires in $expires_minutes minutes");
    $db = new Database();
    $conn = $db->getConnection();
    $token = bin2hex(random_bytes(20));
    $expires_at = date('Y-m-d H:i:s', strtotime("+$expires_minutes minutes"));
    $stmt = $conn->prepare(
        "INSERT INTO one_time_links (file_id, token, expires_at) VALUES (?, ?, ?)"
    );
    $stmt->execute([$file_id, $token, $expires_at]);
    log_message("One-time link created, token: $token, expires_at: $expires_at");
    return $token;
}

function get_file_by_token($token) {
    log_message("Retrieving file by token: $token");
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->prepare(
        "SELECT f.file_name, f.file_path, f.mime_type
         FROM one_time_links otl
         JOIN files f ON otl.file_id = f.id
         WHERE otl.token = ? AND otl.expires_at > NOW()"
    );
    $stmt->execute([$token]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);
    log_message("File by token result: " . ($file ? "file_name={$file['file_name']}, file_path={$file['file_path']}, mime_type={$file['mime_type']}" : "not found or expired"));
    return $file;
}

function delete_one_time_link($token) {
    log_message("Deleting one-time link: $token");
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->prepare("DELETE FROM one_time_links WHERE token = ?");
    $stmt->execute([$token]);
    log_message("One-time link deleted: $token");
}