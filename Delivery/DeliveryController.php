<?php
/**
 * ============================================================================
 * DELIVERY CONTROLLER
 * ============================================================================
 * Controller for Delivery Staff role handling deliveries.
 * 
 * MEMBER 4 - DELIVERY ROLE
 * ============================================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Delivery.php';

class DeliveryController
{
    private $deliveryModel;
    private $deliveryId;

    public function __construct()
    {
        requireRole('delivery');
        $this->deliveryModel = new Delivery();
        $this->deliveryId = getCurrentUserId();
    }

    /**
     * Get dashboard data
     */
    public function dashboard(): array
    {
        return $this->deliveryModel->getDashboardData($this->deliveryId);
    }

    /**
     * Get assigned deliveries
     */
    public function getAssignedDeliveries(): array
    {
        return $this->deliveryModel->getAssignedDeliveries($this->deliveryId);
    }

    /**
     * Get available deliveries
     */
    public function getAvailableDeliveries(): array
    {
        return $this->deliveryModel->getAvailableDeliveries();
    }

    /**
     * Get delivery history
     */
    public function getHistory(): array
    {
        return $this->deliveryModel->getDeliveryHistory($this->deliveryId);
    }

    /**
     * Get statistics
     */
    public function getStats(): array
    {
        return $this->deliveryModel->getStats($this->deliveryId);
    }

    /**
     * Handle delivery actions
     */
    public function handleDeliveryAction(): array
    {
        if (!isPost()) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            return ['success' => false, 'message' => 'Invalid security token'];
        }

        $action = $_POST['action'] ?? '';
        $deliveryId = (int) ($_POST['delivery_id'] ?? 0);

        if (!$deliveryId) {
            return ['success' => false, 'message' => 'Invalid delivery ID'];
        }

        switch ($action) {
            case 'accept':
                $result = $this->deliveryModel->acceptDelivery($deliveryId, $this->deliveryId);
                return [
                    'success' => $result,
                    'message' => $result ? 'Delivery accepted!' : 'Failed to accept delivery (may already be taken)'
                ];

            case 'update_status':
                $status = sanitizeInput($_POST['status'] ?? '');
                $notes = sanitizeInput($_POST['notes'] ?? '');

                $validStatuses = ['picked_up', 'in_transit', 'delivered'];
                if (!in_array($status, $validStatuses)) {
                    return ['success' => false, 'message' => 'Invalid status'];
                }

                $result = $this->deliveryModel->updateDeliveryStatus($deliveryId, $this->deliveryId, $status, $notes);
                return [
                    'success' => $result,
                    'message' => $result ? 'Status updated!' : 'Failed to update status'
                ];

            default:
                return ['success' => false, 'message' => 'Invalid action'];
        }
    }
}
?>