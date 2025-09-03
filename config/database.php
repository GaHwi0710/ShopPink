<?php
// Configuration for database connection
class DatabaseConfig {
    private static $instance = null;
    private $conn;
    
    // Database configuration
    private $host = 'localhost';
    private $db_name = 'shoppink';
    private $username = 'root';
    private $password = '';
    
    // Private constructor to prevent direct creation of object
    private function __construct() {
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
    }
    
    // Get the single instance of Database
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new DatabaseConfig();
        }
        return self::$instance;
    }
    
    // Get the database connection
    public function getConnection() {
        return $this->conn;
    }
    
    // Prevent cloning of the instance
    private function __clone() {}
    
    // Prevent unserializing of the instance
    public function __wakeup() {}
}
?>