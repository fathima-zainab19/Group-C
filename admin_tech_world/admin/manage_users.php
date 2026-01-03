<?php
/**
 * ADMIN - MANAGE USERS VIEW
 * MEMBER 1 - ADMIN ROLE
 */

$pageTitle = 'Manage Users';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../controllers/AdminController.php';

$controller = new AdminController();

// Handle form submission
if (isPost()) {
    $result = $controller->handleUserAction();
    if ($result['success']) {
        setFlashMessage('success', $result['message']);
    } else {
        setFlashMessage('error', $result['message']);
    }
    redirect('/views/admin/manage_users.php');
}

// Get filters from query string
$filters = [
    'role' => $_GET['role'] ?? '',
    'status' => $_GET['status'] ?? '',
    'search' => $_GET['search'] ?? ''
];
$page = max(1, (int) ($_GET['page'] ?? 1));

$data = $controller->getUsers($page, $filters);
$users = $data['users'];
$totalPages = $data['pages'];
?>

<div class="page-header">
    <div class="page-header-actions">
        <div>
            <h1>Manage Users</h1>
            <p>View and manage all user accounts</p>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 200px;">
                <label for="search">Search</label>
                <input type="text" name="search" id="search" class="form-control"
                    value="<?php echo escape($filters['search']); ?>" placeholder="Search users...">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="role">Role</label>
                <select name="role" id="role" class="form-control">
                    <option value="">All Roles</option>
                    <option value="admin" <?php echo $filters['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="customer" <?php echo $filters['role'] === 'customer' ? 'selected' : ''; ?>>Customer
                    </option>
                    <option value="seller" <?php echo $filters['role'] === 'seller' ? 'selected' : ''; ?>>Seller</option>
                    <option value="delivery" <?php echo $filters['role'] === 'delivery' ? 'selected' : ''; ?>>Delivery
                    </option>
                    <option value="support" <?php echo $filters['role'] === 'support' ? 'selected' : ''; ?>>Support
                    </option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Active
                    </option>
                    <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending
                    </option>
                    <option value="suspended" <?php echo $filters['status'] === 'suspended' ? 'selected' : ''; ?>
                        >Suspended</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="<?php echo BASE_URL; ?>/views/admin/manage_users.php" class="btn btn-secondary">Reset</a>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="table-container">
    <div class="table-header">
        <h3>Users (
            <?php echo $data['total']; ?>)
        </h3>
    </div>

    <?php if (!empty($users)): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>#
                            <?php echo $user['id']; ?>
                        </td>
                        <td>
                            <strong>
                                <?php echo escape($user['full_name'] ?? $user['username']); ?>
                            </strong>
                            <br><small class="text-muted">@
                                <?php echo escape($user['username']); ?>
                            </small>
                        </td>
                        <td>
                            <?php echo escape($user['email']); ?>
                        </td>
                        <td>
                            <span class="badge badge-<?php
                            echo match ($user['role']) {
                                'admin' => 'danger',
                                'seller' => 'success',
                                'customer' => 'primary',
                                'delivery' => 'warning',
                                'support' => 'info',
                                default => 'secondary'
                            };
                            ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?php
                            echo match ($user['status']) {
                                'active' => 'success',
                                'pending' => 'warning',
                                'suspended' => 'danger',
                                default => 'secondary'
                            };
                            ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo formatDate($user['created_at']); ?>
                        </td>
                        <td>
                            <div class="table-actions">
                                <?php if ($user['status'] === 'pending'): ?>
                                    <form method="POST" style="display: inline;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-sm btn-success" title="Approve">✓</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($user['status'] === 'active' && $user['id'] !== getCurrentUserId()): ?>
                                    <form method="POST" style="display: inline;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="action" value="suspend">
                                        <button type="submit" class="btn btn-sm btn-warning" title="Suspend"
                                            data-confirm="Are you sure you want to suspend this user?">⏸</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($user['status'] === 'suspended'): ?>
                                    <form method="POST" style="display: inline;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-sm btn-success" title="Reactivate">▶</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($user['id'] !== getCurrentUserId()): ?>
                                    <form method="POST" style="display: inline;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete"
                                            data-confirm="Are you sure you want to delete this user? This cannot be undone.">🗑️</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="table-footer">
                <span>Page
                    <?php echo $page; ?> of
                    <?php echo $totalPages; ?>
                </span>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query($filters); ?>" class="pagination-btn">←
                            Previous</a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>&<?php echo http_build_query($filters); ?>"
                            class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query($filters); ?>"
                            class="pagination-btn">Next →</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">👥</div>
            <h3>No users found</h3>
            <p>Try adjusting your filters or search criteria.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>