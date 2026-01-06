<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>روشتة</title>
    <link rel="stylesheet" href="<?= asset('css/print.css') ?>">
</head>
<body onload="<?php if($printConfig['auto_print']) echo 'window.print()'; ?>">

    <?php if (($printConfig['template_format'] ?? 'thermal') === 'a4' || ($printConfig['template_format'] ?? 'thermal') === 'a5'): ?>
    
    <!-- Formal Prescription (A4/A5) -->
    <div class="format-a4">
        <div class="header">
            <div class="clinic-details">
                <h1><?= $clinicName ?></h1>
                <?php if ($doctorName): ?>
                <h3>د. <?= $doctorName ?></h3>
                <?php endif; ?>
            </div>
            <div class="clinic-logo">
                <!-- Logo -->
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
            <div>
                <strong>الاسم:</strong> <?= $prescription['full_name'] ?>
            </div>
            <div>
                <strong>التاريخ:</strong> <?= date('Y-m-d', strtotime($prescription['created_at'])) ?>
            </div>
        </div>

        <div style="min-height: 400px; font-size: 18px; line-height: 1.8;">
            <div style="font-size: 24px; font-weight: bold; margin-bottom: 20px;">Rx</div>
            
            <ul style="list-style: none; padding: 0;">
            <?php foreach ($items as $index => $item): ?>
                <li style="margin-bottom: 20px;">
                    <div style="font-weight: bold;"><?= $item['medication_name'] ?></div>
                    <div style="padding-right: 20px; font-size: 16px; color: #555;">
                        <?= $item['dosage'] ?> 
                        <?= $item['frequency'] ? ' - ' . $item['frequency'] : '' ?>
                        <?= $item['duration'] ? ' - لمدة ' . $item['duration'] : '' ?>
                    </div>
                    <?php if ($item['instructions']): ?>
                    <div style="padding-right: 20px; font-size: 14px; font-style: italic;">
                        (<?= $item['instructions'] ?>)
                    </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>

        <?php if ($prescription['notes']): ?>
        <div style="border-top: 1px solid #eee; padding-top: 10px; margin-top: 20px;">
            <strong>ملاحظات:</strong> <?= $prescription['notes'] ?>
        </div>
        <?php endif; ?>

        <div style="margin-top: 50px; border-top: 2px solid #333; padding-top: 10px; display: flex; justify-content: space-between; font-size: 12px;">
            <div><?= $clinicAddress ?></div>
            <div><?= $clinicPhone ?></div>
        </div>
    </div>

    <?php else: ?>

    <!-- Thermal Format -->
    <div class="format-thermal">
        <div class="receipt-header">
            <div class="clinic-name"><?= $clinicName ?></div>
            <div><?= $clinicPhone ?></div>
        </div>
        
        <div class="divider">================</div>
        <div class="receipt-title">وصفة طبية</div>
        <div class="divider">----------------</div>

        <div class="info-row">
            <span>المريض:</span>
            <span><?= mb_strimwidth($prescription['full_name'], 0, 18, '..') ?></span>
        </div>

        <div class="medications-section" style="margin-top: 10px;">
            <?php foreach ($items as $index => $item): ?>
            <div style="margin-bottom: 5px; border-bottom: 1px dashed #ccc; padding-bottom: 2px;">
                <div style="font-weight: bold;"><?= $item['medication_name'] ?></div>
                <div style="font-size: 11px;">
                    <?= $item['dosage'] ?> - <?= $item['frequency'] ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="receipt-footer">
            نتمنى لكم الشفاء
        </div>
    </div>

    <?php endif; ?>
</body>
</html>
