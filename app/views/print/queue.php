<?php
$pageTitle = 'طابور الطباعة';
ob_start();
?>

<div class="page-header">
    <h1>🖨️ طابور الطباعة</h1>
    <div class="actions">
        <!-- <button class="btn btn-primary" onclick="window.location.reload()">تحديث</button> -->
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>المعرف</th>
                    <th>المستند</th>
                    <th>الطابعة</th>
                    <th>الحالة</th>
                    <th>المحاولات</th>
                    <th>بواسطة</th>
                    <th>الوقت</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jobs)): ?>
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">لا توجد مهام طباعة معلقة</td>
                </tr>
                <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                <tr>
                    <td>#<?= $job['id'] ?></td>
                    <td><?= $job['document_type'] ?></td>
                    <td><?= $job['printer_name'] ?></td>
                    <td>
                        <span class="badge badge-soft-<?= getStatusColor($job['status']) ?>">
                            <?= getStatusText($job['status']) ?>
                        </span>
                    </td>
                    <td><?= $job['attempts'] ?></td>
                    <td><?= $job['user_name'] ?? '-' ?></td>
                    <td dir="ltr"><?= date('H:i:s', strtotime($job['created_at'])) ?></td>
                    <td>
                        <?php if ($job['status'] == 'failed'): ?>
                        <form action="<?= url('print-queue/retry') ?>" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $job['id'] ?>">
                            <button type="submit" class="btn-icon btn-icon-sm" title="إعادة المحاولة">
                                <i data-feather="refresh-cw"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                        
                        <a href="<?= url('print-queue/delete/' . $job['id']) ?>" 
                           class="btn-icon btn-icon-sm text-danger" 
                           onclick="return confirm('حذف المهمة؟')" title="حذف">
                            <i data-feather="trash-2"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
function getStatusColor($status) {
    return match($status) {
        'pending' => 'warning',
        'processing' => 'info',
        'completed' => 'success',
        'failed' => 'danger',
        default => 'secondary'
    };
}

function getStatusText($status) {
    return match($status) {
        'pending' => 'قيد الانتظار',
        'processing' => 'جاري الطباعة',
        'completed' => 'مكتمل',
        'failed' => 'فشل',
        default => $status
    };
}

$content = ob_get_clean();
require VIEWS_PATH . 'layouts/main.php';
?>
