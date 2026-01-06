<?php
/**
 * متحكم سجل النشاطات
 * Activity Log Controller
 */
class ActivityLogController
{
    /**
     * عرض السجل
     */
    public function index(): void
    {
        AuthController::checkSession();
        AuthController::requireRole('admin');
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // الفلاتر
        $where = "WHERE 1=1";
        $params = [];
        
        if (!empty($_GET['user_id'])) {
            $where .= " AND l.user_id = ?";
            $params[] = $_GET['user_id'];
        }
        
        if (!empty($_GET['action'])) {
            $where .= " AND l.action = ?";
            $params[] = $_GET['action'];
        }
        
        if (!empty($_GET['date'])) {
            $where .= " AND DATE(l.created_at) = ?";
            $params[] = $_GET['date'];
        }
        
        // إجمالي السجلات
        $total = Database::fetchOne(
            "SELECT COUNT(*) as count FROM activity_logs l $where",
            $params
        )['count'];
        
        $totalPages = ceil($total / $limit);
        
        // جلب السجلات
        $logs = Database::fetchAll(
            "SELECT l.*, u.full_name as user_name 
             FROM activity_logs l 
             LEFT JOIN users u ON l.user_id = u.id 
             $where 
             ORDER BY l.created_at DESC 
             LIMIT $limit OFFSET $offset",
            $params
        );
        
        // جلب المستخدمين للفلتر
        $users = Database::fetchAll("SELECT id, full_name FROM users ORDER BY full_name");
        
        require VIEWS_PATH . 'activity_logs/index.php';
    }
}
