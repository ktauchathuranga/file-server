<?php
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/db.php';

function upload_file($file, $client_id) {
    log_message("Starting file upload for client_id: $client_id, file: " . json_encode($file));
    $upload_dir = $_ENV['UPLOAD_DIR'];
    if (!is_writable($upload_dir)) {
        log_message("Upload directory not writable: $upload_dir", 'ERROR');
        send_json_response(['error' => 'Server configuration error'], 500);
    }

    $file_name = validate_input($file['name']);
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];

    if ($file_error !== UPLOAD_ERR_OK) {
        log_message("File upload error: $file_error", 'ERROR');
        send_json_response(['error' => 'File upload failed'], 400);
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_tmp);
    finfo_close($finfo);

    // Validate file
    $allowed_mimes = ['application/pdf', 'video/mp4', 'image/jpeg', 'image/png'];
    if (!in_array($mime_type, $allowed_mimes)) {
        log_message("Invalid file type: $mime_type", 'ERROR');
        send_json_response(['error' => 'Invalid file type'], 400);
    }

    if ($file_size > 100000000) {
        log_message("File too large: $file_size bytes", 'ERROR');
        send_json_response(['error' => 'File too large'], 400);
    }

    // Generate unique file path
    $ext = pathinfo($file_name, PATHINFO_EXTENSION);
    $unique_name = bin2hex(random_bytes(8)) . '.' . $ext;
    $file_path = $unique_name;
    $full_path = $_ENV['UPLOAD_DIR'] . '/files/' . $file_path;

    // Create files directory if it doesn't exist
    $files_dir = "$upload_dir/files";
    if (!is_dir($files_dir)) {
        if (!mkdir($files_dir, 0750, true)) {
            log_message("Failed to create directory: $files_dir", 'ERROR');
            send_json_response(['error' => 'Server configuration error'], 500);
        }
        log_message("Created directory: $files_dir");
    }

    // Verify directory permissions
    if (!is_writable($files_dir)) {
        log_message("Files directory not writable: $files_dir", 'ERROR');
        send_json_response(['error' => 'Server configuration error'], 500);
    }

    log_message("Moving file to: $full_path");
    if (!move_uploaded_file($file_tmp, $full_path)) {
        log_message("Failed to move uploaded file to: $full_path", 'ERROR');
        send_json_response(['error' => 'File upload failed'], 500);
    }

    // Verify file was written
    if (!file_exists($full_path)) {
        log_message("File not found after move: $full_path", 'ERROR');
        send_json_response(['error' => 'File upload failed'], 500);
    }
    log_message("File successfully written to: $full_path");

    // Save metadata
    log_message("Saving file metadata: name=$file_name, path=$file_path, mime=$mime_type, size=$file_size, client_id=$client_id");
    $file_id = save_file_metadata($file_name, $file_path, $mime_type, $file_size, $client_id);
    if (!$file_id) {
        log_message("Failed to save file metadata for: $file_name", 'ERROR');
        send_json_response(['error' => 'Failed to save file metadata'], 500);
    }

    log_message("File upload successful, file_id: $file_id");
    return $file_id;
}

function serve_file($file_path, $file_name, $mime_type) {
    // Normalize paths to avoid duplicate slashes
    $upload_dir = rtrim($_ENV['UPLOAD_DIR'], '/');
    $file_path = ltrim($file_path, '/');
    $full_path = "$upload_dir/$file_path";
    
    log_message("Serve file details: UPLOAD_DIR={$_ENV['UPLOAD_DIR']}, file_path={$file_path}, full_path={$full_path}");
    
    // Debug file existence
    if (!file_exists($full_path)) {
        log_message("ERROR: File not found at: $full_path", 'ERROR');
        
        // Try to list files in the directory to debug
        $dir = dirname($full_path);
        if (is_dir($dir)) {
            $files = scandir($dir);
            log_message("Files in directory $dir: " . implode(", ", $files));
        } else {
            log_message("Directory does not exist: $dir", 'ERROR');
        }
        
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['error' => 'File not found', 'path' => $full_path]);
        exit;
    }
    
    // Set appropriate headers for file download
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: inline; filename="' . $file_name . '"');
    header('Content-Length: ' . filesize($full_path));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output file content
    log_message("Streaming file content from: $full_path");
    readfile($full_path);
    exit;
}