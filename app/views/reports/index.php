<?php
$pageTitle = 'التقارير';
ob_start();
?>

<div class="reports-page">
    <!-- فلتر الفترة -->
    <div class="report-filter">
        <div class="filter-tabs">
            <button class="filter-tab <?= ($period ?? 'today') === 'today' ? 'active' : '' ?>" onclick="filterPeriod('today')">اليوم</button>
            <button class="filter-tab <?= ($period ?? '') === 'week' ? 'active' : '' ?>" onclick="filterPeriod('week')">الأسبوع</button>
            <button class="filter-tab <?= ($period ?? '') === 'month' ? 'active' : '' ?>" onclick="filterPeriod('month')">الشهر</button>
            <button class="filter-tab <?= ($period ?? '') === 'custom' ? 'active' : '' ?>" onclick="showCustomFilter()">مخصص</button>
        </div>
        <div class="custom-filter" id="customFilter" style="display: none;">
            <input type="date" class="form-control" id="startDate" value="<?= $startDate ?? date('Y-m-d') ?>">
            <span>إلى</span>
            <input type="date" class="form-control" id="endDate" value="<?= $endDate ?? date('Y-m-d') ?>">
            <button class="btn btn-primary btn-sm" onclick="applyCustomFilter()">تطبيق</button>
        </div>
    </div>
    
    <!-- أزرار التصدير -->
    <div class="report-actions" style="margin-top: -15px; margin-bottom: 20px; display: flex; justify-content: flex-end; gap: 10px;">
        <button class="btn btn-outline-primary btn-sm" onclick="exportCsv()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            تصدير Excel
        </button>
        <button class="btn btn-outline-secondary btn-sm" onclick="printReport()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            طباعة
        </button>
        <button class="btn btn-outline-info btn-sm" onclick="sendToTelegram()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <line x1="22" y1="2" x2="11" y2="13"></line>
                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
            إرسال لتيليجرام
        </button>
    </div>

    <!-- بطاقات الإحصائيات -->
    <div class="stats-grid">
        <!-- ... (Stat Cards) ... -->
        
        <!-- ساعات الذروة -->
        <div class="stat-card stat-peak">
            <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="28" height="28">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <?php if (!empty($peakHours)): ?>
                    <?php $peak = $peakHours[0]; ?>
                    <div class="stat-number"><?= date('h A', strtotime($peak['hour'] . ':00')) ?></div>
                    <div class="stat-label">ساعة الذروة (<?= $peak['count'] ?> زيارة)</div>
                <?php else: ?>
                    <div class="stat-number">-</div>
                    <div class="stat-label">لا توجد بيانات</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- التفاصيل -->
    <div class="report-details">
        <!-- ... (Other Details) ... -->
        
        <!-- تحليل الساعات -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">أنشط الساعات</h3>
            </div>
            <div class="card-body">
                <?php if (empty($peakHours)): ?>
                <p class="text-muted">لا توجد بيانات</p>
                <?php else: ?>
                <?php foreach ($peakHours as $ph): ?>
                <div class="detail-row">
                    <span class="detail-label"><?= date('h:00 A', strtotime($ph['hour'] . ':00')) ?> - <?= date('h:59 A', strtotime($ph['hour'] . ':00')) ?></span>
                    <span class="detail-value"><?= $ph['count'] ?> زيارة</span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
        <div class="stat-card stat-patients">
            <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="28" height="28">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= $patientStats['visits'] ?? 0 ?></div>
                <div class="stat-label">الزيارات</div>
            </div>
        </div>
        
        <div class="stat-card stat-new">
            <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="28" height="28">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= $patientStats['new_patients'] ?? 0 ?></div>
                <div class="stat-label">مرضى جدد</div>
            </div>
        </div>
        
        <div class="stat-card stat-revenue">
            <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="28" height="28">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= number_format($financeStats['total_paid'] ?? 0, 0) ?></div>
                <div class="stat-label">الإيرادات (ج.م)</div>
            </div>
        </div>
        
        <div class="stat-card stat-pending">
            <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="28" height="28">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= number_format($financeStats['pending_amount'] ?? 0, 0) ?></div>
                <div class="stat-label">مستحقات (ج.م)</div>
            </div>
        </div>
    </div>
    
    <!-- التفاصيل -->
    <div class="report-details">
        <!-- تفاصيل الزيارات -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">تفاصيل الزيارات</h3>
            </div>
            <div class="card-body">
                <div class="detail-row">
                    <span class="detail-label">كشف أول</span>
                    <span class="detail-value"><?= $patientStats['first_visits'] ?? 0 ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">متابعة</span>
                    <span class="detail-value"><?= $patientStats['follow_ups'] ?? 0 ?></span>
                </div>
                <div class="detail-row total">
                    <span class="detail-label">الإجمالي</span>
                    <span class="detail-value"><?= $patientStats['visits'] ?? 0 ?></span>
                </div>
            </div>
        </div>
        
        <!-- تفاصيل المالية -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">تفاصيل المالية</h3>
            </div>
            <div class="card-body">
                <div class="detail-row">
                    <span class="detail-label">عدد الفواتير</span>
                    <span class="detail-value"><?= $financeStats['invoices_count'] ?? 0 ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">إجمالي الفواتير</span>
                    <span class="detail-value"><?= number_format($financeStats['total_invoices'] ?? 0, 2) ?> ج.م</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">المحصّل</span>
                    <span class="detail-value success"><?= number_format($financeStats['total_paid'] ?? 0, 2) ?> ج.م</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">المستحقات</span>
                    <span class="detail-value warning"><?= number_format($financeStats['pending_amount'] ?? 0, 2) ?> ج.م</span>
                </div>
            </div>
        </div>
        
        <!-- أكثر الخدمات -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">أكثر الخدمات</h3>
            </div>
            <div class="card-body">
                <?php if (empty($serviceStats)): ?>
                <p class="text-muted">لا توجد بيانات</p>
                <?php else: ?>
                <?php foreach ($serviceStats as $service): ?>
                <div class="detail-row">
                    <span class="detail-label"><?= $service['description'] ?></span>
                    <span class="detail-value"><?= $service['count'] ?> (<?= number_format($service['total'], 0) ?> ج.م)</span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.reports-page {
    max-width: 1200px;
    margin: 0 auto;
}

.report-filter {
    background: var(--bg-card);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
    box-shadow: var(--shadow-card);
}

.filter-tabs {
    display: flex;
    gap: var(--spacing-sm);
    flex-wrap: wrap;
}

.filter-tab {
    padding: var(--spacing-sm) var(--spacing-lg);
    border: 1px solid var(--border-light);
    background: var(--bg-secondary);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.2s ease;
    font-weight: 500;
}

.filter-tab:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.filter-tab.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.custom-filter {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    margin-top: var(--spacing-md);
    padding-top: var(--spacing-md);
    border-top: 1px solid var(--border-light);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
}

.stat-card {
    background: var(--bg-card);
    border-radius: var(--radius-lg);
    padding: var(--spacing-xl);
    box-shadow: var(--shadow-card);
    display: flex;
    gap: var(--spacing-lg);
    align-items: center;
    border-top: 3px solid transparent;
}

.stat-card.stat-patients { border-top-color: var(--primary); }
.stat-card.stat-new { border-top-color: var(--success); }
.stat-card.stat-revenue { border-top-color: #ffc107; }
.stat-card.stat-pending { border-top-color: var(--warning); }

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-secondary);
    color: var(--primary);
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1;
}

.stat-label {
    font-size: var(--font-size-sm);
    color: var(--text-muted);
    margin-top: 4px;
}

.report-details {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--spacing-lg);
}

.card-header {
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--border-light);
}

.card-title {
    font-size: var(--font-size-md);
    font-weight: 700;
    margin: 0;
}

.card-body {
    padding: var(--spacing-lg);
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: var(--spacing-sm) 0;
    border-bottom: 1px solid var(--border-light);
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row.total {
    font-weight: 700;
    padding-top: var(--spacing-md);
    margin-top: var(--spacing-sm);
    border-top: 2px solid var(--border-light);
}

.detail-label {
    color: var(--text-muted);
}

.detail-value {
    font-weight: 600;
    color: var(--text-primary);
}

.detail-value.success { color: var(--success); }
.detail-value.warning { color: var(--warning); }

@media (max-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .report-details {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function filterPeriod(period) {
    window.location.href = '<?= url('reports') ?>?period=' + period;
}

function showCustomFilter() {
    const filter = document.getElementById('customFilter');
    filter.style.display = filter.style.display === 'none' ? 'flex' : 'none';
}

function applyCustomFilter() {
    const start = document.getElementById('startDate').value;
    const end = document.getElementById('endDate').value;
    window.location.href = `<?= url('reports') ?>?period=custom&start_date=${start}&end_date=${end}`;
}

function exportCsv() {
    const urlParams = new URLSearchParams(window.location.search);
    window.location.href = `<?= url('reports/export-csv') ?>?${urlParams.toString()}`;
}

function printReport() {
    const urlParams = new URLSearchParams(window.location.search);
    window.open(`<?= url('reports/print') ?>?${urlParams.toString()}`, '_blank');
}

function sendToTelegram() {
    if (!confirm('هل أنت متأكد من إرسال التقرير اليومي إلى تيليجرام؟')) return;
    
    fetch('<?= url('reports/send-telegram') ?>', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
        } else {
            alert('حدث خطأ: ' + (data.error || 'غير معروف'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('حدث خطأ في الاتصال');
    });
}
</script>

<?php
$content = ob_get_clean();
require VIEWS_PATH . 'layouts/main.php';
?>
