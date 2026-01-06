<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إيصال كشف</title>
    <link rel="stylesheet" href="<?= asset('css/print.css') ?>">
</head>
<body onload="window.print()">
    <div class="receipt">
        <!-- رأس الإيصال -->
        <div class="receipt-header">
            <div class="clinic-name"><?= $clinicName ?></div>
            <?php if ($clinicPhone): ?>
            <div class="clinic-info"><?= $clinicPhone ?></div>
            <?php endif; ?>
            <?php if ($clinicAddress): ?>
            <div class="clinic-info"><?= $clinicAddress ?></div>
            <?php endif; ?>
        </div>
        
        <div class="divider">================================</div>
        
        <!-- بيانات الإيصال -->
        <div class="receipt-title">إيصال كشف</div>
        
        <div class="receipt-info">
            <div class="info-row">
                <span>التاريخ:</span>
                <span><?= date('Y-m-d', strtotime($record['created_at'])) ?></span>
            </div>
            <div class="info-row">
                <span>الوقت:</span>
                <span><?= date('h:i A', strtotime($record['created_at'])) ?></span>
            </div>
            <div class="info-row">
                <span>رقم الدور:</span>
                <span class="turn-number"><?= $record['turn_number'] ?></span>
            </div>
        </div>
        
        <div class="divider">--------------------------------</div>
        
        <!-- بيانات المريض -->
        <div class="patient-info">
            <div class="info-row">
                <span>الاسم:</span>
                <span><?= $record['full_name'] ?></span>
            </div>
            <div class="info-row">
                <span>الرقم:</span>
                <span><?= $record['electronic_number'] ?></span>
            </div>
            <?php if ($record['phone']): ?>
            <div class="info-row">
                <span>الهاتف:</span>
                <span><?= $record['phone'] ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="divider">--------------------------------</div>
        
        <!-- نوع الزيارة -->
        <div class="visit-type">
            <?php
            $visitTypes = [
                'first_visit' => 'كشف أول',
                'follow_up' => 'متابعة',
                'consultation' => 'استشارة'
            ];
            echo $visitTypes[$record['visit_type']] ?? $record['visit_type'];
            ?>
        </div>
        
        <div class="divider">================================</div>
        
        <!-- التذييل -->
        <div class="receipt-footer">
            <div>شكراً لزيارتكم</div>
            <div class="footer-note">يرجى الاحتفاظ بهذا الإيصال</div>
        </div>
    </div>
</body>
</html>
