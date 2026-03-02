<?php
require_once __DIR__ . '/constants.php';

class Database {
    private $connection;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            // Set charset to UTF-8
            $this->connection->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            error_log("Database Error: " . $e->getMessage());
            die("Database connection error. Please try again later.");
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function escape($value) {
        return $this->connection->real_escape_string($value);
    }
    
    public function query($sql) {
        $result = $this->connection->query($sql);
        
        if (!$result) {
            error_log("Query Error: " . $this->connection->error . " | SQL: " . $sql);
            return false;
        }
        
        return $result;
    }
    
    public function insertId() {
        return $this->connection->insert_id;
    }
    
    public function beginTransaction() {
        return $this->connection->begin_transaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
    
    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}

// Global database instance
$db = new Database();
?>