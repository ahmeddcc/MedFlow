<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة #<?= $invoice['invoice_number'] ?></title>
    <link rel="stylesheet" href="<?= asset('css/print.css') ?>">
</head>
<body onload="<?php if($printConfig['auto_print']) echo 'window.print()'; ?>">

    <?php if (($printConfig['template_format'] ?? 'thermal') === 'a4' || ($printConfig['template_format'] ?? 'thermal') === 'a5'): ?>
    
    <!-- A4 Format -->
    <div class="format-a4">
        <div class="header">
            <div class="clinic-details">
                <h1><?= $clinicName ?></h1>
                <div><?= $clinicAddress ?></div>
                <div><?= $clinicPhone ?></div>
            </div>
            <div class="clinic-logo">
                <!-- If logo exists -->
                <!-- <img src="<?= asset('images/logo.png') ?>" alt="Logo"> -->
            </div>
        </div>

        <div class="invoice-title">فاتورة ضريبية</div>

        <div class="invoice-info-grid">
            <div class="info-group">
                <label>بيانات الفاتورة</label>
                <div>رقم الفاتورة: #<?= $invoice['invoice_number'] ?></div>
                <div>التاريخ: <?= date('Y-m-d', strtotime($invoice['created_at'])) ?></div>
                <div>الحالة: <?= $invoice['status'] ?></div>
            </div>
            <div class="info-group">
                <label>بيانات المريض</label>
                <div>الاسم: <?= $invoice['full_name'] ?></div>
                <div>الرقم: <?= $invoice['electronic_number'] ?></div>
                <div>الهاتف: <?= $invoice['phone'] ?></div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>الخدمة</th>
                    <th width="100">الكمية</th>
                    <th width="150">السعر</th>
                    <th width="150">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= $item['description'] ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= number_format($item['price'], 2) ?></td>
                    <td><?= number_format($item['total'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals-section">
            <div class="total-row">
                <span>المجموع الفرعي</span>
                <span><?= number_format($invoice['subtotal'], 2) ?></span>
            </div>
            <div class="total-row">
                <span>الخصم</span>
                <span><?= number_format($invoice['discount'], 2) ?></span>
            </div>
            <div class="total-row grand-total">
                <span>الإجمالي النهائي</span>
                <span><?= number_format($invoice['total'], 2) ?> ج.م</span>
            </div>
            <div class="total-row">
                <span>المبلغ المدفوع</span>
                <span><?= number_format($invoice['paid'], 2) ?></span>
            </div>
            <div class="total-row">
                <span>المتبقي</span>
                <span><?= number_format($invoice['remaining'], 2) ?></span>
            </div>
        </div>

        <div style="margin-top: 50px; text-align: center; color: #7f8c8d; font-size: 14px;">
            <p>تمنياتنا لكم بالشفاء العاجل</p>
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
        <div style="text-align: center; font-weight: bold; margin: 5px 0;">فاتورة #<?= $invoice['invoice_number'] ?></div>
        <div class="divider">----------------</div>

        <div class="info-row">
            <span>التاريخ:</span>
            <span><?= date('Y-m-d', strtotime($invoice['created_at'])) ?></span>
        </div>
        <div class="info-row">
            <span>المريض:</span>
            <span><?= mb_strimwidth($invoice['full_name'], 0, 20, '...') ?></span>
        </div>

        <div class="divider">----------------</div>

        <div class="items-section">
            <?php foreach ($items as $item): ?>
            <div class="item-row">
                <span><?= mb_strimwidth($item['description'], 0, 15, '..') ?></span>
                <span>x<?= $item['quantity'] ?></span>
                <span><?= number_format($item['total'], 0) ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="divider">----------------</div>

        <div class="totals-section">
            <div class="total-row">
                <span>الإجمالي:</span>
                <span><?= number_format($invoice['total'], 2) ?></span>
            </div>
            <div class="total-row">
                <span>المدفوع:</span>
                <span><?= number_format($invoice['paid'], 2) ?></span>
            </div>
            <?php if($invoice['remaining'] > 0): ?>
            <div class="total-row">
                <span>المتبقي:</span>
                <span><?= number_format($invoice['remaining'], 2) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="receipt-footer">
            شكراً لزيارتكم
        </div>
    </div>

    <?php endif; ?>
</body>
</html>
