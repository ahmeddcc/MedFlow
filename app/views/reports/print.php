<?php
// إعدادات العيادة
$clinicName = getSetting('clinic_name', 'MedFlow Clinic');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير النظام - <?= date('Y-m-d') ?></title>
    <link rel="stylesheet" href="<?= assets('css/main.css') ?>">
    <style>
        body {
            background: white;
            padding: 20px;
        }
        .print-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .report-section {
            margin-bottom: 30px;
        }
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
            border-right: 4px solid var(--primary);
            padding-right: 10px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }
        .stat-box {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary);
        }
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: right;
        }
        th {
            background-color: #f9f9f9;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- أزرار التحكم -->
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" class="btn btn-primary">طباعة</button>
        <button onclick="window.close()" class="btn btn-secondary">إغلاق</button>
    </div>

    <!-- الترويسة -->
    <div class="print-header">
        <h1><?= $clinicName ?></h1>
        <p>تقرير إداري شامل</p>
        <p>الفترة: <?= $_GET['start_date'] ?? date('Y-m-d') ?> إلى <?= $_GET['end_date'] ?? date('Y-m-d') ?></p>
    </div>

    <!-- ملخص سريع -->
    <div class="report-section">
        <div class="report-title">ملخص الأداء</div>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-value"><?= $patientStats['visits'] ?></div>
                <div class="stat-label">إجمالي الزيارات</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?= $patientStats['new_patients'] ?></div>
                <div class="stat-label">مرضى جدد</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?= number_format($financeStats['total_paid']) ?></div>
                <div class="stat-label">الإيرادات المحصلة</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?= number_format($financeStats['pending_amount']) ?></div>
                <div class="stat-label">المستحقات (الآجل)</div>
            </div>
        </div>
    </div>

    <!-- التفاصيل المالية -->
    <div class="report-section">
        <div class="report-title">التفاصيل المالية</div>
        <table>
            <thead>
                <tr>
                    <th>البند</th>
                    <th>القيمة</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>إجمالي قيمة الفواتير</td>
                    <td><?= number_format($financeStats['total_invoices'], 2) ?> ج.م</td>
                </tr>
                <tr>
                    <td>المبلغ المدفوع</td>
                    <td><?= number_format($financeStats['total_paid'], 2) ?> ج.م</td>
                </tr>
                <tr>
                    <td>المبلغ المتبقي (آجل)</td>
                    <td><?= number_format($financeStats['pending_amount'], 2) ?> ج.م</td>
                </tr>
                <tr>
                    <td>عدد الفواتير</td>
                    <td><?= $financeStats['invoices_count'] ?> فاتورة</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- ساعات الذروة -->
    <?php if (!empty($peakHours)): ?>
    <div class="report-section">
        <div class="report-title">أوقات الذروة (الأكثر ازدحاماً)</div>
        <table>
            <thead>
                <tr>
                    <th>الساعة</th>
                    <th>عدد الزيارات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($peakHours as $ph): ?>
                <tr>
                    <td><?= date('h:00 A', strtotime($ph['hour'] . ':00')) ?> - <?= date('h:59 A', strtotime($ph['hour'] . ':00')) ?></td>
                    <td><?= $ph['count'] ?> زيارة</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div style="margin-top: 50px; text-align: center; color: #888; font-size: 12px;">
        تم استخراج هذا التقرير من نظام MedFlow في <?= date('Y-m-d H:i') ?>
    </div>

</body>
</html>
