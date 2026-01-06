<?php
session_start();
require_once 'config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // 1. Search for the user
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        // 2. Verify the password
        if (password_verify($password, $row['password'])) {
            
            // 3. Set Session Variables
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            // 4. Redirect based on role (FIXED)
            $role = $row['role'];
            
            if ($role == 'Admin') {
                header("Location: views/admin/dashboard.php");
            } elseif ($role == 'Customer') {
                header("Location: views/customer/dashboard.php");
            } elseif ($role == 'Seller') {
                header("Location: views/seller/dashboard.php");
            } elseif ($role == 'Delivery Person') {
                header("Location: views/delivery_person/dashboard.php");
            } elseif ($role == 'Payment Manager') {
                header("Location: views/payment_manager/dashboard.php");
            } else {
                // If role is unknown, go to home
                header("Location: index.php");
            }
            exit();
        } else {
            header("Location: login.php?error=Incorrect Password");
            exit();
        }
    } else {
        header("Location: login.php?error=User Not Found");
        exit();
    }
}
?>