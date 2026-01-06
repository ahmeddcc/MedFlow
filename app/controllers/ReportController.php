<?php
/**
 * متحكم التقارير
 * Report Controller
 */
class ReportController
{
    /**
     * الصفحة الرئيسية للتقارير
     */
    public function index(): void
    {
        AuthController::checkSession();
        AuthController::requireRole('doctor', 'admin');
        
        $period = clean($_GET['period'] ?? 'today');
        $startDate = clean($_GET['start_date'] ?? date('Y-m-d'));
        $endDate = clean($_GET['end_date'] ?? date('Y-m-d'));
        
        // تحديد الفترة
        switch ($period) {
            case 'today':
                $startDate = $endDate = date('Y-m-d');
                break;
            case 'week':
                $startDate = date('Y-m-d', strtotime('-7 days'));
                $endDate = date('Y-m-d');
                break;
            case 'month':
                $startDate = date('Y-m-01');
                $endDate = date('Y-m-d');
                break;
        }
        
        // إحصائيات المرضى
        $patientStats = $this->getPatientStats($startDate, $endDate);
        
        // إحصائيات المالية
        $financeStats = $this->getFinanceStats($startDate, $endDate);
        
        // إحصائيات الخدمات
        $serviceStats = $this->getServiceStats($startDate, $endDate);
        
        // ساعات الذروة
        $peakHours = $this->getPeakHours($startDate, $endDate);
        
        require VIEWS_PATH . 'reports/index.php';
    }
    
    /**
     * إحصائيات المرضى
     */
    private function getPatientStats(string $start, string $end): array
    {
        return [
            'new_patients' => Database::count('patients', 
                'DATE(created_at) BETWEEN ? AND ?', [$start, $end]),
            'visits' => Database::count('waiting_list', 
                'DATE(created_at) BETWEEN ? AND ?', [$start, $end]),
            'first_visits' => Database::count('waiting_list', 
                "DATE(created_at) BETWEEN ? AND ? AND visit_type = 'first_visit'", [$start, $end]),
            'follow_ups' => Database::count('waiting_list', 
                "DATE(created_at) BETWEEN ? AND ? AND visit_type = 'follow_up'", [$start, $end]),
        ];
    }
    
    /**
     * إحصائيات مالية
     */
    private function getFinanceStats(string $start, string $end): array
    {
        $totalInvoices = Database::fetchOne(
            "SELECT COALESCE(SUM(total), 0) AS total FROM invoices 
             WHERE DATE(created_at) BETWEEN ? AND ? AND status != 'cancelled'",
            [$start, $end]
        )['total'] ?? 0;
        
        $totalPaid = Database::fetchOne(
            "SELECT COALESCE(SUM(amount), 0) AS total FROM payments 
             WHERE DATE(payment_date) BETWEEN ? AND ?",
            [$start, $end]
        )['total'] ?? 0;
        
        $pendingAmount = Database::fetchOne(
            "SELECT COALESCE(SUM(remaining), 0) AS total FROM invoices 
             WHERE status IN ('pending', 'partial')",
            []
        )['total'] ?? 0;
        
        return [
            'total_invoices' => $totalInvoices,
            'total_paid' => $totalPaid,
            'pending_amount' => $pendingAmount,
            'invoices_count' => Database::count('invoices', 
                "DATE(created_at) BETWEEN ? AND ? AND status != 'cancelled'", [$start, $end]),
        ];
    }
    
    /**
     * إحصائيات الخدمات
     */
    private function getServiceStats(string $start, string $end): array
    {
        return Database::fetchAll(
            "SELECT ii.description, COUNT(*) AS count, SUM(ii.total) AS total
             FROM invoice_items ii
             JOIN invoices i ON ii.invoice_id = i.id
             WHERE DATE(i.created_at) BETWEEN ? AND ? AND i.status != 'cancelled'
             GROUP BY ii.description
             ORDER BY count DESC
             LIMIT 10",
            [$start, $end]
        );
    }
    
    /**
     * حساب ساعات الذروة
     */
    private function getPeakHours(string $start, string $end): array
    {
        return Database::fetchAll(
            "SELECT HOUR(created_at) as hour, COUNT(*) as count 
             FROM waiting_list 
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY HOUR(created_at) 
             ORDER BY count DESC 
             LIMIT 5",
            [$start, $end]
        );
    }

    /**
     * تصدير CSV
     */
    public function exportCsv(): void
    {
        AuthController::checkSession();
        AuthController::requireRole('doctor', 'admin');
        
        $period = clean($_GET['period'] ?? 'today');
        $start = clean($_GET['start_date'] ?? date('Y-m-d'));
        $end = clean($_GET['end_date'] ?? date('Y-m-d'));
        
        if ($period == 'today') $start = $end = date('Y-m-d');
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=report_' . $start . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // BOM for Excel UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Header
        fputcsv($output, ['التاريخ', 'عدد الزيارات', 'إجمالي الفواتير', 'المحصل', 'المتبقي']);
        
        // Data Row
        $finance = $this->getFinanceStats($start, $end);
        $visits = Database::count('waiting_list', 'DATE(created_at) BETWEEN ? AND ?', [$start, $end]);
        
        fputcsv($output, [
            $start . ' to ' . $end,
            $visits,
            $finance['total_invoices'],
            $finance['total_paid'],
            $finance['pending_amount']
        ]);
        
        // التفاصيل المالية
        fputcsv($output, []); 
        fputcsv($output, ['تفاصيل الفواتير']);
        fputcsv($output, ['رقم الفاتورة', 'المريض', 'التاريخ', 'الإجمالي', 'المدفوع', 'المتبقي', 'الحالة']);
        
        $invoices = Database::fetchAll(
            "SELECT i.*, p.full_name 
             FROM invoices i 
             JOIN patients p ON i.patient_id = p.id
             WHERE DATE(i.created_at) BETWEEN ? AND ?
             ORDER BY i.created_at DESC",
            [$start, $end]
        );
        
        foreach ($invoices as $inv) {
            fputcsv($output, [
                $inv['invoice_number'],
                $inv['full_name'],
                $inv['created_at'],
                $inv['total'],
                $inv['paid'],
                $inv['remaining'],
                $inv['status']
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * عرض الطباعة
     */
    public function printView(): void
    {
        AuthController::checkSession();
        AuthController::requireRole('doctor', 'admin');
        
        $startDate = clean($_GET['start_date'] ?? date('Y-m-d'));
        $endDate = clean($_GET['end_date'] ?? date('Y-m-d'));
        
        $patientStats = $this->getPatientStats($startDate, $endDate);
        $financeStats = $this->getFinanceStats($startDate, $endDate);
        $peakHours = $this->getPeakHours($startDate, $endDate);
        
        require VIEWS_PATH . 'reports/print.php';
    }
    /**
     * إرسال التقرير اليومي لتيليجرام
     */
    public function sendDailyToTelegram(): void
    {
        AuthController::checkSession();
        AuthController::requireRole('doctor', 'admin');
        
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        
        $stats = $this->getFinanceStats($startDate, $endDate);
        $visits = Database::count('waiting_list', 'DATE(created_at) BETWEEN ? AND ?', [$startDate, $endDate]);
        
        $summary = [
            'visits' => $visits,
            'revenue' => $stats['total_paid'],
            'expenses' => 0, // يمكن ربطها لاحقاً بجدول المصروفات
            'net_income' => $stats['total_paid']
        ];
        
        // استخدام الخدمة
        if (class_exists('TelegramService')) {
            require_once APP_PATH . 'services/TelegramService.php';
            $telegram = new TelegramService();
            $telegram->sendDailySummary($summary);
            
            if (isAjax()) {
                jsonResponse(['success' => true, 'message' => 'تم إرسال التقرير لتيليجرام بنجاح']);
            }
            
            flash('success', 'تم إرسال التقرير لتيليجرام');
        } else {
            if (isAjax()) {
                jsonResponse(['error' => 'خدمة تيليجرام غير مفعلة'], 400);
            }
            flash('error', 'خدمة تيليجرام غير مفعلة');
        }
        
        redirect('reports');
    }
}
