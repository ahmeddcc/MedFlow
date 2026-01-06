<?php
/**
 * =====================================================
 * MedFlow - متحكم قائمة انتظار المناديب
 * =====================================================
 */

class RepWaitingController
{
    /**
     * عرض قائمة انتظار المناديب
     */
    public function index(): void
    {
        AuthController::checkSession();
        
        $today = date('Y-m-d');
        
        // جلب قائمة الانتظار لليوم
        $waitingList = Database::fetchAll(
            "SELECT * FROM v_rep_waiting_list 
             WHERE DATE(created_at) = ? 
             AND status NOT IN ('completed', 'cancelled')
             ORDER BY created_at ASC",
            [$today]
        );
        
        // الإحصائيات
        $stats = $this->getTodayStats();
        
        // الإعدادات
        $settings = $this->getSettings();
        
        // الشركات
        $companies = Database::fetchAll("SELECT * FROM companies WHERE is_active = 1 ORDER BY letter");
        
        $pageTitle = 'قائمة انتظار المناديب';
        require VIEWS_PATH . 'representatives/waiting.php';
    }
    
    /**
     * شاشة العرض للمناديب
     */
    public function display(): void
    {
        $today = date('Y-m-d');
        
        // الدور الحالي
        $currentCall = Database::fetchOne(
            "SELECT * FROM v_rep_waiting_list 
             WHERE DATE(created_at) = ? AND status = 'called'
             ORDER BY called_at DESC LIMIT 1",
            [$today]
        );
        
        // قائمة الانتظار
        $waitingList = Database::fetchAll(
            "SELECT full_turn, status, company_name FROM v_rep_waiting_list 
             WHERE DATE(created_at) = ? AND status IN ('waiting', 'called')
             ORDER BY created_at ASC LIMIT 10",
            [$today]
        );
        
        $settings = $this->getSettings();
        
        require VIEWS_PATH . 'representatives/display.php';
    }
    
    /**
     * إضافة مندوب لقائمة الانتظار
     */
    public function add(): void
    {
        AuthController::checkSession();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'طريقة غير مسموحة'], 405);
        }
        
        $companyId = (int)($_POST['company_id'] ?? 0);
        $repId = (int)($_POST['rep_id'] ?? 0) ?: null;
        $visitorName = clean($_POST['visitor_name'] ?? '');
        $notes = clean($_POST['notes'] ?? '');
        
        if (!$companyId) {
            jsonResponse(['error' => 'يرجى اختيار الشركة'], 400);
        }
        
        // الحصول على حرف الشركة
        $company = Database::fetchOne("SELECT letter FROM companies WHERE id = ?", [$companyId]);
        if (!$company) {
            jsonResponse(['error' => 'الشركة غير موجودة'], 404);
        }
        
        $letter = $company['letter'];
        
        // الحصول على الرقم التالي لهذا الحرف
        $nextNumber = $this->getNextNumberForLetter($letter);
        
        // الإضافة
        $id = Database::insert('rep_waiting_list', [
            'company_id' => $companyId,
            'rep_id' => $repId,
            'turn_letter' => $letter,
            'turn_number' => $nextNumber,
            'visitor_name' => $visitorName ?: null,
            'notes' => $notes,
            'created_by' => $_SESSION['user_id']
        ]);
        
        $fullTurn = $letter . $nextNumber;
        
        logAction('add_rep_to_waiting', 'rep_waiting_list', $id, null, [
            'full_turn' => $fullTurn
        ]);
        
        jsonResponse([
            'success' => true,
            'message' => 'تم إضافة المندوب لقائمة الانتظار',
            'turn' => $fullTurn
        ]);
    }
    
    /**
     * استدعاء الدور التالي
     */
    public function callNext(): void
    {
        AuthController::checkSession();
        
        if ($this->isPaused()) {
            jsonResponse(['error' => 'قائمة الانتظار متوقفة مؤقتاً'], 400);
        }
        
        // الحصول على الدور التالي
        $next = Database::fetchOne(
            "SELECT * FROM v_rep_waiting_list 
             WHERE DATE(created_at) = CURDATE() AND status = 'waiting'
             ORDER BY created_at ASC LIMIT 1"
        );
        
        if (!$next) {
            jsonResponse(['error' => 'لا يوجد مناديب في قائمة الانتظار', 'empty' => true], 404);
        }
        
        // تحديث المستدعى السابق
        Database::query(
            "UPDATE rep_waiting_list SET status = 'waiting' 
             WHERE DATE(created_at) = CURDATE() AND status = 'called'"
        );
        
        // استدعاء الدور الجديد
        Database::update('rep_waiting_list', [
            'status' => 'called',
            'called_at' => date('Y-m-d H:i:s'),
            'call_count' => $next['call_count'] + 1
        ], 'id = ?', [$next['id']]);
        
        // تسجيل النداء
        Database::insert('rep_call_logs', [
            'waiting_id' => $next['id'],
            'full_turn' => $next['full_turn'],
            'called_by' => $_SESSION['user_id']
        ]);
        
        // تحديث الدور الحالي
        $this->updateSetting('current_turn', $next['full_turn']);
        
        logAction('call_rep', 'rep_waiting_list', $next['id']);
        
        jsonResponse([
            'success' => true,
            'id' => $next['id'],
            'turn' => $next['full_turn'],
            'visitor_name' => $next['visitor_name'],
            'company_name' => $next['company_name'],
            'call_count' => $next['call_count'] + 1
        ]);
    }
    
    /**
     * إعادة النداء
     */
    public function recall(): void
    {
        AuthController::checkSession();
        
        $current = Database::fetchOne(
            "SELECT * FROM v_rep_waiting_list 
             WHERE DATE(created_at) = CURDATE() AND status = 'called'
             ORDER BY called_at DESC LIMIT 1"
        );
        
        if (!$current) {
            jsonResponse(['error' => 'لا يوجد دور حالي'], 404);
        }
        
        Database::update('rep_waiting_list', [
            'call_count' => $current['call_count'] + 1,
            'called_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$current['id']]);
        
        Database::insert('rep_call_logs', [
            'waiting_id' => $current['id'],
            'full_turn' => $current['full_turn'],
            'called_by' => $_SESSION['user_id']
        ]);
        
        jsonResponse([
            'success' => true,
            'turn' => $current['full_turn'],
            'call_count' => $current['call_count'] + 1
        ]);
    }
    
    /**
     * دخول المندوب
     */
    public function enter(int $id): void
    {
        AuthController::checkSession();
        
        Database::update('rep_waiting_list', [
            'status' => 'entered',
            'entered_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);
        
        logAction('rep_entered', 'rep_waiting_list', $id);
        
        jsonResponse(['success' => true]);
    }
    
    /**
     * إنهاء الزيارة
     */
    public function complete(int $id): void
    {
        AuthController::checkSession();
        
        Database::update('rep_waiting_list', [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);
        
        logAction('rep_visit_completed', 'rep_waiting_list', $id);
        
        jsonResponse(['success' => true]);
    }
    
    /**
     * إلغاء الدور
     */
    public function cancel(int $id): void
    {
        AuthController::checkSession();
        
        Database::update('rep_waiting_list', ['status' => 'cancelled'], 'id = ?', [$id]);
        
        logAction('rep_turn_cancelled', 'rep_waiting_list', $id);
        
        jsonResponse(['success' => true]);
    }
    
    /**
     * إيقاف/استئناف
     */
    public function togglePause(): void
    {
        AuthController::checkSession();
        AuthController::requireRole('doctor', 'admin');
        
        $isPaused = $this->isPaused();
        $this->updateSetting('is_paused', $isPaused ? '0' : '1');
        
        jsonResponse([
            'success' => true,
            'is_paused' => !$isPaused
        ]);
    }
    
    /**
     * حالة القائمة (للتحديث المباشر)
     */
    public function status(): void
    {
        $today = date('Y-m-d');
        
        $current = Database::fetchOne(
            "SELECT full_turn, visitor_name, company_name FROM v_rep_waiting_list 
             WHERE DATE(created_at) = ? AND status = 'called'
             ORDER BY called_at DESC LIMIT 1",
            [$today]
        );
        
        $stats = $this->getTodayStats();
        $settings = $this->getSettings();
        
        jsonResponse([
            'current_turn' => $current['full_turn'] ?? '',
            'visitor_name' => $current['visitor_name'] ?? '',
            'company_name' => $current['company_name'] ?? '',
            'waiting_count' => $stats['waiting'],
            'is_paused' => $settings['is_paused'] === '1'
        ]);
    }
    
    /**
     * البحث عن مندوب
     */
    public function searchRep(): void
    {
        AuthController::checkSession();
        
        $query = clean($_GET['q'] ?? '');
        $companyId = (int)($_GET['company_id'] ?? 0);
        
        if (strlen($query) < 2) {
            jsonResponse(['reps' => []]);
        }
        
        $params = ["%{$query}%"];
        $sql = "SELECT r.id, r.full_name, c.name AS company_name, c.letter 
                FROM representatives r
                JOIN companies c ON r.company_id = c.id
                WHERE r.is_active = 1 AND r.full_name LIKE ?";
        
        if ($companyId) {
            $sql .= " AND r.company_id = ?";
            $params[] = $companyId;
        }
        
        $sql .= " ORDER BY r.full_name LIMIT 10";
        
        $reps = Database::fetchAll($sql, $params);
        
        jsonResponse(['reps' => $reps]);
    }
    
    // ==================== الدوال المساعدة ====================
    
    private function getNextNumberForLetter(string $letter): int
    {
        $today = date('Y-m-d');
        
        $last = Database::fetchOne(
            "SELECT MAX(turn_number) as max_num FROM rep_waiting_list 
             WHERE turn_letter = ? AND DATE(created_at) = ?",
            [$letter, $today]
        );
        
        return ($last['max_num'] ?? 0) + 1;
    }
    
    private function isPaused(): bool
    {
        $setting = Database::fetchOne(
            "SELECT setting_value FROM rep_waiting_settings WHERE setting_key = 'is_paused'"
        );
        return ($setting['setting_value'] ?? '0') === '1';
    }
    
    private function getSettings(): array
    {
        $rows = Database::fetchAll("SELECT setting_key, setting_value FROM rep_waiting_settings");
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }
    
    private function updateSetting(string $key, string $value): void
    {
        Database::query(
            "INSERT INTO rep_waiting_settings (setting_key, setting_value) 
             VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?",
            [$key, $value, $value]
        );
    }
    
    private function getTodayStats(): array
    {
        $today = date('Y-m-d');
        
        return [
            'total' => Database::count('rep_waiting_list', 'DATE(created_at) = ?', [$today]),
            'waiting' => Database::count('rep_waiting_list', 'DATE(created_at) = ? AND status = ?', [$today, 'waiting']),
            'called' => Database::count('rep_waiting_list', 'DATE(created_at) = ? AND status = ?', [$today, 'called']),
            'entered' => Database::count('rep_waiting_list', 'DATE(created_at) = ? AND status = ?', [$today, 'entered']),
            'completed' => Database::count('rep_waiting_list', 'DATE(created_at) = ? AND status = ?', [$today, 'completed'])
        ];
    }
}
