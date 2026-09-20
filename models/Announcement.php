<?php
/**
 * Announcement Model
 * Handles database operations for the Student President portal
 * and universal notification broadcasting across all USMS user roles.
 */

require_once __DIR__ . '/../config/db.php';

class Announcement {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Get all announcements with publisher info and optional filtering
     */
    public function getAllAnnouncements($statusFilter = 'All', $search = '', $priorityFilter = '') {
        $sql = "
            SELECT 
                a.announcement_id,
                a.published_by_user_id,
                a.title,
                a.content,
                a.target_audience,
                a.priority,
                a.status,
                a.created_at,
                a.updated_at,
                COALESCE(u.username, 'President') AS publisher_name,
                COALESCE(u.email, 'president@uoc.ac.lk') AS publisher_email
            FROM announcements a
            LEFT JOIN user u ON a.published_by_user_id = u.user_id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($statusFilter) && $statusFilter !== 'All') {
            $sql .= " AND a.status = :status";
            $params[':status'] = $statusFilter;
        }

        if (!empty($priorityFilter) && $priorityFilter !== 'All') {
            $sql .= " AND a.priority = :priority";
            $params[':priority'] = $priorityFilter;
        }

        if (!empty($search)) {
            $sql .= " AND (a.title LIKE :s1 OR a.content LIKE :s2 OR a.target_audience LIKE :s3)";
            $params[':s1'] = "%{$search}%";
            $params[':s2'] = "%{$search}%";
            $params[':s3'] = "%{$search}%";
        }

        $sql .= " ORDER BY 
                    CASE WHEN a.priority = 'Urgent' AND a.status = 'Active' THEN 1
                         WHEN a.priority = 'Important' AND a.status = 'Active' THEN 2
                         ELSE 3 END,
                    a.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single announcement by ID
     */
    public function getAnnouncementById($id) {
        $sql = "
            SELECT 
                a.announcement_id,
                a.published_by_user_id,
                a.title,
                a.content,
                a.target_audience,
                a.priority,
                a.status,
                a.created_at,
                a.updated_at,
                COALESCE(u.username, 'President') AS publisher_name,
                COALESCE(u.email, 'president@uoc.ac.lk') AS publisher_email
            FROM announcements a
            LEFT JOIN user u ON a.published_by_user_id = u.user_id
            WHERE a.announcement_id = :id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new announcement
     */
    public function createAnnouncement($data, $userId) {
        $title     = trim($data['title'] ?? '');
        $content   = trim($data['content'] ?? '');
        $audience  = trim($data['target_audience'] ?? 'All Users');
        $priority  = trim($data['priority'] ?? 'Normal');
        $status    = trim($data['status'] ?? 'Active');

        // Validation
        $allowedAudiences = ['All Users', 'Student Athletes', 'Coaches & Staff', 'Medical Staff', 'TIC'];
        $allowedPriorities = ['Normal', 'Important', 'Urgent'];
        $allowedStatuses   = ['Active', 'Archived'];

        if (!in_array($audience, $allowedAudiences, true)) {
            $audience = 'All Users';
        }
        if (!in_array($priority, $allowedPriorities, true)) {
            $priority = 'Normal';
        }
        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'Active';
        }

        if (empty($title) || empty($content)) {
            return false;
        }

        $sql = "
            INSERT INTO announcements (
                published_by_user_id, title, content, target_audience, priority, status, created_at
            ) VALUES (
                :uid, :title, :content, :audience, :priority, :status, NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':uid'      => (int)$userId,
            ':title'    => $title,
            ':content'  => $content,
            ':audience' => $audience,
            ':priority' => $priority,
            ':status'   => $status
        ]);
    }

    /**
     * Update an existing announcement
     */
    public function updateAnnouncement($id, $data) {
        $id        = (int)$id;
        $title     = trim($data['title'] ?? '');
        $content   = trim($data['content'] ?? '');
        $audience  = trim($data['target_audience'] ?? 'All Users');
        $priority  = trim($data['priority'] ?? 'Normal');
        $status    = trim($data['status'] ?? 'Active');

        $allowedAudiences = ['All Users', 'Student Athletes', 'Coaches & Staff', 'Medical Staff', 'TIC'];
        $allowedPriorities = ['Normal', 'Important', 'Urgent'];
        $allowedStatuses   = ['Active', 'Archived'];

        if (!in_array($audience, $allowedAudiences, true)) {
            $audience = 'All Users';
        }
        if (!in_array($priority, $allowedPriorities, true)) {
            $priority = 'Normal';
        }
        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'Active';
        }

        if ($id <= 0 || empty($title) || empty($content)) {
            return false;
        }

        $sql = "
            UPDATE announcements 
            SET title = :title,
                content = :content,
                target_audience = :audience,
                priority = :priority,
                status = :status,
                updated_at = NOW()
            WHERE announcement_id = :id
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title'    => $title,
            ':content'  => $content,
            ':audience' => $audience,
            ':priority' => $priority,
            ':status'   => $status,
            ':id'       => $id
        ]);
    }

    /**
     * Delete an announcement by ID
     */
    public function deleteAnnouncement($id) {
        $stmt = $this->db->prepare("DELETE FROM announcements WHERE announcement_id = :id");
        return $stmt->execute([':id' => (int)$id]);
    }

    /**
     * Toggle announcement status between Active and Archived
     */
    public function toggleStatus($id) {
        $item = $this->getAnnouncementById($id);
        if (!$item) return false;

        $newStatus = ($item['status'] === 'Active') ? 'Archived' : 'Active';
        $stmt = $this->db->prepare("UPDATE announcements SET status = :status, updated_at = NOW() WHERE announcement_id = :id");
        return $stmt->execute([
            ':status' => $newStatus,
            ':id'     => (int)$id
        ]);
    }

    /**
     * Get statistics for President Command Center
     */
    public function getStats() {
        $sql = "
            SELECT 
                COUNT(*) AS total,
                COALESCE(SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END), 0) AS active_count,
                COALESCE(SUM(CASE WHEN status = 'Archived' THEN 1 ELSE 0 END), 0) AS archived_count,
                COALESCE(SUM(CASE WHEN priority = 'Urgent' THEN 1 ELSE 0 END), 0) AS urgent_count
            FROM announcements
        ";
        $stmt = $this->db->query($sql);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total'    => (int)($res['total'] ?? 0),
            'active'   => (int)($res['active_count'] ?? 0),
            'archived' => (int)($res['archived_count'] ?? 0),
            'urgent'   => (int)($res['urgent_count'] ?? 0)
        ];
    }

    /**
     * Get active announcements for top-bar universal notification bell
     */
    public function getActiveForNotifications($limit = 8) {
        $sql = "
            SELECT 
                a.announcement_id,
                a.title,
                a.content,
                a.target_audience,
                a.priority,
                a.created_at,
                COALESCE(u.username, 'President') AS publisher_name
            FROM announcements a
            LEFT JOIN user u ON a.published_by_user_id = u.user_id
            WHERE a.status = 'Active'
            ORDER BY 
                CASE WHEN a.priority = 'Urgent' THEN 1
                     WHEN a.priority = 'Important' THEN 2
                     ELSE 3 END,
                a.created_at DESC
            LIMIT " . (int)$limit;

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
