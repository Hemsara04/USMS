<?php
/**
 * Equipment Model
 * Handles equipment inventory, requests, and checkouts
 */

require_once __DIR__ . '/../config/db.php';

class Equipment {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Get pending equipment requests
     */
    public function getPendingRequests() {
        $sql = "
            SELECT 
                ei.issue_id,
                ei.equipment_id,
                ei.quantity,
                ei.recipient_type,
                e.item_name,
                s.sport_name,
                COALESCE(a.full_name, ei.recipient_name, 'Staff/Coach') AS requested_by,
                COALESCE(a.index_number, 'N/A') AS index_number,
                COALESCE(a.faculty, 'Sports Staff') AS faculty,
                ei.issue_date,
                ei.expected_return_date,
                ei.status
            FROM equipment_issue ei
            JOIN equipment e ON ei.equipment_id = e.equipment_id
            JOIN sports s ON e.sport_id = s.sport_id
            LEFT JOIN Athletic a ON ei.athletic_id = a.athlete_id
            WHERE ei.status = 'Pending'
            ORDER BY ei.issue_date DESC, ei.issue_id DESC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Approve an equipment request (Changes status to Issued and decreases stock by request quantity)
     */
    public function approveRequest($issue_id) {
        $this->db->beginTransaction();
        try {
            // Get equipment id and quantity
            $stmt = $this->db->prepare("SELECT equipment_id, quantity FROM equipment_issue WHERE issue_id = :id AND status = 'Pending'");
            $stmt->execute([':id' => $issue_id]);
            $issue = $stmt->fetch();

            if (!$issue) {
                $this->db->rollBack();
                return false;
            }

            $qty = max(1, (int) $issue['quantity']);

            // Update status to Issued
            $stmtUpdate = $this->db->prepare("UPDATE equipment_issue SET status = 'Issued' WHERE issue_id = :id");
            $stmtUpdate->execute([':id' => $issue_id]);

            // Decrement stock
            $stmtStock = $this->db->prepare("UPDATE equipment SET quantity_in_stock = GREATEST(0, quantity_in_stock - :qty) WHERE equipment_id = :eq_id");
            $stmtStock->execute([
                ':qty'   => $qty,
                ':eq_id' => $issue['equipment_id']
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Reject an equipment request
     */
    public function rejectRequest($issue_id) {
        $stmt = $this->db->prepare("UPDATE equipment_issue SET status = 'Rejected' WHERE issue_id = :id");
        return $stmt->execute([':id' => $issue_id]);
    }

    /**
     * Delete an issue record completely
     */
    public function deleteIssue($issue_id) {
        $stmt = $this->db->prepare("DELETE FROM equipment_issue WHERE issue_id = :id");
        return $stmt->execute([':id' => $issue_id]);
    }

    /**
     * Create a new equipment request (Pending)
     */
    public function createRequest($equipment_id, $quantity, $recipient_type, $athletic_id, $recipient_name, $issue_date = null, $expected_return_date = null, $status = 'Pending') {
        $quantity = max(1, (int) $quantity);
        $issue_date = $issue_date ?: date('Y-m-d');
        $expected_return_date = $expected_return_date ?: date('Y-m-d');

        $stmt = $this->db->prepare("
            INSERT INTO equipment_issue (equipment_id, quantity, athletic_id, recipient_type, recipient_name, issue_date, expected_return_date, status)
            VALUES (:eq_id, :qty, :ath_id, :rec_type, :rec_name, :issue_date, :exp_date, :status)
        ");
        return $stmt->execute([
            ':eq_id'      => $equipment_id,
            ':qty'        => $quantity,
            ':ath_id'     => $athletic_id ?: null,
            ':rec_type'   => $recipient_type,
            ':rec_name'   => $recipient_name,
            ':issue_date' => $issue_date,
            ':exp_date'   => $expected_return_date,
            ':status'     => $status
        ]);
    }

    /**
     * Create a direct check-out (status = Issued) and decrement stock
     */
    public function directCheckout($equipment_id, $quantity, $recipient_type, $athletic_id, $recipient_name, $expected_return_date) {
        $this->db->beginTransaction();
        try {
            $quantity = max(1, (int) $quantity);
            $today = date('Y-m-d');

            // Verify stock availability
            $checkStmt = $this->db->prepare("SELECT quantity_in_stock FROM equipment WHERE equipment_id = :id");
            $checkStmt->execute([':id' => $equipment_id]);
            $stock = (int) $checkStmt->fetchColumn();

            if ($stock < $quantity) {
                $this->db->rollBack();
                return false;
            }

            // Insert issue record
            $stmt = $this->db->prepare("
                INSERT INTO equipment_issue (equipment_id, quantity, athletic_id, recipient_type, recipient_name, issue_date, expected_return_date, status)
                VALUES (:eq_id, :qty, :ath_id, :rec_type, :rec_name, :issue_date, :exp_date, 'Issued')
            ");
            $stmt->execute([
                ':eq_id'      => $equipment_id,
                ':qty'        => $quantity,
                ':ath_id'     => $athletic_id ?: null,
                ':rec_type'   => $recipient_type,
                ':rec_name'   => $recipient_name,
                ':issue_date' => $today,
                ':exp_date'   => $expected_return_date
            ]);

            // Decrement stock
            $stmtStock = $this->db->prepare("UPDATE equipment SET quantity_in_stock = GREATEST(0, quantity_in_stock - :qty) WHERE equipment_id = :id");
            $stmtStock->execute([':qty' => $quantity, ':id' => $equipment_id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Get full inventory list (with optional sport filter)
     */
    public function getInventory($sport_id = null, $condition = null, $search = null) {
        $sql = "
            SELECT 
                e.equipment_id,
                e.item_name,
                e.quantity_in_stock,
                e.condition_status,
                s.sport_id,
                s.sport_name
            FROM equipment e
            JOIN sports s ON e.sport_id = s.sport_id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($sport_id)) {
            $sql .= " AND e.sport_id = :sport_id";
            $params[':sport_id'] = $sport_id;
        }

        if (!empty($condition)) {
            $sql .= " AND e.condition_status = :condition";
            $params[':condition'] = $condition;
        }

        if (!empty($search)) {
            $sql .= " AND (e.item_name LIKE :search OR s.sport_name LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY e.item_name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Add new equipment or restock existing item (Auto-Detection)
     */
    public function upsertEquipment($sport_id, $item_name, $quantity, $condition = 'Good') {
        $trimmed = trim($item_name);
        $quantity = max(1, (int) $quantity);

        // Check if item with exact name & sport already exists
        $checkStmt = $this->db->prepare("
            SELECT equipment_id, quantity_in_stock 
            FROM equipment 
            WHERE sport_id = :sport_id AND LOWER(TRIM(item_name)) = LOWER(:name)
        ");
        $checkStmt->execute([
            ':sport_id' => $sport_id,
            ':name'     => $trimmed
        ]);
        $existing = $checkStmt->fetch();

        if ($existing) {
            // Restock existing item
            $newQty = (int) $existing['quantity_in_stock'] + $quantity;
            $updateStmt = $this->db->prepare("
                UPDATE equipment 
                SET quantity_in_stock = :new_qty, condition_status = :condition 
                WHERE equipment_id = :id
            ");
            $updateStmt->execute([
                ':new_qty'   => $newQty,
                ':condition' => $condition,
                ':id'        => $existing['equipment_id']
            ]);
            return [
                'status'       => 'restocked',
                'equipment_id' => $existing['equipment_id'],
                'item_name'    => $trimmed,
                'added'        => $quantity,
                'new_total'    => $newQty
            ];
        } else {
            // Insert new equipment
            $insertStmt = $this->db->prepare("
                INSERT INTO equipment (sport_id, item_name, quantity_in_stock, condition_status)
                VALUES (:sport_id, :item_name, :quantity, :condition)
            ");
            $insertStmt->execute([
                ':sport_id'  => $sport_id,
                ':item_name' => $trimmed,
                ':quantity'  => $quantity,
                ':condition' => $condition
            ]);
            return [
                'status'       => 'created',
                'equipment_id' => (int) $this->db->lastInsertId(),
                'item_name'    => $trimmed,
                'added'        => $quantity,
                'new_total'    => $quantity
            ];
        }
    }

    /**
     * Update equipment record
     */
    public function updateEquipment($equipment_id, $item_name, $quantity, $condition) {
        $stmt = $this->db->prepare("
            UPDATE equipment 
            SET item_name = :item_name, quantity_in_stock = :quantity, condition_status = :condition
            WHERE equipment_id = :id
        ");
        return $stmt->execute([
            ':id'        => $equipment_id,
            ':item_name' => trim($item_name),
            ':quantity'  => max(0, (int) $quantity),
            ':condition' => $condition
        ]);
    }

    /**
     * Delete equipment
     */
    public function deleteEquipment($equipment_id) {
        $stmt = $this->db->prepare("DELETE FROM equipment WHERE equipment_id = :id");
        return $stmt->execute([':id' => $equipment_id]);
    }

    /**
     * Get active checkouts (status = 'Issued')
     */
    public function getActiveCheckouts($search = null) {
        $sql = "
            SELECT 
                ei.issue_id,
                ei.equipment_id,
                ei.quantity,
                ei.recipient_type,
                e.item_name,
                s.sport_name,
                a.athlete_id,
                COALESCE(a.full_name, ei.recipient_name, 'Staff/Coach') AS issued_to,
                COALESCE(a.index_number, 'Coach/Staff') AS index_number,
                ei.issue_date,
                ei.expected_return_date,
                ei.status
            FROM equipment_issue ei
            JOIN equipment e ON ei.equipment_id = e.equipment_id
            JOIN sports s ON e.sport_id = s.sport_id
            LEFT JOIN Athletic a ON ei.athletic_id = a.athlete_id
            WHERE ei.status = 'Issued'
            ORDER BY ei.issue_date DESC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Mark an item as returned and restore the issued quantity to stock
     */
    public function markReturned($issue_id) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT equipment_id, quantity FROM equipment_issue WHERE issue_id = :id AND status = 'Issued'");
            $stmt->execute([':id' => $issue_id]);
            $issue = $stmt->fetch();

            if (!$issue) {
                $this->db->rollBack();
                return false;
            }

            $qty = max(1, (int) $issue['quantity']);

            // Update status and actual return date
            $stmtUpdate = $this->db->prepare("
                UPDATE equipment_issue 
                SET status = 'Returned', actual_return_date = :ret_date 
                WHERE issue_id = :id
            ");
            $stmtUpdate->execute([
                ':id'       => $issue_id,
                ':ret_date' => date('Y-m-d')
            ]);

            // Increment stock by returned quantity
            $stmtStock = $this->db->prepare("UPDATE equipment SET quantity_in_stock = quantity_in_stock + :qty WHERE equipment_id = :eq_id");
            $stmtStock->execute([
                ':qty'   => $qty,
                ':eq_id' => $issue['equipment_id']
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Get list of coaches for equipment check-out
     */
    public function getCoaches() {
        $stmt = $this->db->query("SELECT user_id, username, email FROM user WHERE role = 'Coach' ORDER BY username ASC");
        return $stmt->fetchAll();
    }

    /**
     * Get equipment summary statistics
     */
    public function getStats() {
        $totalItems   = (int) $this->db->query("SELECT COALESCE(SUM(quantity_in_stock), 0) FROM equipment")->fetchColumn();
        $inStockTypes = (int) $this->db->query("SELECT COUNT(*) FROM equipment WHERE quantity_in_stock > 0")->fetchColumn();
        $lowStock     = (int) $this->db->query("SELECT COUNT(*) FROM equipment WHERE quantity_in_stock <= 5")->fetchColumn();
        $totalPending = (int) $this->db->query("SELECT COUNT(*) FROM equipment_issue WHERE status = 'Pending'")->fetchColumn();
        $totalOut     = (int) $this->db->query("SELECT COALESCE(SUM(quantity), 0) FROM equipment_issue WHERE status = 'Issued'")->fetchColumn();

        return [
            'total_items'    => $totalItems,
            'in_stock_types' => $inStockTypes,
            'low_stock'      => $lowStock,
            'total_pending'  => $totalPending,
            'total_out'      => $totalOut
        ];
    }
}
?>
