<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفعيل النظام - MedFlow</title>
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/auth.css') ?>">
    <style>
        .lock-container {
            max-width: 600px;
            margin: 50px auto;
            text-align: center;
            padding: 40px;
        }
        .machine-id-box {
            background: #f8f9fa;
            padding: 15px;
            margin: 20px 0;
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
            font-family: monospace;
            font-size: 1.2rem;
            color: #2d3748;
            user-select: all;
            direction: ltr;
        }
        .lock-icon {
            font-size: 4rem;
            color: #e53e3e;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="lock-container card">
        <div class="lock-icon">🔒</div>
        <h1>النظام مقفل</h1>
        <p class="text-muted">
            عذراً، انتهت الفترة التجريبية أو أن الترخيص غير صالح.<br>
            يرجى إدخال مفتاح ترخيص صالح للمتابعة.
        </p>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-error">
                <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>

        <div class="machine-id-box">
            Machine ID: <?= $machineID ?>
        </div>
        <p class="text-sm text-muted">انسخ معرف الجهاز أعلاه وأرسله للمزود للحصول على مفتاح التفعيل.</p>

        <form method="POST" action="<?= url('license/activate') ?>" style="margin-top: 30px;">
            <div class="form-group">
                <input type="text" name="license_key" class="form-control" placeholder="أدخل مفتاح الترخيص هنا..." required style="text-align: center; font-family: monospace; letter-spacing: 2px;">
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">تفعيل النظام</button>
        </form>
        
        <div style="margin-top: 20px;">
            <a href="<?= url('auth/logout') ?>" class="text-muted">تسجيل الخروج</a>
        </div>
    </div>
</body>
</html>
