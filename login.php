<?php
/**
 * ============================================================================
 * LOGIN PAGE
 * ============================================================================
 * Unified login page that redirects users to their role-specific dashboard.
 * 
 * SHARED FILE - Used by all team members
 * 
 * Features:
 * - Secure login with password_verify()
 * - Session management with session_regenerate_id()
 * - Role-based dashboard redirection
 * - CSRF protection
 * ============================================================================
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/User.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $dashboard = ROLE_DASHBOARDS[getCurrentUserRole()] ?? '/index.php';
    redirect($dashboard);
}

$errors = [];
$email = '';

// Get flash messages
$successMessage = getFlashMessage('success');
$errorMessage = getFlashMessage('error');

// Process login form
if (isPost()) {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid request. Please try again.';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Basic validation
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        }
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        }
        
        // If no validation errors, attempt login
        if (empty($errors)) {
            $userModel = new User();
            $result = $userModel->login($email, $password);
            
            if ($result === false) {
                $errors['general'] = 'Invalid email or password';
            } elseif (isset($result['error']) && $result['error'] === 'account_inactive') {
                if ($result['status'] === 'pending') {
                    $errors['general'] = 'Your account is pending approval. Please wait for admin activation.';
                } else {
                    $errors['general'] = 'Your account has been suspended. Please contact support.';
                }
            } else {
                // Successful login - create session
                $userModel->createSession($result);
                
                // Redirect to role-specific dashboard
                $dashboard = ROLE_DASHBOARDS[$result['role']] ?? '/index.php';
                redirect($dashboard);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-container">
        <!-- Left side - Branding -->
        <div class="auth-branding">
            <div class="branding-content">
                <div class="logo">
                    <span class="logo-icon">🛒</span>
                    <h1><?php echo APP_NAME; ?></h1>
                </div>
                <p class="tagline"><?php echo APP_TAGLINE; ?></p>
                <div class="features">
                    <div class="feature">
                        <span class="feature-icon">🛍️</span>
                        <span>Shop from thousands of products</span>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">💼</span>
                        <span>Sell your products online</span>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">🚚</span>
                        <span>Track your deliveries</span>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">💬</span>
                        <span>Get instant support</span>
                    </div>
                </div>
                
                <!-- Demo Credentials -->
                <div class="demo-credentials">
                    <h4>Demo Accounts</h4>
                    <p><small>Password for all: <code>password123</code></small></p>
                    <div class="demo-list">
                        <span class="demo-item">Admin: admin@shopease.com</span>
                        <span class="demo-item">Customer: john@example.com</span>
                        <span class="demo-item">Seller: tech@seller.com</span>
                        <span class="demo-item">Delivery: delivery1@shopease.com</span>
                        <span class="demo-item">Support: support1@shopease.com</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right side - Login Form -->
        <div class="auth-form-container">
            <div class="auth-form-wrapper">
                <h2>Welcome Back</h2>
                <p class="auth-subtitle">Sign in to your account</p>
                
                <?php if ($successMessage): ?>
                    <div class="alert alert-success">
                        <?php echo escape($successMessage); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($errorMessage): ?>
                    <div class="alert alert-error">
                        <?php echo escape($errorMessage); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-error">
                        <?php echo escape($errors['general']); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="auth-form" id="loginForm">
                    <?php echo csrfField(); ?>
                    
                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" name="email" id="email" 
                               class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo escape($email); ?>" 
                               placeholder="Enter your email" required autofocus>
                        <?php if (isset($errors['email'])): ?>
                            <span class="error-message"><?php echo escape($errors['email']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Password -->
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-input">
                            <input type="password" name="password" id="password" 
                                   class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                   placeholder="Enter your password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <span class="eye-icon">👁️</span>
                            </button>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <span class="error-message"><?php echo escape($errors['password']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Remember Me & Forgot Password -->
                    <div class="form-group form-row-between">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" id="remember">
                            <span class="checkmark"></span>
                            Remember me
                        </label>
                        <a href="#" class="link forgot-link">Forgot Password?</a>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-block">
                        <span class="btn-text">Sign In</span>
                        <span class="btn-loader" style="display: none;"></span>
                    </button>
                </form>
                
                <div class="auth-divider">
                    <span>or</span>
                </div>
                
                <div class="auth-footer">
                    <p>Don't have an account? <a href="<?php echo BASE_URL; ?>/register.php" class="link">Create Account</a></p>
                </div>
                
                <div class="back-home">
                    <a href="<?php echo BASE_URL; ?>/index.php" class="link">
                        ← Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo BASE_URL; ?>/public/js/main.js"></script>
    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            field.type = field.type === 'password' ? 'text' : 'password';
        }
        
        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            btn.querySelector('.btn-text').style.display = 'none';
            btn.querySelector('.btn-loader').style.display = 'inline-block';
            btn.disabled = true;
        });
    </script>
</body>
</html>
