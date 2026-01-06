<?php
/**
 * =====================================================
 * MedFlow - متحكم إدارة الأدوية
 * =====================================================
 */

class MedicationController
{
    /**
     * عرض قائمة الأدوية
     */
    public function index(): void
    {
        AuthController::checkSession();
        
        $medications = Database::fetchAll("SELECT * FROM medications ORDER BY name ASC");
        
        $pageTitle = 'إدارة الأدوية';
        require VIEWS_PATH . 'medications/index.php';
    }
    
    /**
     * حفظ دواء جديد
     */
    public function store(): void
    {
        AuthController::checkSession();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('medications');
        }
        
        $name = clean($_POST['name'] ?? '');
        $dosage = clean($_POST['default_dosage'] ?? '');
        $frequency = clean($_POST['default_frequency'] ?? '');
        $duration = clean($_POST['default_duration'] ?? '');
        
        if (empty($name)) {
            $_SESSION['flash_error'] = 'يرجى إدخال اسم الدواء';
            redirect('medications');
        }
        
        Database::insert('medications', [
            'name' => $name,
            'default_dosage' => $dosage,
            'default_frequency' => $frequency,
            'default_duration' => $duration,
            'is_active' => 1
        ]);
        
        // إذا كان طلب AJAX (إضافة سريعة من الروشتة)
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            jsonResponse([
                'success' => true,
                'message' => 'تم إضافة الدواء بنجاح',
                'medication' => [
                    'id' => Database::getConnection()->lastInsertId(),
                    'name' => $name,
                    'dosage' => $dosage,
                    'frequency' => $frequency,
                    'duration' => $duration
                ]
            ]);
        }
        
        $_SESSION['flash_success'] = 'تم إضافة الدواء بنجاح';
        redirect('medications');
    }
    
    /**
     * تعديل دواء
     */
    public function update(int $id): void
    {
        AuthController::checkSession();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('medications');
        }
        
        $name = clean($_POST['name'] ?? '');
        $active = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($name)) {
            jsonResponse(['error' => 'اسم الدواء مطلوب'], 400);
        }
        
        Database::update('medications', [
            'name' => $name,
            'default_dosage' => clean($_POST['default_dosage'] ?? ''),
            'default_frequency' => clean($_POST['default_frequency'] ?? ''),
            'default_duration' => clean($_POST['default_duration'] ?? ''),
            'is_active' => $active
        ], 'id = ?', [$id]);
        
        jsonResponse(['success' => true, 'message' => 'تم تحديث الدواء']);
    }
    
    /**
     * حذف (إلغاء تفعيل) دواء
     */
    public function delete(int $id): void
    {
        AuthController::checkSession();
        AuthController::requireRole('admin', 'doctor');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'طريقة غير مسموحة'], 405);
        }
        
        Database::update('medications', ['is_active' => 0], 'id = ?', [$id]);
        
        jsonResponse(['success' => true, 'message' => 'تم حذف الدواء']);
    }
    
    /**
     * جلب بيانات دواء
     */
    public function show(int $id): void
    {
        AuthController::checkSession();
        
        $med = Database::fetchOne("SELECT * FROM medications WHERE id = ?", [$id]);
        
        if ($med) {
            jsonResponse(['success' => true, 'medication' => $med]);
        } else {
            jsonResponse(['error' => 'الدواء غير موجود'], 404);
        }
    }
    
    /**
     * البحث عن دواء (Autocomplete)
     */
    public function search(): void
    {
        AuthController::checkSession();
        
        $query = clean($_GET['q'] ?? '');
        
        if (strlen($query) < 2) {
            jsonResponse(['results' => []]);
        }
        
        $results = Database::fetchAll(
            "SELECT * FROM medications 
             WHERE is_active = 1 AND name LIKE ? 
             ORDER BY name ASC LIMIT 20",
            ["%$query%"]
        );
        
        jsonResponse(['results' => $results]);
    }
}
