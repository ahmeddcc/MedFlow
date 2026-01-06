<?php
/**
 * متحكم الفواتير
 * Invoice Controller
 */
class InvoiceController
{
    /**
     * عرض قائمة الفواتير
     */
    public function index(): void
    {
        AuthController::checkSession();
        
        $filter = clean($_GET['filter'] ?? 'all');
        $date = clean($_GET['date'] ?? date('Y-m-d'));
        
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
            $date = date('Y-m-d');
        }

        $sql = "SELECT i.*, p.full_name AS patient_name, p.electronic_number
                FROM invoices i
                JOIN patients p ON i.patient_id = p.id
                WHERE DATE(i.created_at) = ?";
        $params = [$date];
        
        if ($filter !== 'all') {
            $sql .= " AND i.status = ?";
            $params[] = $filter;
        }
        
        $sql .= " ORDER BY i.created_at DESC";
        
        $invoices = Database::fetchAll($sql, $params);
        
        // إحصائيات اليوم
        $stats = $this->getDayStats($date);
        
        // الخدمات
        $services = Database::fetchAll("SELECT * FROM services WHERE is_active = 1 ORDER BY name");
        
        require VIEWS_PATH . 'invoices/index.php';
    }
    
    /**
     * إنشاء فاتورة جديدة
     */
    public function create(): void
    {
        AuthController::checkSession();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'طريقة غير مسموحة'], 405);
        }
        
        $patientId = (int)($_POST['patient_id'] ?? 0);
        $items = json_decode($_POST['items'] ?? '[]', true);
        $discount = (float)($_POST['discount'] ?? 0);
        $discountType = clean($_POST['discount_type'] ?? 'fixed');
        $notes = clean($_POST['notes'] ?? '');
        
        if (!$patientId) {
            jsonResponse(['error' => 'يرجى تحديد المريض'], 400);
        }
        
        if (empty($items)) {
            jsonResponse(['error' => 'يرجى إضافة خدمة واحدة على الأقل'], 400);
        }
        
        // حساب الإجمالي
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['quantity'] * $item['price'];
        }
        
        // حساب الخصم
        $discountAmount = $discountType === 'percent' ? ($subtotal * $discount / 100) : $discount;
        $total = max(0, $subtotal - $discountAmount);
        
        // إنشاء رقم الفاتورة
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // إنشاء الفاتورة
        $invoiceId = Database::insert('invoices', [
            'invoice_number' => $invoiceNumber,
            'patient_id' => $patientId,
            'subtotal' => $subtotal,
            'discount' => $discountAmount,
            'discount_type' => $discountType,
            'total' => $total,
            'remaining' => $total,
            'notes' => $notes,
            'created_by' => $_SESSION['user_id']
        ]);
        
        // إضافة البنود
        // إضافة البنود
        foreach ($items as $item) {
            $serviceId = isset($item['service_id']) ? (int)$item['service_id'] : null;
            $description = clean($item['description'] ?? '');
            $quantity = (float)($item['quantity'] ?? 1);
            $price = (float)($item['price'] ?? 0);
            $lineTotal = $quantity * $price;

            Database::insert('invoice_items', [
                'invoice_id' => $invoiceId,
                'service_id' => $serviceId ?: null,
                'description' => $description,
                'quantity' => $quantity,
                'unit_price' => $price,
                'total' => $lineTotal
            ]);
        }
        
        logAction('create_invoice', 'invoices', $invoiceId, null, [
            'patient_id' => $patientId,
            'total' => $total
        ]);
        
        jsonResponse([
            'success' => true,
            'message' => 'تم إنشاء الفاتورة بنجاح',
            'invoice_id' => $invoiceId,
            'invoice_number' => $invoiceNumber
        ]);
    }
    
    /**
     * عرض تفاصيل الفاتورة
     */
    public function show(): void
    {
        AuthController::checkSession();
        
        $id = (int)($_GET['id'] ?? 0);
        
        $invoice = Database::fetchOne(
            "SELECT i.*, p.full_name AS patient_name, p.electronic_number, p.phone AS patient_phone
             FROM invoices i
             JOIN patients p ON i.patient_id = p.id
             WHERE i.id = ?",
            [$id]
        );
        
        if (!$invoice) {
            jsonResponse(['error' => 'الفاتورة غير موجودة'], 404);
        }
        
        $items = Database::fetchAll(
            "SELECT ii.*, s.name AS service_name
             FROM invoice_items ii
             LEFT JOIN services s ON ii.service_id = s.id
             WHERE ii.invoice_id = ?",
            [$id]
        );
        
        $payments = Database::fetchAll(
            "SELECT * FROM payments WHERE invoice_id = ? ORDER BY payment_date DESC",
            [$id]
        );
        
        jsonResponse([
            'success' => true,
            'invoice' => $invoice,
            'items' => $items,
            'payments' => $payments
        ]);
    }
    
    /**
     * تسجيل دفعة
     */
    public function addPayment(): void
    {
        AuthController::checkSession();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'طريقة غير مسموحة'], 405);
        }
        
        $invoiceId = (int)($_POST['invoice_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $method = clean($_POST['method'] ?? 'cash');
        $reference = clean($_POST['reference'] ?? '');
        $notes = clean($_POST['notes'] ?? '');
        
        if (!$invoiceId || $amount <= 0) {
            jsonResponse(['error' => 'بيانات غير صحيحة'], 400);
        }
        
        // التحقق من الفاتورة
        $invoice = Database::fetchOne("SELECT * FROM invoices WHERE id = ?", [$invoiceId]);
        
        if (!$invoice) {
            jsonResponse(['error' => 'الفاتورة غير موجودة'], 404);
        }
        
        if ($invoice['status'] === 'paid') {
            jsonResponse(['error' => 'الفاتورة مسددة بالكامل'], 400);
        }
        
        if ($amount > $invoice['remaining']) {
            $amount = $invoice['remaining'];
        }
        
        // تسجيل الدفعة
        Database::insert('payments', [
            'invoice_id' => $invoiceId,
            'amount' => $amount,
            'payment_method' => $method,
            'payment_date' => date('Y-m-d'),
            'reference' => $reference,
            'notes' => $notes,
            'created_by' => $_SESSION['user_id']
        ]);
        
        // تحديث الفاتورة
        $newPaid = $invoice['paid'] + $amount;
        $newRemaining = $invoice['total'] - $newPaid;
        $newStatus = $newRemaining <= 0 ? 'paid' : 'partial';
        
        Database::update('invoices', [
            'paid' => $newPaid,
            'remaining' => max(0, $newRemaining),
            'status' => $newStatus
        ], 'id = ?', [$invoiceId]);
        
        logAction('add_payment', 'payments', $invoiceId, null, ['amount' => $amount]);
        
        jsonResponse([
            'success' => true,
            'message' => 'تم تسجيل الدفعة بنجاح',
            'new_status' => $newStatus
        ]);
    }
    
    /**
     * إلغاء الفاتورة
     */
    public function cancel(): void
    {
        AuthController::checkSession();
        AuthController::requireRole('doctor', 'admin');
        
        $id = (int)($_POST['id'] ?? 0);
        
        $invoice = Database::fetchOne("SELECT * FROM invoices WHERE id = ?", [$id]);
        
        if (!$invoice) {
            jsonResponse(['error' => 'الفاتورة غير موجودة'], 404);
        }
        
        if ($invoice['paid'] > 0) {
            jsonResponse(['error' => 'لا يمكن إلغاء فاتورة بها مدفوعات'], 400);
        }
        
        Database::update('invoices', ['status' => 'cancelled'], 'id = ?', [$id]);
        
        logAction('cancel_invoice', 'invoices', $id);
        
        jsonResponse(['success' => true, 'message' => 'تم إلغاء الفاتورة']);
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
     * الخدمات المتاحة
     */
    public function services(): void
    {
        AuthController::checkSession();
        
        $services = Database::fetchAll("SELECT * FROM services WHERE is_active = 1 ORDER BY name");
        
        jsonResponse(['services' => $services]);
    }
    
    /**
     * إحصائيات اليوم
     */
    private function getDayStats(string $date): array
    {
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
            $date = date('Y-m-d');
        }

        return [
            'total_invoices' => Database::count('invoices', 'DATE(created_at) = ?', [$date]),
            'total_amount' => Database::fetchOne(
                "SELECT COALESCE(SUM(total), 0) AS total FROM invoices WHERE DATE(created_at) = ? AND status != 'cancelled'",
                [$date]
            )['total'] ?? 0,
            'total_paid' => Database::fetchOne(
                "SELECT COALESCE(SUM(amount), 0) AS total FROM payments WHERE DATE(payment_date) = ?",
                [$date]
            )['total'] ?? 0,
            'pending' => Database::count('invoices', 'DATE(created_at) = ? AND status = ?', [$date, 'pending']),
        ];
    }
}
