<?php
/**
 * متحكم النسخ الاحتياطي
 * Backup Controller
 */
require_once __DIR__ . '/../core/Controller.php';

class BackupController extends Controller
{
    private $backupService;

    public function __construct()
    {
        require_once APP_PATH . 'services/BackupService.php';
        $this->backupService = new BackupService();
    }

    /**
     * عرض الصفحة (عادة ضمن الإعدادات)
     */
    public function index(): void
    {
        AuthController::checkSession();
        AuthController::requireRole('admin');
        
        // لا يوجد صفحة مستقلة، بل يتم تضمينها في الإعدادات
        // أو يمكننا عمل صفحة بسيطة
        redirect('settings#backups');
    }

    /**
     * إنشاء نسخة جديدة
     */
    public function create(): void
    {
        AuthController::checkSession();
        AuthController::requireRole('admin');
        
        try {
            $filename = $this->backupService->createBackup();
            
            if (isAjax()) {
                jsonResponse(['success' => true, 'message' => "تم إنشاء النسخة $filename بنجاح"]);
            }
            
            flash('success', "تم إنشاء النسخة الاحتياطية بنجاح: $filename");
            
        } catch (Exception $e) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
            }
            flash('error', 'فشل إنشاء النسخة: ' . $e->getMessage());
        }
        
        redirect('settings#backups');
    }

    /**
     * تحميل نسخة
     */
    public function download(): void
    {
        AuthController::checkSession();
        AuthController::requireRole('admin');
        
        $file = $_GET['file'] ?? '';
        $path = $this->backupService->getBackupPath($file);
        
        if ($path && file_exists($path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($path).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($path));
            readfile($path);
            exit;
        }
        
        flash('error', 'الملف غير موجود');
        redirect('settings#backups');
    }
}
