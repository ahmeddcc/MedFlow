<?php
$pageTitle = 'سجل النشاطات';
ob_start();
?>

<div class="page-header">
    <h1>سجل نشاطات النظام</h1>
    <div class="actions">
        <span class="badge badge-info"><?= $total ?> سجل</span>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="<?= url('logs') ?>" method="GET" class="filter-form">
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>المستخدم</label>
                    <select name="user_id" class="form-control">
                        <option value="">الكل</option>
                        <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($u['id'] == ($_GET['user_id'] ?? '')) ? 'selected' : '' ?>>
                            <?= $u['full_name'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>الإجراء</label>
                    <select name="action" class="form-control">
                        <option value="">الكل</option>
                        <option value="login_success" <?= (($_GET['action'] ?? '') == 'login_success') ? 'selected' : '' ?>>تسجيل دخول</option>
                        <option value="logout" <?= (($_GET['action'] ?? '') == 'logout') ? 'selected' : '' ?>>تسجيل خروج</option>
                        <option value="create_patient" <?= (($_GET['action'] ?? '') == 'create_patient') ? 'selected' : '' ?>>إضافة مريض</option>
                        <option value="delete_patient" <?= (($_GET['action'] ?? '') == 'delete_patient') ? 'selected' : '' ?>>حذف مريض</option>
                        <option value="create_invoice" <?= (($_GET['action'] ?? '') == 'create_invoice') ? 'selected' : '' ?>>إنشاء فاتورة</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>التاريخ</label>
                    <input type="date" name="date" class="form-control" value="<?= $_GET['date'] ?? '' ?>">
                </div>
                <div class="form-group col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">تصفية</button>
                    <a href="<?= url('logs') ?>" class="btn btn-secondary ml-2">إلغاء</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>المستخدم</th>
                    <th>الإجراء</th>
                    <th>الجدول</th>
                    <th>رقم السجل</th>
                    <th>التفاصيل</th>
                    <th>التاريخ</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">لا توجد سجلات مطابقة</td>
                </tr>
                <?php else: ?>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm ml-2 bg-primary-soft text-primary">
                                <?= mb_substr($log['user_name'] ?? '?', 0, 1) ?>
                            </div>
                            <?= $log['user_name'] ?? 'النظام' ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-soft-<?= getActionColor($log['action']) ?>">
                            <?= getActionName($log['action']) ?>
                        </span>
                    </td>
                    <td><?= $log['table_name'] ?></td>
                    <td><?= $log['record_id'] ?></td>
                    <td>
                        <?php if ($log['details']): ?>
                        <div class="details-json" title="<?= htmlspecialchars($log['details']) ?>">
                            <?= mb_strimwidth(htmlspecialchars($log['details']), 0, 50, '...') ?>
                        </div>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                    </td>
                    <td dir="ltr" class="text-right"><?= date('Y-m-d H:i', strtotime($log['created_at'])) ?></td>
                    <td><?= $log['ip_address'] ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- الترحيل -->
    <?php if ($totalPages > 1): ?>
    <div class="card-footer d-flex justify-content-center">
        <nav>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= url('logs') ?>?page=<?= $i ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">
                        <?= $i ?>
                    </a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<style>
.details-json {
    font-family: monospace;
    font-size: 0.85em;
    color: var(--text-muted);
    cursor: help;
}
.badge-soft-login_success { background: #e0f2f1; color: #00695c; }
.badge-soft-logout { background: #eceff1; color: #455a64; }
.badge-soft-delete_patient { background: #ffebee; color: #c62828; }
.badge-soft-create_patient { background: #e3f2fd; color: #1565c0; }
</style>

<?php
function getActionName($action) {
    $map = [
        'login_success' => 'تسجيل دخول',
        'logout' => 'تسجيل خروج',
        'create_patient' => 'إضافة مريض',
        'update_patient' => 'تعديل مريض',
        'delete_patient' => 'حذف مريض',
        'create_invoice' => 'إنشاء فاتورة',
        // ... يمكن إضافة المزيد
    ];
    return $map[$action] ?? $action;
}

function getActionColor($action) {
    if (strpos($action, 'delete') !== false) return 'danger';
    if (strpos($action, 'create') !== false) return 'success';
    if (strpos($action, 'update') !== false) return 'warning';
    return 'secondary';
}

$content = ob_get_clean();
require VIEWS_PATH . 'layouts/main.php';
?>
