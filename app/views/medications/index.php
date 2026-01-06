<?php
$pageTitle = 'إدارة الأدوية';
ob_start();
?>

<div class="card">
    <div class="card-header">
        <div class="card-title-wrap">
            <h2 class="card-title">قاعدة بيانات الأدوية</h2>
            <div class="card-subtitle">إدارة الأدوية والجرعات الافتراضية</div>
        </div>
        <div class="card-actions">
            <button type="button" class="btn btn-primary" onclick="showAddModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                إضافة دواء
            </button>
        </div>
    </div>
    
    <div class="card-body">
        <?php if (empty($medications)): ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="64" height="64">
                <path d="M10.5 20.5l10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7z"></path>
                <path d="M8.5 8.5l7 7"></path>
            </svg>
            <h3>لا توجد أدوية مسجلة</h3>
            <p>أضف الأدوية لتسهيل كتابة الروشتة والإكمال التلقائي</p>
            <button class="btn btn-primary" onclick="showAddModal()">إضافة دواء</button>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>اسم الدواء</th>
                        <th>الجرعة الافتراضية</th>
                        <th>التكرار</th>
                        <th>المدة</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($medications as $med): ?>
                    <tr>
                        <td class="font-bold"><?= $med['name'] ?></td>
                        <td><?= $med['default_dosage'] ?: '-' ?></td>
                        <td><?= $med['default_frequency'] ?: '-' ?></td>
                        <td><?= $med['default_duration'] ?: '-' ?></td>
                        <td>
                            <?php if ($med['is_active']): ?>
                                <span class="badge badge-success">نشط</span>
                            <?php else: ?>
                                <span class="badge badge-danger">متوقف</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="btn btn-ghost btn-sm" onclick="showEditModal(<?= $med['id'] ?>)">
                                    تعديل
                                </button>
                                <button class="btn btn-ghost btn-sm text-danger" onclick="deleteMed(<?= $med['id'] ?>)">
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
<div class="modal-overlay" id="medModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">إضافة دواء جديد</h3>
            <button type="button" class="modal-close" onclick="closeModal()">×</button>
        </div>
        <form id="medForm" action="<?= url('medications/store') ?>" method="POST">
            <div class="modal-body">
                <input type="hidden" name="id" id="medId">
                
                <div class="form-group">
                    <label class="form-label required">اسم الدواء</label>
                    <input type="text" name="name" id="medName" class="form-control" required placeholder="مثال: Panadol Extra 500mg">
                </div>
                
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">الجرعة الافتراضية</label>
                            <input type="text" name="default_dosage" id="medDosage" class="form-control" placeholder="مثال: قرص واحد">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">التكرار الافتراضي</label>
                            <input type="text" name="default_frequency" id="medFrequency" class="form-control" placeholder="مثال: 3 مرات يومياً">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">المدة الافتراضية</label>
                    <input type="text" name="default_duration" id="medDuration" class="form-control" placeholder="مثال: 5 أيام">
                </div>
                
                <div class="form-group hidden" id="statusGroup">
                    <label class="label-checkbox">
                        <input type="checkbox" name="is_active" id="medActive" value="1" checked>
                        <span>الدواء نشط</span>
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
    document.getElementById('modalTitle').textContent = 'إضافة دواء جديد';
    document.getElementById('medForm').action = '<?= url('medications/store') ?>';
    document.getElementById('medForm').reset();
    document.getElementById('medId').value = '';
    document.getElementById('statusGroup').classList.add('hidden');
    document.getElementById('medModal').classList.add('show');
}

function showEditModal(id) {
    document.getElementById('modalTitle').textContent = 'تعديل بيانات الدواء';
    document.getElementById('medForm').action = '<?= url('medications/update/') ?>' + id;
    
    fetch('<?= url('medications/show/') ?>' + id, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            const med = data.medication;
            document.getElementById('medId').value = med.id;
            document.getElementById('medName').value = med.name;
            document.getElementById('medDosage').value = med.default_dosage;
            document.getElementById('medFrequency').value = med.default_frequency;
            document.getElementById('medDuration').value = med.default_duration;
            document.getElementById('medActive').checked = med.is_active == 1;
            document.getElementById('statusGroup').classList.remove('hidden');
            
            document.getElementById('medModal').classList.add('show');
        }
    });
}

function closeModal() {
    document.getElementById('medModal').classList.remove('show');
}

function deleteMed(id) {
    if(!confirm('هل أنت متأكد من حذف هذا الدواء؟')) return;
    
    fetch('<?= url('medications/delete/') ?>' + id, {
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
