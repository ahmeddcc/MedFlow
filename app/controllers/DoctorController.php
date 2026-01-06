<?php

require_once __DIR__ . '/../core/Controller.php';

class DoctorController extends Controller {

    public function __construct() {
        // التأكد من الصلاحيات - طبيب أو مدير
        AuthController::checkSession();
        // AuthController::requireRole('doctor'); // يمكن تفعيل هذا لاحقاً
    }
    /**
     * استدعاء المساعدة (يرسل تنبيه تليجرام)
     */
    public function summonAssistant() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        // في الواقع يجب جلب اسم الطبيب من الجلسة
        $doctorName = $_SESSION['user_name'] ?? 'الطبيب';
        
        // إرسال تنبيه للمشرفين
        require_once __DIR__ . '/../services/TelegramService.php';
        $telegram = new TelegramService();
        $message = "📢 *نداء عاجل*\n\n" . 
                   "👨‍⚕️ **$doctorName** يطلب المساعدة في العيادة.\n" .
                   "⏰ " . date('h:i A');
                   
        $telegram->sendOperationMessage($message);
        
        jsonResponse(['success' => true, 'message' => 'تم إرسال النداء بنجاح']);
    }

    /**
     * تحويل المريض أو تعليق الحالة
     */
    public function transferPatient() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $visitId = $_POST['visit_id'] ?? 0;
        $action = $_POST['action'] ?? ''; // hold, reception, lab
        
        if (!$visitId || !$action) {
            jsonResponse(['success' => false, 'error' => 'بيانات غير مكتملة']);
            return;
        }

        $updates = [];
        if ($action === 'hold') {
            $updates = ['status' => 'waiting']; // يعود للانتظار
            // يمكن إضافة منطق لرفع الأولويتة هنا (مثلاً تعديل وقت الدخول ليكون قديماً جداً)
        } elseif ($action === 'reception') {
            $updates = ['status' => 'completed']; // يعتبر مكتمل من العيادة لكن سيذهب للاستقبال
            // منطق إضافي لإنشاء تذكرة استقبال إذا لزم الأمر
        }
        
        if (!empty($updates)) {
            Database::update('waiting_list', $updates, 'id = ?', [$visitId]);
            jsonResponse(['success' => true]);
        }
        
        jsonResponse(['success' => false]);
    }


    /**
     * واجهة مكتب الطبيب الرئيسية
     */
    public function index() {
        // 1. جلب الدور الحالي (status = 'entered')
        $currentVisit = Database::fetchOne(
            "SELECT w.*, p.full_name, p.gender, p.date_of_birth, p.phone, p.electronic_number as file_number 
             FROM waiting_list w
             JOIN patients p ON w.patient_id = p.id
             WHERE w.status = 'entered' AND date(w.created_at) = CURDATE()
             ORDER BY w.entered_at DESC LIMIT 1"
        );

        // 2. جلب قائمة الانتظار (status = 'waiting' or 'called')
        $waitingList = Database::fetchAll(
            "SELECT w.*, p.full_name 
             FROM waiting_list w
             JOIN patients p ON w.patient_id = p.id
             WHERE w.status IN ('waiting', 'called') AND date(w.created_at) = CURDATE()
             ORDER BY w.turn_number ASC"
        );

        // 3. إذا كان هناك مريض حالي، جلب تاريخه المرضي
        $history = [];
        if ($currentVisit) {
            $history = Database::fetchAll(
                "SELECT * FROM invoices 
                 WHERE patient_id = ? 
                 ORDER BY created_at DESC LIMIT 5",
                [$currentVisit['patient_id']]
            );
        }

        require VIEWS_PATH . 'doctor/workbench.php';
    }

    /**
     * حفظ ملاحظات الزيارة / التشخيص
     */
    public function saveNotes() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $visitId = $_POST['visit_id'] ?? 0;
        $notes = clean($_POST['doctor_notes'] ?? '');
        $diagnosis = clean($_POST['diagnosis'] ?? '');
        
        if ($visitId) {
            Database::update('waiting_list', [
                'doctor_notes' => $notes,
                'diagnosis' => $diagnosis
            ], 'id = ?', [$visitId]);
            
            jsonResponse(['success' => true]);
        }
        jsonResponse(['success' => false]);
    }
}
