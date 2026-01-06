<?php
require_once('../config/database.php');

class AdminController {
    // READ: View all users in the system
    public function getAllUsers($conn) {
        $sql = "SELECT id, username, email, role FROM users";
        return mysqli_query($conn, $sql);
    }

    // DELETE: Remove a user (Academic Integrity/Management)
    public function deleteUser($conn, $userId) {
        $sql = "DELETE FROM users WHERE id = $userId";
        return mysqli_query($conn, $sql);
    }
}
?>