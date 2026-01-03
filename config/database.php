<?php
/**
 * ============================================================================
 * DATABASE CONNECTION CLASS
 * ============================================================================
 * This file handles the database connection using PDO (PHP Data Objects).
 * It implements a singleton pattern to ensure only one connection instance.
 * 
 * SHARED FILE - Used by all team members
 * 
 * Usage:
 *   $db = Database::getInstance()->getConnection();
 *   $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
 *   $stmt->execute([$userId]);
 * ============================================================================
 */

class Database {
    // Database configuration - UPDATE THESE VALUES FOR YOUR ENVIRONMENT
    private $host = 'localhost';           // Database host
    private $db_name = 'tech_world_db'; // Database name
    private $username = 'root';            // Database username
    private $password = '';                // Database password (empty for XAMPP default)
    private $charset = 'utf8mb4';          // Character set
    
    // PDO connection instance
    private $conn = null;
    
    // Singleton instance
    private static $instance = null;
    
    /**
     * Private constructor to prevent direct instantiation
     * Establishes database connection with error handling
     */
    private function __construct() {
        try {
            // Build DSN (Data Source Name)
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
            
            // PDO options for security and error handling
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,     // Throw exceptions on errors
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,           // Return associative arrays
                PDO::ATTR_EMULATE_PREPARES   => false,                       // Use real prepared statements
                PDO::ATTR_PERSISTENT         => false,                       // Don't use persistent connections
            ];
            
            // Create PDO instance
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch (PDOException $e) {
            // Log error and display user-friendly message
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please try again later or contact support.");
        }
    }
    
    /**
     * Get singleton instance of Database class
     * @return Database
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get the PDO connection object
     * @return PDO
     */
    public function getConnection(): PDO {
        return $this->conn;
    }
    
    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization of the instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    
    /**
     * Begin a database transaction
     * @return bool
     */
    public function beginTransaction(): bool {
        return $this->conn->beginTransaction();
    }
    
    /**
     * Commit the current transaction
     * @return bool
     */
    public function commit(): bool {
        return $this->conn->commit();
    }
    
    /**
     * Rollback the current transaction
     * @return bool
     */
    public function rollback(): bool {
        return $this->conn->rollBack();
    }
    
    /**
     * Get the last inserted ID
     * @return string
     */
    public function lastInsertId(): string {
        return $this->conn->lastInsertId();
    }
    
    /**
     * Execute a prepared statement with parameters
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind
     * @return PDOStatement
     */
    public function query(string $sql, array $params = []): PDOStatement {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
?>
