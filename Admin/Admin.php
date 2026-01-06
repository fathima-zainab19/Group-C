<?php
class Admin {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // READ: Get all users
    public function viewAllUsers() {
        $query = "SELECT id, name, email, role FROM users";
        return mysqli_query($this->conn, $query);
    }

    // UPDATE: Update user role
    public function updateUserRole($userId, $newRole) {
        $query = "UPDATE users SET role = '$newRole' WHERE id = $userId";
        return mysqli_query($this->conn, $query);
    }

    // DELETE: Remove a user from the system
    public function deleteUser($userId) {
        $query = "DELETE FROM users WHERE id = $userId";
        return mysqli_query($this->conn, $query);
    }
}
?>