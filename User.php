<?php
/**
 * ============================================================================
 * USER MODEL - BASE CLASS
 * ============================================================================
 * This model handles all user-related database operations.
 * It serves as the base model for authentication and user management.
 * 
 * SHARED FILE - Used by all team members
 * 
 * Features:
 * - User registration with password hashing
 * - User authentication with password verification
 * - CRUD operations for user accounts
 * - Role-based user queries
 * ============================================================================
 */

require_once __DIR__ . '/../config/config.php';

class User
{
    // Database connection
    protected $db;

    // Table name
    protected $table = 'users';

    // User properties
    public $id;
    public $username;
    public $email;
    public $password;
    public $role;
    public $status;
    public $phone;
    public $full_name;
    public $address;
    public $created_at;
    public $updated_at;

    /**
     * Constructor - Initialize database connection
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ========================================================================
    // AUTHENTICATION METHODS
    // ========================================================================

    /**
     * Register a new user
     * @param array $data User data (username, email, password, role, etc.)
     * @return int|false User ID on success, false on failure
     */
    public function register(array $data)
    {
        try {
            // Hash password securely using bcrypt
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

            // Determine initial status (customers active, others pending approval)
            $status = ($data['role'] === 'customer') ? 'active' : 'pending';

            $sql = "INSERT INTO {$this->table} 
                    (username, email, password, role, status, phone, full_name, address) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['username'],
                $data['email'],
                $hashedPassword,
                $data['role'],
                $status,
                $data['phone'] ?? null,
                $data['full_name'] ?? null,
                $data['address'] ?? null
            ]);

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Authenticate user login
     * @param string $email User email
     * @param string $password Plain text password
     * @return array|false User data on success, false on failure
     */
    public function login(string $email, string $password)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                return false; // User not found
            }

            // Verify password using password_verify()
            if (!password_verify($password, $user['password'])) {
                return false; // Invalid password
            }

            // Check if account is active
            if ($user['status'] !== 'active') {
                return ['error' => 'account_inactive', 'status' => $user['status']];
            }

            // Remove password from returned data
            unset($user['password']);
            return $user;

        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create user session after successful login
     * @param array $user User data
     */
    public function createSession(array $user): void
    {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
    }

    /**
     * Destroy user session (logout)
     */
    public function destroySession(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    // ========================================================================
    // CRUD OPERATIONS
    // ========================================================================

    /**
     * Find user by ID
     * @param int $id User ID
     * @return array|false User data or false
     */
    public function findById(int $id)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $user = $stmt->fetch();

            if ($user) {
                unset($user['password']); // Don't expose password
            }
            return $user;

        } catch (PDOException $e) {
            error_log("Find User Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find user by email
     * @param string $email User email
     * @return array|false User data or false
     */
    public function findByEmail(string $email)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->fetch();

        } catch (PDOException $e) {
            error_log("Find User By Email Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find user by username
     * @param string $username Username
     * @return array|false User data or false
     */
    public function findByUsername(string $username)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE username = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$username]);
            return $stmt->fetch();

        } catch (PDOException $e) {
            error_log("Find User By Username Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all users with optional filters
     * @param array $filters Optional filters (role, status, search)
     * @param int $limit Limit results
     * @param int $offset Offset for pagination
     * @return array Users list
     */
    public function getAll(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        try {
            $sql = "SELECT id, username, email, role, status, phone, full_name, created_at 
                    FROM {$this->table} WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($filters['role'])) {
                $sql .= " AND role = ?";
                $params[] = $filters['role'];
            }

            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['search'])) {
                $sql .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Get All Users Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get users by role
     * @param string $role User role
     * @return array Users list
     */
    public function getByRole(string $role): array
    {
        return $this->getAll(['role' => $role]);
    }

    /**
     * Update user information
     * @param int $id User ID
     * @param array $data Data to update
     * @return bool Success status
     */
    public function update(int $id, array $data): bool
    {
        try {
            $allowedFields = ['username', 'email', 'phone', 'full_name', 'address', 'status'];
            $updateFields = [];
            $params = [];

            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields)) {
                    $updateFields[] = "{$key} = ?";
                    $params[] = $value;
                }
            }

            if (empty($updateFields)) {
                return false;
            }

            $params[] = $id;
            $sql = "UPDATE {$this->table} SET " . implode(', ', $updateFields) . " WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);

        } catch (PDOException $e) {
            error_log("Update User Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user password
     * @param int $id User ID
     * @param string $newPassword New plain text password
     * @return bool Success status
     */
    public function updatePassword(int $id, string $newPassword): bool
    {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            $sql = "UPDATE {$this->table} SET password = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$hashedPassword, $id]);

        } catch (PDOException $e) {
            error_log("Update Password Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user status
     * @param int $id User ID
     * @param string $status New status
     * @return bool Success status
     */
    public function updateStatus(int $id, string $status): bool
    {
        try {
            $sql = "UPDATE {$this->table} SET status = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$status, $id]);

        } catch (PDOException $e) {
            error_log("Update Status Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete user
     * @param int $id User ID
     * @return bool Success status
     */
    public function delete(int $id): bool
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);

        } catch (PDOException $e) {
            error_log("Delete User Error: " . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // STATISTICS METHODS
    // ========================================================================

    /**
     * Get total users count
     * @param string|null $role Filter by role
     * @param string|null $status Filter by status
     * @return int User count
     */
    public function count(?string $role = null, ?string $status = null): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE 1=1";
            $params = [];

            if ($role) {
                $sql .= " AND role = ?";
                $params[] = $role;
            }

            if ($status) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();

        } catch (PDOException $e) {
            error_log("Count Users Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get user statistics by role
     * @return array Statistics array
     */
    public function getStatsByRole(): array
    {
        try {
            $sql = "SELECT role, COUNT(*) as count FROM {$this->table} GROUP BY role";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        } catch (PDOException $e) {
            error_log("Get Stats Error: " . $e->getMessage());
            return [];
        }
    }

    // ========================================================================
    // VALIDATION METHODS
    // ========================================================================

    /**
     * Check if email exists
     * @param string $email Email to check
     * @param int|null $excludeId User ID to exclude (for updates)
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = ?";
            $params = [$email];

            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn() > 0;

        } catch (PDOException $e) {
            error_log("Email Exists Check Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if username exists
     * @param string $username Username to check
     * @param int|null $excludeId User ID to exclude (for updates)
     * @return bool
     */
    public function usernameExists(string $username, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE username = ?";
            $params = [$username];

            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn() > 0;

        } catch (PDOException $e) {
            error_log("Username Exists Check Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate registration data
     * @param array $data Registration data
     * @return array Validation errors (empty if valid)
     */
    public function validateRegistration(array $data): array
    {
        $errors = [];

        // Username validation
        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        } elseif (strlen($data['username']) < 3 || strlen($data['username']) > 50) {
            $errors['username'] = 'Username must be 3-50 characters';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
            $errors['username'] = 'Username can only contain letters, numbers, and underscores';
        } elseif ($this->usernameExists($data['username'])) {
            $errors['username'] = 'Username already taken';
        }

        // Email validation
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } elseif ($this->emailExists($data['email'])) {
            $errors['email'] = 'Email already registered';
        }

        // Password validation
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        // Confirm password validation
        if (empty($data['confirm_password'])) {
            $errors['confirm_password'] = 'Please confirm your password';
        } elseif ($data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        // Role validation
        $validRoles = ['customer', 'seller', 'delivery', 'support'];
        if (empty($data['role'])) {
            $errors['role'] = 'Please select a role';
        } elseif (!in_array($data['role'], $validRoles)) {
            $errors['role'] = 'Invalid role selected';
        }

        return $errors;
    }
}
?>