<?php

require_once __DIR__ . '/env.php';

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        $this->host = getenv('DB_HOST') ?: '127.0.0.1';
        $this->db_name = getenv('DB_NAME') ?: 'restaurante_pos';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASSWORD') ?: '';
        $this->port = getenv('DB_PORT') ?: '3306';
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $timezone = getenv('APP_TIMEZONE') ?: 'America/Mexico_City';
            date_default_timezone_set($timezone);
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $dbTz = getenv('DB_TIMEZONE') ?: '-06:00';
            $this->conn->exec("SET time_zone = '" . str_replace("'", "", $dbTz) . "'");
        } catch(PDOException $exception) {
            error_log("Error de conexión DB: " . $exception->getMessage());
            $this->conn = null;
        }
        return $this->conn;
    }
}

function testConnection() {
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✅ Conexión exitosa a la base de datos!";
    } else {
        echo "❌ Error de conexión a la base de datos";
    }
}
?>
