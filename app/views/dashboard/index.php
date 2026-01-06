<?php
$pageTitle = __('dashboard');
ob_start();
?>

<div class="dashboard-content">
    <!-- التنبيهات الذكية -->
    <?php if (!empty($alerts)): ?>
    <div class="alerts-section mb-4">
        <?php foreach ($alerts as $alert): ?>
        <div class="alert alert-<?= $alert['type'] ?> d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-circle"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span><?= $alert['message'] ?></span>
            </div>
            <?php if(isset($alert['action'])): ?>
            <a href="<?= $alert['action'] ?>" class="btn btn-sm btn-light">اتخاذ إجراء</a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- الإحصائيات السريعة -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?= number_format($stats['total_patients']) ?></h3>
                <p class="stat-label">إجمالي المرضى</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, var(--success) 0%, #00C095 100%);">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?= number_format($stats['today_patients']) ?></h3>
                <p class="stat-label">مرضى اليوم</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, var(--warning) 0%, #FFA502 100%);">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?= number_format($stats['waiting_count']) ?></h3>
                <p class="stat-label">في الانتظار</p>
            </div>
        </div>
        
        <?php if (isset($stats['today_revenue'])): ?>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
            <div class="stat-content">
                <h3 class="stat-value"><?= number_format($stats['today_revenue']) ?> <small style="font-size:0.5em">ج.م</small></h3>
                <p class="stat-label">دخل اليوم</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- مؤشرات الأداء (Charts) -->
    <?php if (!empty($kpis)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ساعات الذروة (اليوم)</h3>
                </div>
                <div class="card-body">
                    <canvas id="peakHoursChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('peakHoursChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($kpis['peak_hours']['labels']) ?>,
                datasets: [{
                    label: 'عدد الزيارات',
                    data: <?= json_encode($kpis['peak_hours']['data']) ?>,
                    borderColor: '#4ecdc4',
                    backgroundColor: 'rgba(78, 205, 196, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    });
    </script>
    <?php endif; ?>
    
    <!-- آخر المرضى -->
    <div class="card mt-3">
        <div class="card-header">
            <h2 class="card-title">آخر المرضى المسجلين</h2>
            <a href="<?= url('patients') ?>" class="btn btn-secondary btn-sm">
                عرض الكل
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($recentPatients)): ?>
            <div class="text-center p-3" style="color: var(--text-muted);">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="48" height="48" style="opacity: 0.5; margin-bottom: 1rem;">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                </svg>
                <p>لا يوجد مرضى مسجلين بعد</p>
                <a href="<?= url('patients/create') ?>" class="btn btn-primary mt-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    إضافة مريض جديد
                </a>
            </div>
            <?php else: ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>المريض</th>
                            <th>الرقم الإلكتروني</th>
                            <th>الهاتف</th>
                            <th>تاريخ التسجيل</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentPatients as $patient): ?>
                        <tr>
                            <td>
                                <div class="patient-info">
                                    <div class="patient-avatar">
                                        <?= mb_substr($patient['full_name'], 0, 1, 'UTF-8') ?>
                                    </div>
                                    <div>
                                        <div class="patient-name"><?= $patient['full_name'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-primary"><?= $patient['electronic_number'] ?></span>
                            </td>
                            <td><?= $patient['phone'] ?: '-' ?></td>
                            <td><?= formatDateArabic($patient['created_at']) ?></td>
                            <td>
                                <a href="<?= url('patients/' . $patient['id']) ?>" class="btn btn-ghost btn-sm">
                                    عرض
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- الإجراءات السريعة -->
    <div class="card mt-3">
        <div class="card-header">
            <h2 class="card-title">الإجراءات السريعة</h2>
        </div>
        <div class="card-body">
            <div class="quick-actions">
                <!-- Call Next Action -->
                <form action="<?= url('waiting-list/call-next') ?>" method="POST" style="display:contents">
                    <button type="submit" class="quick-action-card" style="border:none; cursor:pointer; width:100%">
                        <div class="quick-action-icon" style="background: rgba(255, 107, 107, 0.1); color: var(--primary);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2"></path></svg>
                        </div>
                        <span>نداء التالي</span>
                    </button>
                </form>
                <a href="<?= url('patients/create') ?>" class="quick-action-card">
                    <div class="quick-action-icon" style="background: rgba(78, 205, 196, 0.1); color: var(--primary);">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <line x1="20" y1="8" x2="20" y2="14"></line>
                            <line x1="23" y1="11" x2="17" y2="11"></line>
                        </svg>
                    </div>
                    <span>مريض جديد</span>
                </a>
                
                <a href="<?= url('waiting-list') ?>" class="quick-action-card">
                    <div class="quick-action-icon" style="background: rgba(0, 217, 165, 0.1); color: var(--success);">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <span>قائمة الانتظار</span>
                </a>
                
                <a href="<?= url('patients') ?>" class="quick-action-card">
                    <div class="quick-action-icon" style="background: rgba(84, 160, 255, 0.1); color: var(--info);">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                    <span>البحث عن مريض</span>
                </a>
                
                <a href="<?= url('reports') ?>" class="quick-action-card">
                    <div class="quick-action-icon" style="background: rgba(255, 165, 2, 0.1); color: var(--warning);">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <span>التقارير</span>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: var(--spacing-lg);
}

.stat-card {
    display: flex;
    align-items: center;
    gap: var(--spacing-lg);
    padding: var(--spacing-xl);
    background: var(--bg-card);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
}

.stat-icon {
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-md);
    flex-shrink: 0;
}

.stat-icon svg {
    width: 28px;
    height: 28px;
    color: white;
}

.stat-value {
    font-size: var(--font-size-3xl);
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1;
    margin-bottom: var(--spacing-xs);
}

.stat-label {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: var(--spacing-md);
}

.quick-action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-xl);
    background: var(--bg-secondary);
    border-radius: var(--radius-md);
    text-decoration: none;
    color: var(--text-primary);
    transition: all var(--transition-normal);
}

.quick-action-card:hover {
    background: var(--bg-card);
    box-shadow: var(--shadow-md);
    transform: translateY(-4px);
}

.quick-action-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-md);
}

.quick-action-icon svg {
    width: 24px;
    height: 24px;
}

.quick-action-card span {
    font-weight: 600;
    font-size: var(--font-size-sm);
}
</style>

<?php
$content = ob_get_clean();
require VIEWS_PATH . 'layouts/main.php';
?>
