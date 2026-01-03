<?php
/**
 * ============================================================================
 * REGISTRATION PAGE
 * ============================================================================
 * Unified registration page for all user roles.
 * Users select their role from a dropdown and register.
 * 
 * SHARED FILE - Used by all team members
 * 
 * Features:
 * - Role selection (Customer, Seller, Delivery Staff, Support Staff)
 * - Form validation (client and server-side)
 * - Password hashing using password_hash()
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
$formData = [
    'username' => '',
    'email' => '',
    'full_name' => '',
    'phone' => '',
    'role' => 'customer'
];

// Process registration form
if (isPost()) {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid request. Please try again.';
    } else {
        // Sanitize input
        $formData = [
            'username' => sanitizeInput($_POST['username'] ?? ''),
            'email' => sanitizeInput($_POST['email'] ?? ''),
            'full_name' => sanitizeInput($_POST['full_name'] ?? ''),
            'phone' => sanitizeInput($_POST['phone'] ?? ''),
            'role' => sanitizeInput($_POST['role'] ?? 'customer'),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'address' => sanitizeInput($_POST['address'] ?? '')
        ];
        
        // Validate using User model
        $userModel = new User();
        $errors = $userModel->validateRegistration($formData);
        
        // If no errors, proceed with registration
        if (empty($errors)) {
            $userId = $userModel->register($formData);
            
            if ($userId) {
                // Set success message based on role
                if ($formData['role'] === 'customer') {
                    setFlashMessage('success', 'Registration successful! You can now login.');
                } else {
                    setFlashMessage('success', 'Registration successful! Your account is pending approval by admin.');
                }
                redirect('/login.php');
            } else {
                $errors['general'] = 'Registration failed. Please try again.';
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
    <title>Register - <?php echo APP_NAME; ?></title>
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
                        <span class="feature-icon">🚀</span>
                        <span>Fast & Secure Shopping</span>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">💳</span>
                        <span>Multiple Payment Options</span>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">📦</span>
                        <span>Quick Delivery</span>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">🎧</span>
                        <span>24/7 Customer Support</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right side - Registration Form -->
        <div class="auth-form-container">
            <div class="auth-form-wrapper">
                <h2>Create Account</h2>
                <p class="auth-subtitle">Join our marketplace today</p>
                
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-error">
                        <?php echo escape($errors['general']); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="auth-form" id="registerForm">
                    <?php echo csrfField(); ?>
                    
                    <!-- Role Selection -->
                    <div class="form-group">
                        <label for="role">I want to join as <span class="required">*</span></label>
                        <select name="role" id="role" class="form-control <?php echo isset($errors['role']) ? 'is-invalid' : ''; ?>" required>
                            <option value="customer" <?php echo $formData['role'] === 'customer' ? 'selected' : ''; ?>>Customer - Shop products</option>
                            <option value="seller" <?php echo $formData['role'] === 'seller' ? 'selected' : ''; ?>>Seller - Sell products</option>
                            <option value="delivery" <?php echo $formData['role'] === 'delivery' ? 'selected' : ''; ?>>Delivery Staff - Deliver orders</option>
                            <option value="support" <?php echo $formData['role'] === 'support' ? 'selected' : ''; ?>>Support Staff - Help customers</option>
                        </select>
                        <?php if (isset($errors['role'])): ?>
                            <span class="error-message"><?php echo escape($errors['role']); ?></span>
                        <?php endif; ?>
                        <small class="form-hint" id="roleHint">Customers can start shopping immediately. Other roles require admin approval.</small>
                    </div>
                    
                    <!-- Full Name -->
                    <div class="form-group">
                        <label for="full_name">Full Name <span class="required">*</span></label>
                        <input type="text" name="full_name" id="full_name" class="form-control" 
                               value="<?php echo escape($formData['full_name']); ?>" 
                               placeholder="Enter your full name" required>
                    </div>
                    
                    <!-- Username -->
                    <div class="form-group">
                        <label for="username">Username <span class="required">*</span></label>
                        <input type="text" name="username" id="username" 
                               class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo escape($formData['username']); ?>" 
                               placeholder="Choose a unique username" required>
                        <?php if (isset($errors['username'])): ?>
                            <span class="error-message"><?php echo escape($errors['username']); ?></span>
                        <?php endif; ?>
                        <small class="form-hint">3-50 characters, letters, numbers, and underscores only</small>
                    </div>
                    
                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" name="email" id="email" 
                               class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo escape($formData['email']); ?>" 
                               placeholder="Enter your email" required>
                        <?php if (isset($errors['email'])): ?>
                            <span class="error-message"><?php echo escape($errors['email']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Phone -->
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" name="phone" id="phone" class="form-control" 
                               value="<?php echo escape($formData['phone']); ?>" 
                               placeholder="Enter your phone number">
                    </div>
                    
                    <!-- Password -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password <span class="required">*</span></label>
                            <div class="password-input">
                                <input type="password" name="password" id="password" 
                                       class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                       placeholder="Create a password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <span class="eye-icon">👁️</span>
                                </button>
                            </div>
                            <?php if (isset($errors['password'])): ?>
                                <span class="error-message"><?php echo escape($errors['password']); ?></span>
                            <?php endif; ?>
                            <small class="form-hint">Minimum 8 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                            <div class="password-input">
                                <input type="password" name="confirm_password" id="confirm_password" 
                                       class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                       placeholder="Confirm your password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <span class="eye-icon">👁️</span>
                                </button>
                            </div>
                            <?php if (isset($errors['confirm_password'])): ?>
                                <span class="error-message"><?php echo escape($errors['confirm_password']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Address (for customers and sellers) -->
                    <div class="form-group" id="addressGroup">
                        <label for="address">Address</label>
                        <textarea name="address" id="address" class="form-control" rows="2" 
                                  placeholder="Enter your address"><?php echo escape($formData['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <!-- Terms Agreement -->
                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="terms" id="terms" required>
                            <span class="checkmark"></span>
                            I agree to the <a href="#" class="link">Terms of Service</a> and <a href="#" class="link">Privacy Policy</a>
                        </label>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-block">
                        <span class="btn-text">Create Account</span>
                        <span class="btn-loader" style="display: none;"></span>
                    </button>
                </form>
                
                <div class="auth-footer">
                    <p>Already have an account? <a href="<?php echo BASE_URL; ?>/login.php" class="link">Sign In</a></p>
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
        
        // Update role hint
        document.getElementById('role').addEventListener('change', function() {
            const hints = {
                'customer': 'Customers can start shopping immediately. Other roles require admin approval.',
                'seller': 'Seller accounts require admin approval. You can list and sell products.',
                'delivery': 'Delivery staff accounts require admin approval. You will deliver orders.',
                'support': 'Support staff accounts require admin approval. You will help customers.'
            };
            document.getElementById('roleHint').textContent = hints[this.value] || hints['customer'];
        });
        
        // Form submission with loading state
        document.getElementById('registerForm').addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            btn.querySelector('.btn-text').style.display = 'none';
            btn.querySelector('.btn-loader').style.display = 'inline-block';
            btn.disabled = true;
        });
    </script>
</body>
</html>
