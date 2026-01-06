<?php

require_once __DIR__ . '/../services/LicenseService.php';

class LicenseController extends Controller {
    
    /**
     * عرض صفحة القفل (عند انتهاء الترخيص أو عدم صلاحيته)
     */
    public function lock() {
        // لا نحتاج للتحقق من الجلسة هنا لأننا نريد السماح للمسؤول بالدخول لتفعيل النظام
        // ولكن يفضل التأكد من تسجيل الدخول لعرض الاسم وللأمان
        AuthController::checkSession();
        
        $machineID = LicenseService::getMachineID();
        $status = LicenseService::getStatus();
        
        // إذا كان النظام مفعل أصلاً، نعيده للرئيسية
        if ($status === 'active') {
            redirect('');
            return;
        }
        
        require VIEWS_PATH . 'license/lock.php';
    }
    
    /**
     * معالجة طلب التفعيل
     */
    public function activate() {
        AuthController::checkSession();
        AuthController::requireRole('admin'); // فقط المدير يمكنه التفعيل
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $key = clean($_POST['license_key'] ?? '');
            
            if (LicenseService::validateLicense($key)) {
                setSetting('license_key', $key);
                setSetting('license_status', 'active');
                setFlash('success', 'تم تفعيل النظام بنجاح! شكراً لك.');
                redirect('');
            } else {
                setFlash('error', 'مفتاح الترخيص غير صحيح لهذا الجهاز.');
                redirect('license/lock');
            }
        }
    }
}
