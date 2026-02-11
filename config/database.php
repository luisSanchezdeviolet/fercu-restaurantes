<?php

class Database {
    private $host = "217.21.78.151";
    private $db_name = "restaurante_pos";
    private $username = "restaurantpos";
    private $password = "restaurantpos";
    private $port = "3308";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            date_default_timezone_set('America/Mexico_City');
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->exec("SET time_zone = '-06:00'");
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
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
