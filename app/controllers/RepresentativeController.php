<?php
/**
 * =====================================================
 * MedFlow - متحكم إدارة المناديب
 * =====================================================
 */

class RepresentativeController
{
    /**
     * عرض قائمة المناديب
     */
    public function index(): void
    {
        AuthController::checkSession();
        
        $reps = Database::fetchAll(
            "SELECT r.*, c.name as company_name 
             FROM representatives r
             JOIN companies c ON r.company_id = c.id 
             WHERE r.is_active = 1 
             ORDER BY r.full_name ASC"
        );
        
        $companies = Database::fetchAll("SELECT * FROM companies WHERE is_active = 1 ORDER BY name ASC");
        
        $pageTitle = 'إدارة المناديب';
        require VIEWS_PATH . 'representatives/index.php';
    }
    
    /**
     * حفظ مندوب جديد
     */
    public function store(): void
    {
        AuthController::checkSession();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('representatives');
        }
        
        $name = clean($_POST['full_name'] ?? '');
        $companyId = (int)($_POST['company_id'] ?? 0);
        $phone = clean($_POST['phone'] ?? '');
        $email = clean($_POST['email'] ?? '');
        
        if (empty($name) || !$companyId) {
            $_SESSION['flash_error'] = 'يرجى إدخال اسم المندوب والشركة';
            redirect('representatives');
        }
        
        Database::insert('representatives', [
            'full_name' => $name,
            'company_id' => $companyId,
            'phone' => $phone,
            'email' => $email,
            'is_active' => 1
        ]);
        
        $_SESSION['flash_success'] = 'تم إضافة المندوب بنجاح';
        redirect('representatives');
    }
    
    /**
     * تعديل بيانات مندوب
     */
    public function update(int $id): void
    {
        AuthController::checkSession();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('representatives');
        }
        
        $name = clean($_POST['full_name'] ?? '');
        $companyId = (int)($_POST['company_id'] ?? 0);
        $phone = clean($_POST['phone'] ?? '');
        $email = clean($_POST['email'] ?? '');
        
        if (empty($name) || !$companyId) {
            jsonResponse(['error' => 'بيانات ناقصة'], 400);
        }
        
        Database::update('representatives', [
            'full_name' => $name,
            'company_id' => $companyId,
            'phone' => $phone,
            'email' => $email
        ], 'id = ?', [$id]);
        
        jsonResponse(['success' => true, 'message' => 'تم تحديث البيانات بنجاح']);
    }
    
    /**
     * حذف (إلغاء تفعيل) مندوب
     */
    public function delete(int $id): void
    {
        AuthController::checkSession();
        AuthController::requireRole('admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'طريقة غير مسموحة'], 405);
        }
        
        // Soft Delete
        Database::update('representatives', ['is_active' => 0], 'id = ?', [$id]);
        
        jsonResponse(['success' => true, 'message' => 'تم حذف المندوب بنجاح']);
    }
    
    /**
     * جلب بيانات مندوب للمودال
     */
    public function show(int $id): void
    {
        AuthController::checkSession();
        
        $rep = Database::fetchOne("SELECT * FROM representatives WHERE id = ?", [$id]);
        
        if ($rep) {
            jsonResponse(['success' => true, 'rep' => $rep]);
        } else {
            jsonResponse(['error' => 'المندوب غير موجود'], 404);
        }
    }
}
