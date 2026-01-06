<?php
$pageTitle = 'حول النظام';
ob_start();

// جلب معلومات النظام
$appVersion = getSetting('app_version', '1.0.0');
$clinicName = getSetting('clinic_name', 'MedFlow Clinic');
$developerName = getSetting('developer_name', 'MedFlow Team');
$developerEmail = getSetting('developer_email', '');
$developerPhone = getSetting('developer_phone', '');
$licenseStatus = getSetting('license_status', 'trial');
$licenseExpiry = getSetting('license_expiry', '');

// إحصائيات النظام
$totalPatients = Database::count('patients');
$totalUsers = Database::count('users');
$totalInvoices = Database::count('invoices');
?>

<div class="about-page">
    <!-- شعار ومعلومات النظام -->
    <div class="about-header">
        <div class="app-logo">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="64" height="64">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
            </svg>
        </div>
        <h1 class="app-name">MedFlow</h1>
        <p class="app-description">نظام إدارة العيادات الطبية</p>
        <div class="app-version">الإصدار <?= $appVersion ?></div>
    </div>
    
    <!-- معلومات العيادة -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">معلومات العيادة</h3>
        </div>
        <div class="card-body">
            <div class="info-row">
                <span class="info-label">اسم العيادة</span>
                <span class="info-value"><?= $clinicName ?></span>
            </div>
        </div>
    </div>
    
    <!-- الترخيص -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">الترخيص</h3>
        </div>
        <div class="card-body">
            <div class="info-row">
                <span class="info-label">الحالة</span>
                <span class="info-value">
                    <?php if ($licenseStatus === 'active'): ?>
                    <span class="badge badge-success">مفعّل</span>
                    <?php elseif ($licenseStatus === 'trial'): ?>
                    <span class="badge badge-warning">تجريبي</span>
                    <?php else: ?>
                    <span class="badge badge-danger">غير مفعّل</span>
                    <?php endif; ?>
                </span>
            </div>
            <?php if ($licenseExpiry): ?>
            <div class="info-row">
                <span class="info-label">تاريخ الانتهاء</span>
                <span class="info-value"><?= $licenseExpiry ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- إحصائيات النظام -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">إحصائيات النظام</h3>
        </div>
        <div class="card-body">
            <div class="stats-mini">
                <div class="stat-mini-item">
                    <span class="stat-mini-number"><?= number_format($totalPatients) ?></span>
                    <span class="stat-mini-label">مريض</span>
                </div>
                <div class="stat-mini-item">
                    <span class="stat-mini-number"><?= number_format($totalUsers) ?></span>
                    <span class="stat-mini-label">مستخدم</span>
                </div>
                <div class="stat-mini-item">
                    <span class="stat-mini-number"><?= number_format($totalInvoices) ?></span>
                    <span class="stat-mini-label">فاتورة</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- معلومات المطور -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">الدعم الفني</h3>
        </div>
        <div class="card-body">
            <div class="info-row">
                <span class="info-label">المطور</span>
                <span class="info-value"><?= $developerName ?></span>
            </div>
            <?php if ($developerEmail): ?>
            <div class="info-row">
                <span class="info-label">البريد</span>
                <span class="info-value"><a href="mailto:<?= $developerEmail ?>"><?= $developerEmail ?></a></span>
            </div>
            <?php endif; ?>
            <?php if ($developerPhone): ?>
            <div class="info-row">
                <span class="info-label">الهاتف</span>
                <span class="info-value"><a href="tel:<?= $developerPhone ?>"><?= $developerPhone ?></a></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- حقوق النشر -->
    <div class="copyright">
        <p>© <?= date('Y') ?> MedFlow. جميع الحقوق محفوظة.</p>
    </div>
</div>

<style>
.about-page {
    max-width: 600px;
    margin: 0 auto;
}

.about-header {
    text-align: center;
    padding: var(--spacing-2xl);
    background: var(--bg-card);
    border-radius: var(--radius-xl);
    margin-bottom: var(--spacing-xl);
    box-shadow: var(--shadow-card);
}

.app-logo {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto var(--spacing-lg);
    color: white;
}

.app-name {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: var(--spacing-xs);
}

.app-description {
    color: var(--text-muted);
    margin-bottom: var(--spacing-md);
}

.app-version {
    display: inline-block;
    padding: var(--spacing-xs) var(--spacing-md);
    background: var(--bg-secondary);
    border-radius: var(--radius-full);
    font-size: var(--font-size-sm);
    color: var(--primary);
    font-weight: 600;
}

.card {
    margin-bottom: var(--spacing-lg);
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: var(--spacing-sm) 0;
    border-bottom: 1px solid var(--border-light);
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    color: var(--text-muted);
}

.info-value {
    font-weight: 600;
    color: var(--text-primary);
}

.info-value a {
    color: var(--primary);
    text-decoration: none;
}

.badge {
    padding: 2px 10px;
    border-radius: var(--radius-full);
    font-size: var(--font-size-sm);
    font-weight: 500;
}

.badge-success { background: rgba(0, 217, 165, 0.15); color: var(--success); }
.badge-warning { background: rgba(255, 165, 2, 0.15); color: #CC8400; }
.badge-danger { background: rgba(255, 107, 107, 0.15); color: #E85555; }

.stats-mini {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--spacing-lg);
    text-align: center;
}

.stat-mini-number {
    display: block;
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--primary);
}

.stat-mini-label {
    font-size: var(--font-size-sm);
    color: var(--text-muted);
}

.copyright {
    text-align: center;
    padding: var(--spacing-xl);
    color: var(--text-muted);
    font-size: var(--font-size-sm);
}
</style>

<?php
$content = ob_get_clean();
require VIEWS_PATH . 'layouts/main.php';
?>
