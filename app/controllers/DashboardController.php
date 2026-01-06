<?php
/**
 * =====================================================
 * MedFlow - متحكم لوحة التحكم
 * =====================================================
 */

class DashboardController
{
    /**
     * عرض لوحة التحكم
     */

    
    /**
     * عرض لوحة التحكم
     */
    public function index(): void
    {
        AuthController::checkSession();
        $userRole = $_SESSION['user_role'] ?? 'user';
        
        // الصلاحيات (لاحقاً يمكن نقلها لنظام صلاحيات قاعدة البيانات)
        // حالياً نعتمد على الدور
        $canViewFinancial = in_array($userRole, ['admin', 'doctor']);
        $canViewOperational = true; // الكل يرى التشغيلي
        $canViewKPIs = in_array($userRole, ['doctor', 'admin']);
        $canViewAlerts = in_array($userRole, ['doctor', 'assistant', 'receptionist']);

        // 1. الإحصائيات (مخصصة حسب الدور)
        $stats = $this->getStats($canViewFinancial);

        // 2. التنبيهات الذكية (للطاقم الطبي والاستقبال)
        $alerts = [];
        if ($canViewAlerts) {
            $alerts = $this->getSmartAlerts();
        }

        // 3. مؤشرات الأداء (للطبيب والمدير)
        $kpis = [];
        if ($canViewKPIs) {
            $kpis = $this->getKPIs();
        }
        
        // 4. آخر المرضى (للكل)
        $recentPatients = Database::fetchAll(
            "SELECT id, full_name, electronic_number, phone, created_at 
             FROM patients 
             WHERE is_active = 1 
             ORDER BY created_at DESC 
             LIMIT 5"
        );
        
        $pageTitle = __('dashboard');
        require VIEWS_PATH . 'dashboard/index.php';
    }
    
    /**
     * الحصول على الإحصائيات (مالية + تشغيلية)
     */
    private function getStats(bool $includeFinancial): array
    {
        $today = date('Y-m-d');
        
        // أساسي: المرضى
        $stats = [
            'total_patients' => Database::count('patients', 'is_active = 1'),
            'today_patients' => Database::count('patients', "is_active = 1 AND DATE(created_at) = '$today'"),
            'waiting_count'  => Database::count('waiting_list', "DATE(created_at) = '$today' AND status IN ('waiting', 'called')"),
        ];

        // مالي: الدخل اليومي (فقط للمصرح لهم)
        if ($includeFinancial) {
            $revenue = Database::fetchOne(
                "SELECT COALESCE(SUM(amount), 0) as total 
                 FROM payments 
                 WHERE DATE(payment_date) = ?", 
                [$today]
            );
            $stats['today_revenue'] = $revenue['total'] ?? 0;
        }

        return $stats;
    }

    /**
     * التنبيهات العيادية الذكية
     */
    private function getSmartAlerts(): array
    {
        $alerts = [];
        $today = date('Y-m-d');

        // تنبيه 1: مرضى ينتظرون أكثر من 30 دقيقة
        // نحسب الفرق بين وقت الإنشاء والوقت الحالي للمنتظرين
        $longWait = Database::fetchAll(
            "SELECT count(*) as count 
             FROM waiting_list 
             WHERE status = 'waiting' 
             AND DATE(created_at) = ? 
             AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) > 30",
            [$today]
        );
        
        if (($longWait[0]['count'] ?? 0) > 0) {
            $alerts[] = [
                'type' => 'danger',
                'message' => "يوجد <strong>{$longWait[0]['count']}</strong> مرضى ينتظرون أكثر من 30 دقيقة!",
                'action' => url('waiting-list')
            ];
        }

        // تنبيه 2: متابعات اليوم
        // نفترض وجود حقل next_visit_date في جدول المرضى أو جدول زيارات
        // سنبحث في المرضى مباشرة إذا كان لديهم حقل، أو نتركها فارغة إذا لم ينفذ بعد
        // للإصدار الحالي: سنبحث عن المواعيد المحجوزة
        /*
        $followUps = Database::count('appointments', "visit_date = '$today' AND type = 'follow_up'");
        if ($followUps > 0) {
             $alerts[] = [
                'type' => 'warning',
                'message' => "لديك {$followUps} مواعيد متابعة اليوم.",
                'action' => url('appointments')
            ];
        }
        */

        return $alerts;
    }

    /**
     * مؤشرات الأداء (Charts Data)
     */
    private function getKPIs(): array
    {
        $today = date('Y-m-d');
        
        // ساعات الذروة (Peak Hours) لزيارات اليوم
        // نجمع عدد المرضى لكل ساعة بناءً على وقت الدخول entered_at
        $peakHoursData = Database::fetchAll(
            "SELECT HOUR(created_at) as hour_num, COUNT(*) as count 
             FROM waiting_list 
             WHERE DATE(created_at) = ? 
             GROUP BY HOUR(created_at) 
             ORDER BY hour_num ASC",
            [$today]
        );

        $hours = [];
        $counts = [];
        
        // تهيئة الساعات من 9 ص إلى 11 م (مثلاً)
        for ($i = 9; $i <= 23; $i++) {
            $hours[] = date("g A", strtotime("$i:00"));
            $found = false;
            foreach ($peakHoursData as $row) {
                if ($row['hour_num'] == $i) {
                    $counts[] = $row['count'];
                    $found = true;
                    break;
                }
            }
            if (!$found) $counts[] = 0;
        }

        return [
            'peak_hours' => [
                'labels' => $hours,
                'data' => $counts
            ]
        ];
    }

}
