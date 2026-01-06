<?php
$pageTitle = 'إدارة التحاليل';
ob_start();
?>

<div class="card">
    <div class="card-header">
        <div class="card-title-wrap">
            <h2 class="card-title">قائمة التحاليل الطبية</h2>
            <div class="card-subtitle">إدارة أنواع التحاليل والقيم الطبيعية</div>
        </div>
        <div class="card-actions">
            <button type="button" class="btn btn-primary" onclick="showAddModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                تحليل جديد
            </button>
        </div>
    </div>
    
    <div class="card-body">
        <?php if (empty($tests)): ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="64" height="64">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
            </svg>
            <h3>لا توجد تحاليل مسجلة</h3>
            <p>أضف أنواع التحاليل ليتمكن الطبيب من طلبها</p>
            <button class="btn btn-primary" onclick="showAddModal()">تحليل جديد</button>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>اسم التحليل</th>
                        <th>المعدل الطبيعي (Range)</th>
                        <th>الوحدة (Unit)</th>
                        <th>السعر</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tests as $test): ?>
                    <tr>
                        <td class="font-bold"><?= $test['name'] ?></td>
                        <td><?= $test['normal_range'] ?: '-' ?></td>
                        <td><?= $test['unit'] ?: '-' ?></td>
                        <td><?= number_format($test['price'], 2) ?></td>
                        <td>
                            <?php if ($test['is_active']): ?>
                                <span class="badge badge-success">نشط</span>
                            <?php else: ?>
                                <span class="badge badge-danger">متوقف</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="btn btn-ghost btn-sm" onclick="showEditModal(<?= $test['id'] ?>)">
                                    تعديل
                                </button>
                                <button class="btn btn-ghost btn-sm text-danger" onclick="deleteTest(<?= $test['id'] ?>)">
                                    حذف
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="testModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">إضافة تحليل جديد</h3>
            <button type="button" class="modal-close" onclick="closeModal()">×</button>
        </div>
        <form id="testForm" action="<?= url('lab-tests/store') ?>" method="POST">
            <div class="modal-body">
                <input type="hidden" name="id" id="testId">
                
                <div class="form-group">
                    <label class="form-label required">اسم التحليل</label>
                    <input type="text" name="name" id="testName" class="form-control" required placeholder="مثال: CBC">
                </div>
                
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">المعدل الطبيعي (Range)</label>
                            <input type="text" name="normal_range" id="testRange" class="form-control" placeholder="مثال: 4.5 - 11.0">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">الوحدة (Unit)</label>
                            <input type="text" name="unit" id="testUnit" class="form-control" placeholder="مثال: K/uL">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">السعر الافتراضي</label>
                    <input type="number" step="0.01" name="price" id="testPrice" class="form-control" placeholder="0.00">
                </div>
                
                <div class="form-group hidden" id="statusGroup">
                    <label class="label-checkbox">
                        <input type="checkbox" name="is_active" id="testActive" value="1" checked>
                        <span>التحليل مفعل</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">إلغاء</button>
                <button type="submit" class="btn btn-primary">حفظ</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddModal() {
    document.getElementById('modalTitle').textContent = 'إضافة تحليل جديد';
    document.getElementById('testForm').action = '<?= url('lab-tests/store') ?>';
    document.getElementById('testForm').reset();
    document.getElementById('testId').value = '';
    document.getElementById('statusGroup').classList.add('hidden');
    document.getElementById('testModal').classList.add('show');
}

function showEditModal(id) {
    document.getElementById('modalTitle').textContent = 'تعديل بيانات التحليل';
    document.getElementById('testForm').action = '<?= url('lab-tests/update/') ?>' + id;
    
    fetch('<?= url('lab-tests/show/') ?>' + id, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            const t = data.test;
            document.getElementById('testId').value = t.id;
            document.getElementById('testName').value = t.name;
            document.getElementById('testRange').value = t.normal_range;
            document.getElementById('testUnit').value = t.unit;
            document.getElementById('testPrice').value = t.price;
            document.getElementById('testActive').checked = t.is_active == 1;
            document.getElementById('statusGroup').classList.remove('hidden');
            
            document.getElementById('testModal').classList.add('show');
        }
    });
}

function closeModal() {
    document.getElementById('testModal').classList.remove('show');
}

function deleteTest(id) {
    if(!confirm('هل أنت متأكد من حذف هذا التحليل؟')) return;
    
    fetch('<?= url('lab-tests/delete/') ?>' + id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            location.reload();
        } else {
            alert(data.error || 'حدث خطأ');
        }
    });
}
</script>

<?php
$content = ob_get_clean();
require VIEWS_PATH . 'layouts/main.php';
?>
