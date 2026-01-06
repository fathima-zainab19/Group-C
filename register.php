
<!DOCTYPE html>
<html>
<head>
    <title>Login - Pinky Petal</title>
<?php include 'includes/header.php'; ?>
<?php require_once 'config/database.php'; ?>

<div class="auth-container">
    <div class="card">
        <h2>Create Account</h2>
        <form action="" method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            
            <select name="role" required>
                <option value="">Select Your Role</option>
            
                <option value="Customer">Customer</option>
                <option value="Seller">Seller</option>
                <option value="Delivery Person">Delivery Person</option>
                <option value="Payment Manager">Payment Manager</option>
            </select>
            
            <button type="submit" name="register">Sign Up</button>
        </form>

        <?php
        if (isset($_POST['register'])) {
            $name = mysqli_real_escape_string($conn, $_POST['name']);
            $username = mysqli_real_escape_string($conn, $_POST['username']);
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $role = $_POST['role'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // STEP 1: Check if username already exists
            $check_sql = "SELECT * FROM users WHERE username = '$username'";
            $check_result = mysqli_query($conn, $check_sql);

            if (mysqli_num_rows($check_result) > 0) {
                // If found, show error message instead of crashing
                echo "<p style='color:red; margin-top:10px;'>Error: The username '<strong>$username</strong>' is already taken. Please choose another.</p>";
            } else {
                // STEP 2: If not found, save the new user
                $sql = "INSERT INTO users (name, username, email, password, role) VALUES ('$name', '$username', '$email', '$password', '$role')";
                
                try {
                    if (mysqli_query($conn, $sql)) {
                        echo "<p style='color:green; margin-top:10px;'>Success! <a href='login.php'>Login now</a></p>";
                    }
                } catch (mysqli_sql_exception $e) {
                    echo "<p style='color:red;'>Database Error: " . $e->getMessage() . "</p>";
                }
            }
        }
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>