<?php
/**
 * =====================================================
 * MedFlow - متحكم إدارة التحاليل
 * =====================================================
 */

class LabTestController
{
    /**
     * عرض قائمة التحاليل
     */
    public function index(): void
    {
        AuthController::checkSession();
        
        $tests = Database::fetchAll("SELECT * FROM lab_tests ORDER BY name ASC");
        
        $pageTitle = 'إدارة التحاليل';
        require VIEWS_PATH . 'lab_tests/index.php';
    }
    
    /**
     * حفظ تحليل جديد
     */
    public function store(): void
    {
        AuthController::checkSession();
        AuthController::requireRole('admin', 'doctor');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('lab-tests');
        }
        
        $name = clean($_POST['name'] ?? '');
        
        if (empty($name)) {
            $_SESSION['flash_error'] = 'يرجى إدخال اسم التحليل';
            redirect('lab-tests');
        }
        
        Database::insert('lab_tests', [
            'name' => $name,
            'normal_range' => clean($_POST['normal_range'] ?? ''),
            'unit' => clean($_POST['unit'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'is_active' => 1
        ]);
        
        $_SESSION['flash_success'] = 'تم إضافة التحليل بنجاح';
        redirect('lab-tests');
    }
    
    /**
     * تعديل تحليل
     */
    public function update(int $id): void
    {
        AuthController::checkSession();
        AuthController::requireRole('admin', 'doctor');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('lab-tests');
        }
        
        $name = clean($_POST['name'] ?? '');
        $active = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($name)) {
            jsonResponse(['error' => 'اسم التحليل مطلوب'], 400);
        }
        
        Database::update('lab_tests', [
            'name' => $name,
            'normal_range' => clean($_POST['normal_range'] ?? ''),
            'unit' => clean($_POST['unit'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'is_active' => $active
        ], 'id = ?', [$id]);
        
        jsonResponse(['success' => true, 'message' => 'تم تحديث التحليل']);
    }
    
    /**
     * حذف تحليل
     */
    public function delete(int $id): void
    {
        AuthController::checkSession();
        AuthController::requireRole('admin', 'doctor');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'طريقة غير مسموحة'], 405);
        }
        
        Database::update('lab_tests', ['is_active' => 0], 'id = ?', [$id]);
        
        jsonResponse(['success' => true, 'message' => 'تم حذف التحليل']);
    }
    
    /**
     * جلب بيانات تحليل
     */
    public function show(int $id): void
    {
        AuthController::checkSession();
        
        $test = Database::fetchOne("SELECT * FROM lab_tests WHERE id = ?", [$id]);
        
        if ($test) {
            jsonResponse(['success' => true, 'test' => $test]);
        } else {
            jsonResponse(['error' => 'التحليل غير موجود'], 404);
        }
    }
}
