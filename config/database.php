<?php
// Database Credentials
$host     = "localhost";
$username = "root";     // Default XAMPP/WAMP user
$password = "";         // Default XAMPP/WAMP password is empty
$dbname   = "pinky_petal_db";

// Create Connection
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check Connection
if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

/**
 * Security Requirement: Function to safely encrypt passwords 
 * as required by the project guidelines.
 */
function securePassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}
?>