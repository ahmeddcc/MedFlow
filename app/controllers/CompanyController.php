<?php
/**
 * =====================================================
 * MedFlow - متحكم إدارة الشركات
 * =====================================================
 */

class CompanyController
{
    /**
     * عرض قائمة الشركات
     */
    public function index(): void
    {
        AuthController::checkSession();
        
        $companies = Database::fetchAll("SELECT * FROM companies ORDER BY name ASC");
        
        $pageTitle = 'إدارة الشركات';
        require VIEWS_PATH . 'companies/index.php';
    }
    
    /**
     * حفظ شركة جديدة
     */
    public function store(): void
    {
        AuthController::checkSession();
        AuthController::requireRole('admin', 'doctor');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('companies');
        }
        
        $name = clean($_POST['name'] ?? '');
        $letter = clean($_POST['letter'] ?? '');
        $email = clean($_POST['email'] ?? '');
        $phone = clean($_POST['phone'] ?? '');
        
        if (empty($name) || empty($letter)) {
            $_SESSION['flash_error'] = 'يرجى إدخال اسم الشركة والحرف المميز';
            redirect('companies');
        }
        
        // التحقق من تكرار الحرف
        $exists = Database::fetchOne("SELECT id FROM companies WHERE letter = ?", [$letter]);
        if ($exists) {
            $_SESSION['flash_error'] = 'الحرف المميز مستخدم بالفعل لشركة أخرى';
            redirect('companies');
        }
        
        // معالجة الشعار (اختياري)
        $logoPath = null;
        if (!empty($_FILES['logo']['name'])) {
            $uploadDir = UPLOADS_PATH . 'companies/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $filename = uniqid() . '_' . basename($_FILES['logo']['name']);
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $filename)) {
                $logoPath = 'uploads/companies/' . $filename;
            }
        }
        
        Database::insert('companies', [
            'name' => $name,
            'letter' => strtoupper($letter),
            'email' => $email,
            'phone' => $phone,
            'logo' => $logoPath,
            'is_active' => 1
        ]);
        
        $_SESSION['flash_success'] = 'تم إضافة الشركة بنجاح';
        redirect('companies');
    }
    
    /**
     * تعديل بيانات الشركة
     */
    public function update(int $id): void
    {
        AuthController::checkSession();
        AuthController::requireRole('admin', 'doctor');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('companies');
        }
        
        $name = clean($_POST['name'] ?? '');
        $letter = clean($_POST['letter'] ?? '');
        $email = clean($_POST['email'] ?? '');
        $phone = clean($_POST['phone'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($name) || empty($letter)) {
            jsonResponse(['error' => 'بيانات ناقصة'], 400);
        }
        
        // التحقق من تكرار الحرف لغير هذه الشركة
        $exists = Database::fetchOne("SELECT id FROM companies WHERE letter = ? AND id != ?", [$letter, $id]);
        if ($exists) {
            jsonResponse(['error' => 'الحرف المميز مستخدم بالفعل'], 400);
        }
        
        $data = [
            'name' => $name,
            'letter' => strtoupper($letter),
            'email' => $email,
            'phone' => $phone,
            'is_active' => $isActive
        ];
        
        // تحديث الشعار إذا تم رفعه
        if (!empty($_FILES['logo']['name'])) {
            $uploadDir = UPLOADS_PATH . 'companies/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $filename = uniqid() . '_' . basename($_FILES['logo']['name']);
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $filename)) {
                $data['logo'] = 'uploads/companies/' . $filename;
            }
        }
        
        Database::update('companies', $data, 'id = ?', [$id]);
        
        jsonResponse(['success' => true, 'message' => 'تم تحديث البيانات بنجاح']);
    }
    
    /**
     * حذف شركة
     */
    public function delete(int $id): void
    {
        AuthController::checkSession();
        AuthController::requireRole('admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'طريقة غير مسموحة'], 405);
        }
        
        // التحقق من وجود مناديب أو سجلات
        $hasReps = Database::count('representatives', 'company_id = ?', [$id]);
        $hasVisits = Database::count('rep_waiting_list', 'company_id = ?', [$id]);
        
        if ($hasReps > 0 || $hasVisits > 0) {
            // Soft delete بدلاً من الحذف النهائي إذا كان هناك بيانات مرتبطة
            Database::update('companies', ['is_active' => 0], 'id = ?', [$id]);
            jsonResponse(['success' => true, 'message' => 'تم إلغاء تفعيل الشركة لوجود سجلات مرتبطة بها']);
        } else {
            Database::delete('companies', 'id = ?', [$id]);
            jsonResponse(['success' => true, 'message' => 'تم حذف الشركة بنجاح']);
        }
    }
    
    /**
     * جلب بيانات شركة للمودال
     */
    public function show(int $id): void
    {
        AuthController::checkSession();
        
        $company = Database::fetchOne("SELECT * FROM companies WHERE id = ?", [$id]);
        
        if ($company) {
            jsonResponse(['success' => true, 'company' => $company]);
        } else {
            jsonResponse(['error' => 'الشركة غير موجودة'], 404);
        }
    }
}
