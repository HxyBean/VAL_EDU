<?php
class Database {
    private static $instance = null;
    private $connection;
    
    // Database configuration for XAMPP
    private $host = '127.0.0.1';
    private $port = 3306;
    private $database = 'ValEduDatabase';
    private $username = 'root';
    private $password = ''; // Empty for default XAMPP
    
    private function __construct() {
        try {
            $this->connection = new mysqli(
                $this->host, 
                $this->username, 
                $this->password, 
                $this->database,
                $this->port
            );
            
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            // Set charset to UTF-8 for Vietnamese characters
            $this->connection->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Legacy method for compatibility with your existing BaseModel
    public static function open() {
        return self::getInstance()->getConnection();
    }
    
    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
?>