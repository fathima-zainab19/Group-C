<?php
/**
 * ADMIN - MANAGE CATEGORIES VIEW
 * MEMBER 1 - ADMIN ROLE
 */

$pageTitle = 'Manage Categories';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../controllers/AdminController.php';

$controller = new AdminController();

// Handle form submission
if (isPost()) {
    $result = $controller->handleCategoryAction();
    if ($result['success']) {
        setFlashMessage('success', $result['message']);
    } else {
        setFlashMessage('error', $result['message']);
    }
    redirect('/views/admin/manage_categories.php');
}

$categories = $controller->getCategories();

// Get category for editing
$editCategory = null;
if (isset($_GET['edit'])) {
    $editCategory = $controller->getCategory((int) $_GET['edit']);
}
?>

<div class="page-header">
    <div class="page-header-actions">
        <div>
            <h1>Manage Categories</h1>
            <p>Create and manage product categories</p>
        </div>
        <button class="btn btn-primary" data-modal="addCategoryModal">➕ Add Category</button>
    </div>
</div>

<!-- Categories Table -->
<div class="table-container">
    <div class="table-header">
        <h3>Categories (
            <?php echo count($categories); ?>)
        </h3>
    </div>

    <?php if (!empty($categories)): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td>#
                            <?php echo $category['id']; ?>
                        </td>
                        <td><strong>
                                <?php echo escape($category['name']); ?>
                            </strong></td>
                        <td>
                            <?php echo escape($category['description'] ?? '-'); ?>
                        </td>
                        <td>
                            <?php echo $category['product_count']; ?>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $category['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($category['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="?edit=<?php echo $category['id']; ?>" class="btn btn-sm btn-secondary">✏️ Edit</a>

                                <?php if ($category['product_count'] == 0): ?>
                                    <form method="POST" style="display: inline;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            data-confirm="Are you sure you want to delete this category?">🗑️ Delete</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">📁</div>
            <h3>No categories yet</h3>
            <p>Create your first category to get started.</p>
            <button class="btn btn-primary" data-modal="addCategoryModal">Add Category</button>
        </div>
    <?php endif; ?>
</div>

<!-- Add Category Modal -->
<div class="modal-overlay" id="addCategoryModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add New Category</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="create">

                <div class="form-group">
                    <label for="name">Category Name <span class="required">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary">Create Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Form -->
<?php if ($editCategory): ?>
    <div class="card mt-3">
        <div class="card-header">
            <h3>Edit Category:
                <?php echo escape($editCategory['name']); ?>
            </h3>
            <a href="<?php echo BASE_URL; ?>/views/admin/manage_categories.php" class="btn btn-sm btn-secondary">Cancel</a>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="category_id" value="<?php echo $editCategory['id']; ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_name">Category Name <span class="required">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control"
                            value="<?php echo escape($editCategory['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_status">Status</label>
                        <select name="status" id="edit_status" class="form-control">
                            <option value="active" <?php echo $editCategory['status'] === 'active' ? 'selected' : ''; ?>
                                >Active</option>
                            <option value="inactive" <?php echo $editCategory['status'] === 'inactive' ? 'selected' : ''; ?>
                                >Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea name="description" id="edit_description" class="form-control"
                        rows="3"><?php echo escape($editCategory['description']); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Update Category</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>