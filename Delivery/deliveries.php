<?php
/**
 * DELIVERY STAFF - DELIVERIES VIEW
 * MEMBER 4 - DELIVERY ROLE
 */

$pageTitle = 'Deliveries';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../controllers/DeliveryController.php';

$controller = new DeliveryController();

// Handle form submissions
if (isPost()) {
    $result = $controller->handleDeliveryAction();
    if ($result['success']) {
        setFlashMessage('success', $result['message']);
    } else {
        setFlashMessage('error', $result['message']);
    }
    redirect('/views/delivery/deliveries.php');
}

$assignedDeliveries = $controller->getAssignedDeliveries();
$availableDeliveries = $controller->getAvailableDeliveries();
?>

<div class="page-header">
    <h1>Manage Deliveries</h1>
    <p>Accept new deliveries and update their status</p>
</div>

<!-- Assigned Deliveries -->
<div class="card mb-3">
    <div class="card-header">
        <h3>📦 My Assigned Deliveries (
            <?php echo count($assignedDeliveries); ?>)
        </h3>
    </div>
    <div class="card-body">
        <?php if (!empty($assignedDeliveries)): ?>
            <?php foreach ($assignedDeliveries as $delivery): ?>
                <div
                    style="padding: 1.5rem; border: 2px solid var(--gray-200); border-radius: var(--radius-lg); margin-bottom: 1rem; background: var(--gray-50);">
                    <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 1rem;">
                        <div style="flex: 1; min-width: 300px;">
                            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                                <h3 style="margin: 0;">Order #
                                    <?php echo $delivery['order_id']; ?>
                                </h3>
                                <span class="badge badge-<?php
                                echo match ($delivery['status']) {
                                    'assigned' => 'warning',
                                    'picked_up' => 'info',
                                    'in_transit' => 'primary',
                                    default => 'secondary'
                                };
                                ?>" style="font-size: 0.875rem;">
                                    <?php echo DELIVERY_STATUSES[$delivery['status']] ?? $delivery['status']; ?>
                                </span>
                            </div>

                            <p style="margin: 0 0 0.5rem 0;"><strong>👤 Customer:</strong>
                                <?php echo escape($delivery['customer_name']); ?>
                            </p>
                            <p style="margin: 0 0 0.5rem 0;"><strong>📍 Address:</strong>
                                <?php echo escape($delivery['delivery_address'] ?: $delivery['shipping_address']); ?>
                            </p>
                            <p style="margin: 0 0 0.5rem 0;"><strong>📞 Phone:</strong>
                                <?php echo escape($delivery['contact_phone'] ?: $delivery['shipping_phone']); ?>
                            </p>
                            <p style="margin: 0 0 0.5rem 0;"><strong>💰 Amount:</strong>
                                <?php echo formatPrice($delivery['total_amount']); ?>
                                <span
                                    class="badge badge-<?php echo $delivery['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                                    <?php echo $delivery['payment_status'] === 'paid' ? 'Paid' : 'COD'; ?>
                                </span>
                            </p>
                        </div>

                        <div style="min-width: 250px;">
                            <form method="POST" action="">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="delivery_id" value="<?php echo $delivery['id']; ?>">

                                <div class="form-group">
                                    <label for="status_<?php echo $delivery['id']; ?>">Update Status</label>
                                    <select name="status" id="status_<?php echo $delivery['id']; ?>" class="form-control">
                                        <option value="">Select Status</option>
                                        <?php if ($delivery['status'] === 'assigned'): ?>
                                            <option value="picked_up">📦 Picked Up</option>
                                        <?php endif; ?>
                                        <?php if ($delivery['status'] === 'assigned' || $delivery['status'] === 'picked_up'): ?>
                                            <option value="in_transit">🚚 In Transit</option>
                                        <?php endif; ?>
                                        <option value="delivered">✅ Delivered</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="notes_<?php echo $delivery['id']; ?>">Notes (Optional)</label>
                                    <input type="text" name="notes" id="notes_<?php echo $delivery['id']; ?>"
                                        class="form-control" placeholder="Any delivery notes...">
                                </div>

                                <button type="submit" class="btn btn-primary btn-block">Update Status</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <p>No assigned deliveries. Accept new deliveries below!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Available Deliveries -->
<div class="card">
    <div class="card-header">
        <h3>🆕 Available for Pickup (
            <?php echo count($availableDeliveries); ?>)
        </h3>
    </div>
    <div class="card-body">
        <?php if (!empty($availableDeliveries)): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Address</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($availableDeliveries as $delivery): ?>
                        <tr>
                            <td><strong>#
                                    <?php echo $delivery['order_id']; ?>
                                </strong></td>
                            <td>
                                <?php echo escape($delivery['customer_name']); ?>
                            </td>
                            <td style="max-width: 250px;">
                                <?php echo escape($delivery['shipping_address']); ?>
                            </td>
                            <td>
                                <?php echo formatPrice($delivery['total_amount']); ?>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="action" value="accept">
                                    <input type="hidden" name="delivery_id" value="<?php echo $delivery['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-success">✓ Accept</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>No deliveries available</h3>
                <p>Check back later for new deliveries.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>