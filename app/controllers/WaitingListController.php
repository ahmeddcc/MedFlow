<?php
/**
 * =====================================================
 * MedFlow - متحكم قائمة الانتظار
 * =====================================================
 */

class WaitingListController
{
    /**
     * عرض قائمة الانتظار
     */
    public function index(): void
    {
        AuthController::checkSession();
        
        $today = date('Y-m-d');
        
        // جلب قائمة الانتظار لليوم (مرتبة حسب الأولوية ثم الدور)
        $waitingList = Database::fetchAll(
            "SELECT * FROM v_waiting_list 
             WHERE DATE(created_at) = ? 
             AND status NOT IN ('completed', 'cancelled')
             ORDER BY 
                CASE priority_level 
                    WHEN 'urgent' THEN 1 
                    WHEN 'vip' THEN 2 
                    WHEN 'normal' THEN 3 
                END ASC,
                turn_number ASC",
            [$today]
        );
        
        // الإحصائيات
        $stats = $this->getTodayStats();
        
        // الإعدادات
        $settings = $this->getSettings();
        
        $pageTitle = __('waiting_list');
        require VIEWS_PATH . 'waiting/index.php';
    }
    
    /**
     * شاشة العرض للمرضى
     */
    public function display(): void
    {
        $today = date('Y-m-d');
        
        // الدور الحالي (المستدعى)
        $currentCall = Database::fetchOne(
            "SELECT * FROM v_waiting_list 
             WHERE DATE(created_at) = ? AND status = 'called'
             ORDER BY called_at DESC LIMIT 1",
            [$today]
        );
        
        // قائمة الانتظار
        $waitingList = Database::fetchAll(
            "SELECT turn_number, status FROM v_waiting_list 
             WHERE DATE(created_at) = ? AND status IN ('waiting', 'called')
             ORDER BY turn_number ASC LIMIT 10",
            [$today]
        );
        
        $settings = $this->getSettings();
        
        require VIEWS_PATH . 'waiting/display.php';
    }
    
    /**
     * إضافة مريض لقائمة الانتظار
     */
    public function add(): void
    {
        AuthController::checkSession();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'طريقة غير مسموحة'], 405);
        }
        
        $patientId = (int)($_POST['patient_id'] ?? 0);
        $visitType = clean($_POST['visit_type'] ?? 'checkup');
        $notes = clean($_POST['notes'] ?? '');
        
        if (!$patientId) {
            jsonResponse(['error' => 'يرجى تحديد المريض'], 400);
        }
        
        // التحقق من وجود المريض
        $patient = Database::fetchOne("SELECT id, full_name FROM patients WHERE id = ?", [$patientId]);
        if (!$patient) {
            jsonResponse(['error' => 'المريض غير موجود'], 404);
        }
        
        // التحقق من أن المريض ليس في قائمة الانتظار بالفعل
        $exists = Database::fetchOne(
            "SELECT id FROM waiting_list 
             WHERE patient_id = ? AND DATE(created_at) = CURDATE() 
             AND status NOT IN ('completed', 'cancelled')",
            [$patientId]
        );
        
        if ($exists) {
            jsonResponse(['error' => 'المريض موجود بالفعل في قائمة الانتظار'], 400);
        }
        
        $priority = clean($_POST['priority'] ?? 'normal');
        
        // التحقق من الرقم المحجوز للمريض
        $turnNumber = $this->getTurnNumberForPatient($patientId);
        
        // الإضافة
        $id = Database::insert('waiting_list', [
            'patient_id' => $patientId,
            'turn_number' => $turnNumber,
            'visit_type' => $visitType,
            'priority_level' => $priority,
            'notes' => $notes,
            'created_by' => $_SESSION['user_id']
        ]);
        
        // تحديث عداد اليوم إذا كان الرقم أكبر من العداد الحالي
        $settings = $this->getSettings();
        if ($turnNumber > (int)($settings['daily_counter'] ?? 0)) {
            $this->updateSetting('daily_counter', $turnNumber);
        }
        
        logAction('add_to_waiting', 'waiting_list', $id, null, [
            'patient_id' => $patientId,
            'turn_number' => $turnNumber
        ]);
        
        jsonResponse([
            'success' => true,
            'message' => 'تم إضافة المريض لقائمة الانتظار',
            'turn_number' => $turnNumber,
            'patient_name' => $patient['full_name']
        ]);
    }
    
    /**
     * استدعاء الدور التالي
     */
    public function callNext(): void
    {
        AuthController::checkSession();
        
        // التحقق من أن القائمة ليست متوقفة
        if ($this->isPaused()) {
            jsonResponse(['error' => 'قائمة الانتظار متوقفة مؤقتاً'], 400);
        }
        
        // الحصول على الدور التالي في الانتظار (حسب الأولوية)
        $next = Database::fetchOne(
            "SELECT * FROM v_waiting_list 
             WHERE DATE(created_at) = CURDATE() AND status = 'waiting'
             ORDER BY 
                CASE priority_level 
                    WHEN 'urgent' THEN 1 
                    WHEN 'vip' THEN 2 
                    WHEN 'normal' THEN 3 
                END ASC,
                turn_number ASC 
             LIMIT 1"
        );
        
        if (!$next) {
            jsonResponse(['error' => 'لا يوجد مرضى في قائمة الانتظار', 'empty' => true], 404);
        }
        
        // تحديث حالة المستدعى السابق إلى "في الانتظار" إذا لم يدخل
        Database::query(
            "UPDATE waiting_list SET status = 'waiting' 
             WHERE DATE(created_at) = CURDATE() AND status = 'called'"
        );
        
        // استدعاء الدور الجديد
        Database::update('waiting_list', [
            'status' => 'called',
            'called_at' => date('Y-m-d H:i:s'),
            'call_count' => $next['call_count'] + 1
        ], 'id = ?', [$next['id']]);
        
        // تسجيل النداء
        Database::insert('call_logs', [
            'waiting_id' => $next['id'],
            'turn_number' => $next['turn_number'],
            'called_by' => $_SESSION['user_id']
        ]);
        
        // تحديث الدور الحالي
        $this->updateSetting('current_turn', $next['turn_number']);
        
        logAction('call_patient', 'waiting_list', $next['id']);
        
        jsonResponse([
            'success' => true,
            'id' => $next['id'],
            'turn_number' => $next['turn_number'],
            'patient_name' => $next['patient_name'],
            'call_count' => $next['call_count'] + 1
        ]);
    }
    
    /**
     * إعادة استدعاء الدور الحالي
     */
    public function recall(): void
    {
        AuthController::checkSession();
        
        $current = Database::fetchOne(
             "SELECT * FROM v_waiting_list 
             WHERE DATE(created_at) = CURDATE() AND status = 'waiting'
             ORDER BY 
                CASE priority_level 
                    WHEN 'urgent' THEN 1 
                    WHEN 'vip' THEN 2 
                    WHEN 'normal' THEN 3 
                END ASC,
                turn_number ASC 
             LIMIT 1"
        );
        
        if (!$current) {
            jsonResponse(['error' => 'لا يوجد دور حالي للاستدعاء'], 404);
        }
        
        // تحديث عدد مرات النداء
        Database::update('waiting_list', [
            'call_count' => $current['call_count'] + 1,
            'called_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$current['id']]);
        
        // تسجيل النداء
        Database::insert('call_logs', [
            'waiting_id' => $current['id'],
            'turn_number' => $current['turn_number'],
            'called_by' => $_SESSION['user_id']
        ]);
        
        jsonResponse([
            'success' => true,
            'turn_number' => $current['turn_number'],
            'patient_name' => $current['patient_name'],
            'call_count' => $current['call_count'] + 1
        ]);
    }
    
    /**
     * دخول المريض
     */
    public function enter(int $id): void
    {
        AuthController::checkSession();
        
        $waiting = Database::fetchOne("SELECT * FROM waiting_list WHERE id = ?", [$id]);
        
        if (!$waiting) {
            jsonResponse(['error' => 'السجل غير موجود'], 404);
        }
        
        Database::update('waiting_list', [
            'status' => 'entered',
            'entered_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);
        
        logAction('patient_entered', 'waiting_list', $id);
        
        jsonResponse(['success' => true, 'message' => 'تم تسجيل دخول المريض']);
    }
    
    /**
     * إنهاء الكشف
     */
    public function complete(int $id): void
    {
        AuthController::checkSession();
        
        Database::update('waiting_list', [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);
        
        logAction('visit_completed', 'waiting_list', $id);
        
        jsonResponse(['success' => true, 'message' => 'تم إنهاء الكشف']);
    }
    
    /**
     * إلغاء الدور
     */
    public function cancel(int $id): void
    {
        AuthController::checkSession();
        
        Database::update('waiting_list', [
            'status' => 'cancelled'
        ], 'id = ?', [$id]);
        
        logAction('turn_cancelled', 'waiting_list', $id);
        
        jsonResponse(['success' => true, 'message' => 'تم إلغاء الدور']);
    }
    
    /**
     * إيقاف/استئناف قائمة الانتظار
     */
    public function togglePause(): void
    {
        AuthController::checkSession();
        AuthController::requireRole('doctor', 'admin');
        
        $isPaused = $this->isPaused();
        $this->updateSetting('is_paused', $isPaused ? '0' : '1');
        
        logAction($isPaused ? 'resume_waiting' : 'pause_waiting', 'waiting_settings', null);
        
        jsonResponse([
            'success' => true,
            'is_paused' => !$isPaused,
            'message' => $isPaused ? 'تم استئناف قائمة الانتظار' : 'تم إيقاف قائمة الانتظار مؤقتاً'
        ]);
    }
    
    /**
     * إعادة تعيين قائمة الانتظار
     */
    public function reset(): void
    {
        AuthController::checkSession();
        AuthController::requireRole('doctor', 'admin');
        
        // إنهاء جميع الأدوار المعلقة
        Database::query(
            "UPDATE waiting_list SET status = 'cancelled' 
             WHERE DATE(created_at) = CURDATE() AND status IN ('waiting', 'called', 'paused')"
        );
        
        $this->updateSetting('daily_counter', '0');
        $this->updateSetting('current_turn', '0');
        $this->updateSetting('is_paused', '0');
        
        logAction('reset_waiting', 'waiting_list', null);
        
        jsonResponse(['success' => true, 'message' => 'تم إعادة تعيين قائمة الانتظار']);
    }
    
    /**
     * الحصول على حالة قائمة الانتظار (للتحديث المباشر)
     */
    public function status(): void
    {
        $today = date('Y-m-d');
        
        $current = Database::fetchOne(
            "SELECT turn_number, patient_name FROM v_waiting_list 
             WHERE DATE(created_at) = ? AND status = 'called'
             ORDER BY called_at DESC LIMIT 1",
            [$today]
        );
        
        $stats = $this->getTodayStats();
        $settings = $this->getSettings();
        
        jsonResponse([
            'current_turn' => $current['turn_number'] ?? 0,
            'patient_name' => $current['patient_name'] ?? '',
            'waiting_count' => $stats['waiting'],
            'is_paused' => $settings['is_paused'] === '1'
        ]);
    }
    
    /**
     * البحث عن مريض لإضافته
     */
    public function searchPatient(): void
    {
        AuthController::checkSession();
        
        $query = clean($_GET['q'] ?? '');
        
        if (strlen($query) < 2) {
            jsonResponse(['patients' => []]);
        }
        
        $searchParam = "%{$query}%";
        $patients = Database::fetchAll(
            "SELECT p.id, p.full_name, p.electronic_number, p.phone
             FROM patients p
             WHERE p.is_active = 1 
             AND (p.full_name LIKE ? OR p.electronic_number LIKE ? OR p.phone LIKE ?)
             AND p.id NOT IN (
                 SELECT patient_id FROM waiting_list 
                 WHERE DATE(created_at) = CURDATE() 
                 AND status NOT IN ('completed', 'cancelled')
             )
             ORDER BY p.full_name LIMIT 10",
            [$searchParam, $searchParam, $searchParam]
        );
        
        jsonResponse(['patients' => $patients]);
    }
    
    // ==================== الدوال المساعدة ====================
    
    /**
     * الحصول على رقم الدور التالي
     */
    private function getNextTurnNumber(): int
    {
        $settings = $this->getSettings();
        $lastReset = $settings['last_reset_date'] ?? '';
        $today = date('Y-m-d');
        
        // إذا كان يوم جديد، نعيد العداد
        if ($lastReset !== $today) {
            $this->updateSetting('daily_counter', '0');
            $this->updateSetting('last_reset_date', $today);
            return 1;
        }
        
        return (int)$settings['daily_counter'] + 1;
    }
    
    /**
     * التحقق من إيقاف القائمة
     */
    private function isPaused(): bool
    {
        $setting = Database::fetchOne(
            "SELECT setting_value FROM waiting_settings WHERE setting_key = 'is_paused'"
        );
        return ($setting['setting_value'] ?? '0') === '1';
    }
    
    /**
     * الحصول على الإعدادات
     */
    private function getSettings(): array
    {
        $rows = Database::fetchAll("SELECT setting_key, setting_value FROM waiting_settings");
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }
    
    /**
     * تحديث إعداد
     */
    private function updateSetting(string $key, string $value): void
    {
        Database::query(
            "INSERT INTO waiting_settings (setting_key, setting_value) 
             VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?",
            [$key, $value, $value]
        );
    }
    
    /**
     * إحصائيات اليوم
     */
    private function getTodayStats(): array
    {
        $today = date('Y-m-d');
        
        return [
            'total' => Database::count('waiting_list', 'DATE(created_at) = ?', [$today]),
            'waiting' => Database::count('waiting_list', 'DATE(created_at) = ? AND status = ?', [$today, 'waiting']),
            'called' => Database::count('waiting_list', 'DATE(created_at) = ? AND status = ?', [$today, 'called']),
            'entered' => Database::count('waiting_list', 'DATE(created_at) = ? AND status = ?', [$today, 'entered']),
            'completed' => Database::count('waiting_list', 'DATE(created_at) = ? AND status = ?', [$today, 'completed']),
            'cancelled' => Database::count('waiting_list', 'DATE(created_at) = ? AND status = ?', [$today, 'cancelled'])
        ];
    }
    
    /**
     * الحصول على رقم الدور للمريض (محجوز أو تالي)
     */
    private function getTurnNumberForPatient(int $patientId): int
    {
        // التحقق من وجود رقم محجوز للمريض
        $reserved = Database::fetchOne(
            "SELECT reserved_number FROM reserved_numbers 
             WHERE patient_id = ? AND is_active = 1",
            [$patientId]
        );
        
        if ($reserved) {
            $reservedNum = (int)$reserved['reserved_number'];
            
            // التحقق من أن الرقم غير مستخدم اليوم
            $usedToday = Database::fetchOne(
                "SELECT id FROM waiting_list 
                 WHERE turn_number = ? AND DATE(created_at) = CURDATE() 
                 AND status NOT IN ('completed', 'cancelled')",
                [$reservedNum]
            );
            
            if (!$usedToday) {
                return $reservedNum;
            }
        }
        
        // إرجاع الرقم التالي العادي
        return $this->getNextTurnNumber();
    }
    
    // ==================== إدارة الأرقام المحجوزة ====================
    
    /**
     * عرض الأرقام المحجوزة
     */
    public function reservedNumbers(): void
    {
        AuthController::checkSession();
        AuthController::requireRole('doctor', 'admin');
        
        $reservedNumbers = Database::fetchAll("SELECT * FROM v_reserved_numbers ORDER BY reserved_number");
        
        jsonResponse(['success' => true, 'data' => $reservedNumbers]);
    }
    
    /**
     * حجز رقم لمريض
     */
    public function reserveNumber(): void
    {
        AuthController::checkSession();
        AuthController::requireRole('doctor', 'admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'طريقة غير مسموحة'], 405);
        }
        
        $patientId = (int)($_POST['patient_id'] ?? 0);
        $number = (int)($_POST['number'] ?? 0);
        $notes = clean($_POST['notes'] ?? '');
        
        if (!$patientId || !$number) {
            jsonResponse(['error' => 'يرجى تحديد المريض والرقم'], 400);
        }
        
        if ($number < 1 || $number > 999) {
            jsonResponse(['error' => 'الرقم يجب أن يكون بين 1 و 999'], 400);
        }
        
        // التحقق من أن المريض ليس لديه رقم محجوز
        $existingPatient = Database::fetchOne(
            "SELECT id FROM reserved_numbers WHERE patient_id = ? AND is_active = 1",
            [$patientId]
        );
        
        if ($existingPatient) {
            jsonResponse(['error' => 'هذا المريض لديه رقم محجوز بالفعل'], 400);
        }
        
        // التحقق من أن الرقم غير محجوز
        $existingNumber = Database::fetchOne(
            "SELECT id FROM reserved_numbers WHERE reserved_number = ? AND is_active = 1",
            [$number]
        );
        
        if ($existingNumber) {
            jsonResponse(['error' => 'هذا الرقم محجوز لمريض آخر'], 400);
        }
        
        // الحجز
        $id = Database::insert('reserved_numbers', [
            'patient_id' => $patientId,
            'reserved_number' => $number,
            'notes' => $notes,
            'created_by' => $_SESSION['user_id']
        ]);
        
        $patient = Database::fetchOne("SELECT full_name FROM patients WHERE id = ?", [$patientId]);
        
        logAction('reserve_number', 'reserved_numbers', $id, null, [
            'patient_id' => $patientId,
            'number' => $number
        ]);
        
        jsonResponse([
            'success' => true,
            'message' => 'تم حجز الرقم ' . $number . ' للمريض ' . $patient['full_name']
        ]);
    }
    
    /**
     * إلغاء حجز رقم
     */
    public function cancelReservation(): void
    {
        AuthController::checkSession();
        AuthController::requireRole('doctor', 'admin');
        
        $id = (int)($_POST['id'] ?? 0);
        
        if (!$id) {
            jsonResponse(['error' => 'معرف غير صحيح'], 400);
        }
        
        Database::update('reserved_numbers', ['is_active' => 0], 'id = ?', [$id]);
        
        logAction('cancel_reservation', 'reserved_numbers', $id);
        
        jsonResponse(['success' => true, 'message' => 'تم إلغاء الحجز']);
    }
    
    /**
     * التحقق من الرقم المحجوز لمريض
     */
    public function checkReserved(): void
    {
        AuthController::checkSession();
        
        $patientId = (int)($_GET['patient_id'] ?? 0);
        
        $reserved = Database::fetchOne(
            "SELECT reserved_number FROM reserved_numbers 
             WHERE patient_id = ? AND is_active = 1",
            [$patientId]
        );
        
        jsonResponse([
            'has_reserved' => (bool)$reserved,
            'number' => $reserved['reserved_number'] ?? null
        ]);
    }
}

