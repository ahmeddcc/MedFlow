<?php
/**
 * متحكم الطباعة الذكية
 * Smart Print Controller
 * يستخدم PrintService لتحديد الطابعة والقالب المناسب
 */
class PrintController
{
    private $printService;
    
    public function __construct()
    {
        require_once __DIR__ . '/../services/PrintService.php';
        $this->printService = new PrintService();
    }
    
    /**
     * طباعة إيصال كشف
     */
    public function receipt(): void
    {
        AuthController::checkSession();
        
        $waitingId = (int)($_GET['id'] ?? 0);
        
        $record = Database::fetchOne(
            "SELECT w.*, p.full_name, p.electronic_number, p.phone, p.date_of_birth
             FROM waiting_list w
             JOIN patients p ON w.patient_id = p.id
             WHERE w.id = ?",
            [$waitingId]
        );
        
        if (!$record) {
            die('السجل غير موجود');
        }
        
        // بيانات العيادة
        $clinicName = getSetting('clinic_name', 'MedFlow Clinic');
        $clinicPhone = getSetting('clinic_phone', '');
        $clinicAddress = getSetting('clinic_address', '');
        
        // إعدادات الطباعة الذكية
        $printConfig = $this->printService->getPrintConfig('receipt');
        
        require VIEWS_PATH . 'print/receipt.php';
    }
    
    /**
     * طباعة فاتورة
     */
    public function invoice(): void
    {
        AuthController::checkSession();
        
        $invoiceId = (int)($_GET['id'] ?? 0);
        
        $invoice = Database::fetchOne(
            "SELECT i.*, p.full_name, p.electronic_number, p.phone
             FROM invoices i
             JOIN patients p ON i.patient_id = p.id
             WHERE i.id = ?",
            [$invoiceId]
        );
        
        if (!$invoice) {
            die('الفاتورة غير موجودة');
        }
        
        $items = Database::fetchAll(
            "SELECT * FROM invoice_items WHERE invoice_id = ?",
            [$invoiceId]
        );
        
        $payments = Database::fetchAll(
            "SELECT * FROM payments WHERE invoice_id = ?",
            [$invoiceId]
        );
        
        // بيانات العيادة
        $clinicName = getSetting('clinic_name', 'MedFlow Clinic');
        $clinicPhone = getSetting('clinic_phone', '');
        $clinicAddress = getSetting('clinic_address', '');
        
        // إعدادات الطباعة الذكية
        $printConfig = $this->printService->getPrintConfig('invoice');
        
        require VIEWS_PATH . 'print/invoice.php';
    }
    
    /**
     * طباعة وصفة طبية
     */
    public function prescription(): void
    {
        AuthController::checkSession();
        
        $rxId = (int)($_GET['id'] ?? 0);
        
        $prescription = Database::fetchOne(
            "SELECT pr.*, p.full_name, p.electronic_number, p.date_of_birth, p.phone
             FROM prescriptions pr
             JOIN patients p ON pr.patient_id = p.id
             WHERE pr.id = ?",
            [$rxId]
        );
        
        if (!$prescription) {
            die('الوصفة غير موجودة');
        }
        
        $items = Database::fetchAll(
            "SELECT * FROM prescription_items WHERE prescription_id = ?",
            [$rxId]
        );
        
        // بيانات العيادة
        $clinicName = getSetting('clinic_name', 'MedFlow Clinic');
        $doctorName = getSetting('doctor_name', '');
        $clinicPhone = getSetting('clinic_phone', '');
        
        // إعدادات الطباعة الذكية
        $printConfig = $this->printService->getPrintConfig('prescription');
        
        require VIEWS_PATH . 'print/prescription.php';
    }
    
    /**
     * طباعة نتيجة تحليل
     */
    public function labResult(): void
    {
        AuthController::checkSession();
        
        $orderId = (int)($_GET['id'] ?? 0);
        
        $order = Database::fetchOne(
            "SELECT o.*, p.full_name, p.electronic_number, p.date_of_birth, t.name AS test_name, t.normal_range, t.unit
             FROM lab_orders o
             JOIN patients p ON o.patient_id = p.id
             JOIN lab_tests t ON o.lab_test_id = t.id
             WHERE o.id = ?",
            [$orderId]
        );
        
        if (!$order) {
            die('الطلب غير موجود');
        }
        
        $clinicName = getSetting('clinic_name', 'MedFlow Clinic');
        
        // إعدادات الطباعة الذكية
        $printConfig = $this->printService->getPrintConfig('lab_result');
        
        require VIEWS_PATH . 'print/lab_result.php';
    }
}
