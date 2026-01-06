<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>نتيجة تحليل</title>
    <link rel="stylesheet" href="<?= asset('css/print.css') ?>">
</head>
<body onload="window.print()">
    <div class="receipt lab-receipt">
        <!-- رأس التقرير -->
        <div class="receipt-header">
            <div class="clinic-name"><?= $clinicName ?></div>
            <div class="clinic-info">معمل التحاليل</div>
        </div>
        
        <div class="divider">================================</div>
        
        <div class="receipt-title">نتيجة تحليل</div>
        <div class="order-number"><?= $order['order_number'] ?></div>
        
        <div class="receipt-info">
            <div class="info-row">
                <span>التاريخ:</span>
                <span><?= date('Y-m-d', strtotime($order['result_date'] ?? $order['created_at'])) ?></span>
            </div>
            <div class="info-row">
                <span>المريض:</span>
                <span><?= $order['full_name'] ?></span>
            </div>
            <div class="info-row">
                <span>الرقم:</span>
                <span><?= $order['electronic_number'] ?></span>
            </div>
        </div>
        
        <div class="divider">--------------------------------</div>
        
        <!-- النتيجة -->
        <div class="result-section">
            <div class="test-name"><?= $order['test_name'] ?></div>
            
            <div class="result-box">
                <div class="result-label">النتيجة:</div>
                <div class="result-value <?= $order['result_status'] ?>"><?= $order['result_value'] ?> <?= $order['unit'] ?></div>
            </div>
            
            <?php if ($order['normal_range']): ?>
            <div class="normal-range">
                المعدل الطبيعي: <?= $order['normal_range'] ?> <?= $order['unit'] ?>
            </div>
            <?php endif; ?>
            
            <?php if ($order['result']): ?>
            <div class="result-details">
                <?= nl2br($order['result']) ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="divider">================================</div>
        
        <div class="receipt-footer">
            <div>هذه النتيجة للاستخدام الطبي فقط</div>
        </div>
    </div>
</body>
</html>
