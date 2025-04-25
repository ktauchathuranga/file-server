<?php
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/../lib/utils.php';

class Database {
    private $conn;

    public function __construct() {
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $dbname = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];

        try {
            $this->conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            log_message("Database connection failed: " . $e->getMessage());
            http_response_code(500);
            die(json_encode(['error' => 'Internal server error']));
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}