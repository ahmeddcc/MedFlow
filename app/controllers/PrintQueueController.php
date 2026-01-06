<?php
/**
 * متحكم إدارة طابور الطباعة
 * Print Queue Manager
 */
require_once __DIR__ . '/../core/Controller.php';

class PrintQueueController extends Controller
{
    /**
     * عرض طابور الطباعة
     */
    public function index(): void
    {
        AuthController::checkSession();
        AuthController::requireRole('admin', 'doctor', 'receptionist');
        
        $jobs = Database::fetchAll(
            "SELECT j.*, p.name as printer_name, u.full_name as user_name
             FROM print_jobs j
             JOIN printers p ON j.printer_id = p.id
             LEFT JOIN users u ON j.created_by = u.id
             ORDER BY j.created_at DESC
             LIMIT 50"
        );
        
        require VIEWS_PATH . 'print/queue.php';
    }
    
    /**
     * إعادة محاولة طباعة
     */
    public function retry(): void
    {
        AuthController::checkSession();
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id) {
            Database::update('print_jobs', ['status' => 'pending', 'attempts' => 0], 'id = ?', [$id]);
            flash('success', 'تم إعادة جدولة المهمة');
        }
        
        redirect('print-queue');
    }
    
    /**
     * حذف مهمة
     */
    public function delete(): void
    {
        AuthController::checkSession();
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id) {
            Database::query("DELETE FROM print_jobs WHERE id = ?", [$id]);
            flash('success', 'تم حذف المهمة');
        }
        
        redirect('print-queue');
    }
}
