<?php
/**
 * متحكم الوصفات والتحاليل
 * Prescription & Lab Controller
 */
class PrescriptionController
{
    /**
     * عرض صفحة الوصفات
     */
    public function index(): void
    {
        AuthController::checkSession();
        
        $date = clean($_GET['date'] ?? date('Y-m-d'));
        
        $prescriptions = Database::fetchAll(
            "SELECT * FROM v_prescriptions WHERE DATE(created_at) = ? ORDER BY created_at DESC",
            [$date]
        );
        
        $medications = Database::fetchAll("SELECT * FROM medications WHERE is_active = 1 ORDER BY name");
        
        require VIEWS_PATH . 'prescriptions/index.php';
    }
    
    /**
     * إنشاء وصفة جديدة
     */
    public function create(): void
    {
        AuthController::checkSession();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'طريقة غير مسموحة'], 405);
        }
        
        $patientId = (int)($_POST['patient_id'] ?? 0);
        $diagnosis = clean($_POST['diagnosis'] ?? '');
        $notes = clean($_POST['notes'] ?? '');
        $items = json_decode($_POST['items'] ?? '[]', true);
        
        if (!$patientId) {
            jsonResponse(['error' => 'يرجى تحديد المريض'], 400);
        }
        
        if (empty($items)) {
            jsonResponse(['error' => 'يرجى إضافة دواء واحد على الأقل'], 400);
        }
        
        // رقم الوصفة
        $prescriptionNumber = 'RX-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // إنشاء الوصفة
        $prescriptionId = Database::insert('prescriptions', [
            'prescription_number' => $prescriptionNumber,
            'patient_id' => $patientId,
            'diagnosis' => $diagnosis,
            'notes' => $notes,
            'created_by' => $_SESSION['user_id']
        ]);
        
        // إضافة البنود
        foreach ($items as $item) {
            Database::insert('prescription_items', [
                'prescription_id' => $prescriptionId,
                'medication_id' => $item['medication_id'] ?? null,
                'medication_name' => $item['name'],
                'dosage' => $item['dosage'] ?? '',
                'frequency' => $item['frequency'] ?? '',
                'duration' => $item['duration'] ?? '',
                'instructions' => $item['instructions'] ?? ''
            ]);
        }
        
        logAction('create_prescription', 'prescriptions', $prescriptionId);
        
        jsonResponse([
            'success' => true,
            'message' => 'تم إنشاء الوصفة بنجاح',
            'prescription_id' => $prescriptionId,
            'prescription_number' => $prescriptionNumber
        ]);
    }
    
    /**
     * عرض وصفة
     */
    public function show(): void
    {
        AuthController::checkSession();
        
        $id = (int)($_GET['id'] ?? 0);
        
        $prescription = Database::fetchOne(
            "SELECT p.*, pt.full_name AS patient_name, pt.electronic_number, pt.date_of_birth
             FROM prescriptions p
             JOIN patients pt ON p.patient_id = pt.id
             WHERE p.id = ?",
            [$id]
        );
        
        if (!$prescription) {
            jsonResponse(['error' => 'الوصفة غير موجودة'], 404);
        }
        
        $items = Database::fetchAll(
            "SELECT * FROM prescription_items WHERE prescription_id = ?",
            [$id]
        );
        
        jsonResponse([
            'success' => true,
            'prescription' => $prescription,
            'items' => $items
        ]);
    }
    
    /**
     * البحث عن مريض
     */
    public function searchPatient(): void
    {
        AuthController::checkSession();
        
        $query = clean($_GET['q'] ?? '');
        
        if (strlen($query) < 2) {
            jsonResponse(['results' => []]);
        }
        
        $results = Database::fetchAll(
            "SELECT id, full_name, electronic_number, phone 
             FROM patients 
             WHERE full_name LIKE ? OR electronic_number LIKE ? OR phone LIKE ?
             LIMIT 10",
            ["%$query%", "%$query%", "%$query%"]
        );
        
        jsonResponse(['results' => $results]);
    }
    
    /**
     * الأدوية المتاحة
     */
    public function medications(): void
    {
        AuthController::checkSession();
        
        $medications = Database::fetchAll("SELECT * FROM medications WHERE is_active = 1 ORDER BY name");
        
        jsonResponse(['medications' => $medications]);
    }
    
    // ==================== التحاليل ====================
    
    /**
     * عرض صفحة التحاليل
     */
    public function labOrders(): void
    {
        AuthController::checkSession();
        
        $filter = clean($_GET['filter'] ?? 'all');
        $date = clean($_GET['date'] ?? date('Y-m-d'));
        
        $where = "DATE(o.created_at) = ?";
        $params = [$date];
        
        if ($filter !== 'all') {
            $where .= " AND o.status = ?";
            $params[] = $filter;
        }
        
        $orders = Database::fetchAll(
            "SELECT o.*, p.full_name AS patient_name, p.electronic_number, t.name AS test_name
             FROM lab_orders o
             JOIN patients p ON o.patient_id = p.id
             JOIN lab_tests t ON o.lab_test_id = t.id
             WHERE $where
             ORDER BY o.created_at DESC",
            $params
        );
        
        $tests = Database::fetchAll("SELECT * FROM lab_tests WHERE is_active = 1 ORDER BY name");
        
        require VIEWS_PATH . 'prescriptions/lab.php';
    }
    
    /**
     * طلب تحليل جديد
     */
    public function createLabOrder(): void
    {
        AuthController::checkSession();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'طريقة غير مسموحة'], 405);
        }
        
        $patientId = (int)($_POST['patient_id'] ?? 0);
        $testId = (int)($_POST['test_id'] ?? 0);
        $notes = clean($_POST['notes'] ?? '');
        
        if (!$patientId || !$testId) {
            jsonResponse(['error' => 'يرجى تحديد المريض والتحليل'], 400);
        }
        
        // رقم الطلب
        $orderNumber = 'LAB-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $orderId = Database::insert('lab_orders', [
            'order_number' => $orderNumber,
            'patient_id' => $patientId,
            'lab_test_id' => $testId,
            'notes' => $notes,
            'created_by' => $_SESSION['user_id']
        ]);
        
        logAction('create_lab_order', 'lab_orders', $orderId);
        
        jsonResponse([
            'success' => true,
            'message' => 'تم طلب التحليل بنجاح',
            'order_id' => $orderId
        ]);
    }
    
    /**
     * إدخال نتيجة تحليل
     */
    public function updateLabResult(): void
    {
        AuthController::checkSession();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'طريقة غير مسموحة'], 405);
        }
        
        $orderId = (int)($_POST['order_id'] ?? 0);
        $resultValue = clean($_POST['result_value'] ?? '');
        $resultStatus = clean($_POST['result_status'] ?? 'normal');
        $result = clean($_POST['result'] ?? '');
        
        if (!$orderId) {
            jsonResponse(['error' => 'معرف غير صحيح'], 400);
        }
        
        $data = [
            'result_value' => $resultValue,
            'result_status' => $resultStatus,
            'result' => $result,
            'result_date' => date('Y-m-d H:i:s'),
            'status' => 'completed'
        ];
        
        // معالجة المرفقات
        if (!empty($_FILES['attachment']['name'])) {
            $uploadDir = UPLOADS_PATH . 'lab_results/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $filename = 'LAB_' . $orderId . '_' . uniqid() . '.' . pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
            
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadDir . $filename)) {
                $data['attachment_path'] = 'uploads/lab_results/' . $filename;
            }
        }
        
        Database::update('lab_orders', $data, 'id = ?', [$orderId]);
        
        logAction('update_lab_result', 'lab_orders', $orderId);
        
        jsonResponse(['success' => true, 'message' => 'تم حفظ النتيجة']);
    }
    
    /**
     * التحاليل المتاحة
     */
    public function labTests(): void
    {
        AuthController::checkSession();
        
        $tests = Database::fetchAll("SELECT * FROM lab_tests WHERE is_active = 1 ORDER BY name");
        
        jsonResponse(['tests' => $tests]);
    }
}
