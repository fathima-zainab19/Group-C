<?php
require_once 'config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];

    $sql = "INSERT INTO users (username, email, password, role, name) 
            VALUES ('$username', '$email', '$password', '$role', '$name')";

    if (mysqli_query($conn, $sql)) {
        header("Location: login.php?msg=Registration successful! Please login.");
        exit();
    } else {
        header("Location: register.php?error=" . urlencode(mysqli_error($conn)));
        exit();
    }
}
?>