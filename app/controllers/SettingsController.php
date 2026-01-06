<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../config/Database.php';

class SettingsController extends Controller {

    public function index() {
        $this->checkPermission(['admin', 'doctor']); 
        
        $settings = [];
        try {
            $settings = $this->getAllSettings();
        } catch (Throwable $e) {
            setFlash('error', 'خطأ: ' . $e->getMessage());
            // Fallback to empty settings to prevent view crash
            $settings = [];
        }
        
        $data = [
            'settings' => $settings,
            'pageTitle' => 'إعدادات النظام'
        ];
        
        $this->view('settings/index', $data);
    }
    
    public function save() {
        $this->checkPermission(['admin', 'doctor']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('settings');
            return;
        }
        
        $type = $_POST['form_type'] ?? '';
        
        try {
            switch ($type) {
                case 'general':
                    $this->saveGeneralSettings();
                    redirect('settings#general');
                    break;
                case 'toggles':
                    $this->saveFeatureToggles();
                    redirect('settings#toggles');
                    break;
                case 'telegram':
                    $this->saveTelegramSettings();
                    redirect('settings#telegram');
                    break;
                case 'printers':
                    $this->savePrinterSettings();
                    redirect('settings#printers');
                    break;
                default:
                    setFlash('error', 'نوع غير معروف');
                    redirect('settings');
            }
        } catch (Throwable $e) {
            setFlash('error', 'خطأ: ' . $e->getMessage());
            redirect('settings');
        }
    }

    private function saveGeneralSettings() {
        $fields = ['clinic_name', 'clinic_phone', 'clinic_address', 'doctor_name'];
        
        if (!empty($_FILES['clinic_logo']['name'])) {
            $logoPath = $this->handleLogoUpload();
            if ($logoPath) {
                $this->updateSetting('clinic_logo', $logoPath);
            }
        }
        
        foreach ($fields as $field) {
            $this->updateSetting($field, clean($_POST[$field] ?? ''));
        }
        setFlash('success', 'تم حفظ البيانات العامة');
    }
    
    private function saveFeatureToggles() {
        $toggles = ['enable_debts', 'enable_patient_printing', 'enable_lab_pricing',
                    'enable_rad_pricing', 'enable_smart_scheduling', 'enable_idle_branding'];
        
        foreach ($toggles as $key) {
            $this->updateSetting($key, isset($_POST[$key]) ? '1' : '0');
        }
        setFlash('success', 'تم تحديث الميزات');
    }
    
    private function saveTelegramSettings() {
        $textFields = ['telegram_bot_token', 'telegram_chat_id',
                       'telegram_support_bot_token', 'telegram_support_chat_id'];
        
        foreach ($textFields as $field) {
            $this->updateSetting($field, trim($_POST[$field] ?? ''));
        }
        
        $this->updateSetting('telegram_enabled', isset($_POST['telegram_enabled']) ? '1' : '0');
        $this->updateSetting('telegram_support_enabled', isset($_POST['telegram_support_enabled']) ? '1' : '0');
        
        setFlash('success', 'تم حفظ إعدادات تيليجرام ✅');
    }

    private function savePrinterSettings() {
        if (!empty($_POST['printer_name'])) {
            Database::query(
                "INSERT INTO printers (name, type, location) VALUES (?, ?, ?)", 
                [clean($_POST['printer_name']), clean($_POST['printer_type']), clean($_POST['printer_location'])]
            );
            setFlash('success', 'تم إضافة الطابعة');
        }

        if (isset($_POST['routing'])) {
            foreach ($_POST['routing'] as $docType => $rules) {
                Database::query("DELETE FROM print_routing WHERE document_type = ?", [$docType]);
                
                if ((int)$rules['printer_id'] > 0) {
                    Database::query(
                        "INSERT INTO print_routing (document_type, printer_id, template_format, auto_print) VALUES (?, ?, ?, ?)",
                        [$docType, (int)$rules['printer_id'], clean($rules['format']), isset($rules['auto']) ? 1 : 0]
                    );
                }
            }
            setFlash('success', 'تم تحديث التوجيه');
        }
    }
    
    public function deletePrinter($id) {
        $this->checkPermission(['admin']);
        
        $id = (int)$id;
        if ($id > 0) {
            // حذف قواعد التوجيه المرتبطة أولاً
            Database::query("DELETE FROM print_routing WHERE printer_id = ?", [$id]);
            // حذف الطابعة
            Database::query("DELETE FROM printers WHERE id = ?", [$id]);
            setFlash('success', 'تم حذف الطابعة');
        }
        
        redirect('settings#printers');
    }

    public function testBot() {
        $this->checkPermission(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['ok'=>false], 400);
            return;
        }

        require_once __DIR__ . '/../services/TelegramService.php';
        $telegram = new TelegramService();
        $type = $_POST['type'] ?? 'ops';
        
        $clinicName = getSetting('clinic_name', 'عيادتي');
        $projectName = 'MedFlow';
        
        if ($type === 'ops') {
            $message = "✅ *اختبار الاتصال ناجح*\n\n🏥 العيادة: {$clinicName}\n📦 النظام: {$projectName}";
            $res = $telegram->sendOperationMessage($message, true);
        } else {
            $message = "🛡️ *اختبار الحماية ناجح*\n\n🏥 العيادة: {$clinicName}\n📦 النظام: {$projectName}";
            $res = $telegram->sendSupportMessage($message, true);
        }
        
        jsonResponse($res);
    }
    
    /**
     * حفظ إعدادات الترخيص
     */
    public function saveLicenseSettings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('settings');
            return;
        }
        
        require_once __DIR__ . '/../services/LicenseService.php';
        
        $key = clean($_POST['license_key'] ?? '');
        
        if (LicenseService::validateLicense($key)) {
            setSetting('license_key', $key);
            setSetting('license_status', 'active');
            setFlash('success', '✅ تم تفعيل الترخيص بنجاح');
        } else {
            setFlash('error', '❌ مفتاح الترخيص غير صحيح لهذا الجهاز');
        }
        
        redirect('settings#license');
    }

    private function getAllSettings() {
        $stmt = Database::query("SELECT setting_key, setting_value FROM settings");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        // Printers
        try {
            require_once __DIR__ . '/../services/PrintService.php';
            $printService = new PrintService();
            $settings['printers'] = $printService->getPrinters();
            $settings['routing'] = $printService->getRoutingRules();
            
            $routingMap = [];
            foreach ($settings['routing'] as $rule) {
                $routingMap[$rule['document_type']] = $rule;
            }
            $settings['routing_map'] = $routingMap;
        } catch (Throwable $e) {
            $settings['printers'] = [];
            $settings['routing'] = [];
            $settings['routing_map'] = [];
        }

        // Backups
        try {
            require_once __DIR__ . '/../services/BackupService.php';
            $backupService = new BackupService();
            $settings['backups'] = $backupService->listBackups();
        } catch (Throwable $e) {
            $settings['backups'] = [];
        }

        return $settings;
    }
    
    private function updateSetting($key, $value) {
        $exists = Database::fetchOne("SELECT id FROM settings WHERE setting_key = ?", [$key]);
        if ($exists) {
            Database::query("UPDATE settings SET setting_value = ? WHERE setting_key = ?", [$value, $key]);
        } else {
            Database::query("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)", [$key, $value]);
        }
    }
    
    private function handleLogoUpload() {
        $targetDir = "public/assets/images/";
        if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
        
        $fileName = "logo_" . time() . "_" . basename($_FILES["clinic_logo"]["name"]);
        $target = $targetDir . $fileName;
        
        if (move_uploaded_file($_FILES["clinic_logo"]["tmp_name"], $target)) {
            return "assets/images/" . $fileName;
        }
        return false;
    }
}
