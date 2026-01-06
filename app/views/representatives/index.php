<?php
$pageTitle = 'إدارة المناديب';
ob_start();
?>

<div class="card">
    <div class="card-header">
        <div class="card-title-wrap">
            <h2 class="card-title">إدارة المناديب الطبية</h2>
            <div class="card-subtitle">سجل المناديب المسجلين للنظام</div>
        </div>
        <div class="card-actions">
            <button type="button" class="btn btn-primary" onclick="showAddModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                إضافة مندوب
            </button>
        </div>
    </div>
    
    <div class="card-body">
        <?php if (empty($reps)): ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <h3>لا يوجد مناديب مسجلين</h3>
            <p>يمكنك تسجيل المناديب لسهولة إضافتهم لقائمة الانتظار</p>
            <button class="btn btn-primary" onclick="showAddModal()">إضافة مندوب</button>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>الشركة</th>
                        <th>معلومات الاتصال</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reps as $rep): ?>
                    <tr>
                        <td class="font-bold"><?= $rep['full_name'] ?></td>
                        <td><?= $rep['company_name'] ?></td>
                        <td>
                            <?php if ($rep['phone']): ?>
                                <div><small class="text-muted">هـ:</small> <?= $rep['phone'] ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($rep['is_active']): ?>
                                <span class="badge badge-success">نشط</span>
                            <?php else: ?>
                                <span class="badge badge-danger">متوقف</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="btn btn-ghost btn-sm" onclick="showEditModal(<?= $rep['id'] ?>)">
                                    تعديل
                                </button>
                                <button class="btn btn-ghost btn-sm text-danger" onclick="deleteRep(<?= $rep['id'] ?>)">
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
<div class="modal-overlay" id="repModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">إضافة مندوب جديد</h3>
            <button type="button" class="modal-close" onclick="closeModal()">×</button>
        </div>
        <form id="repForm" action="<?= url('representatives/store') ?>" method="POST">
            <div class="modal-body">
                <input type="hidden" name="id" id="repId">
                
                <div class="form-group">
                    <label class="form-label required">اسم المندوب</label>
                    <input type="text" name="full_name" id="repName" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label required">الشركة</label>
                    <select name="company_id" id="repCompany" class="form-control" required>
                        <option value="">اختر الشركة...</option>
                        <?php foreach ($companies as $company): ?>
                        <option value="<?= $company['id'] ?>"><?= $company['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">رقم الهاتف</label>
                            <input type="text" name="phone" id="repPhone" class="form-control">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="email" id="repEmail" class="form-control">
                        </div>
                    </div>
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
    document.getElementById('modalTitle').textContent = 'إضافة مندوب جديد';
    document.getElementById('repForm').action = '<?= url('representatives/store') ?>';
    document.getElementById('repForm').reset();
    document.getElementById('repId').value = '';
    document.getElementById('repModal').classList.add('show');
}

function showEditModal(id) {
    document.getElementById('modalTitle').textContent = 'تعديل بيانات المندوب';
    document.getElementById('repForm').action = '<?= url('representatives/update/') ?>' + id;
    
    fetch('<?= url('representatives/show/') ?>' + id, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            const rep = data.rep;
            document.getElementById('repId').value = rep.id;
            document.getElementById('repName').value = rep.full_name;
            document.getElementById('repCompany').value = rep.company_id;
            document.getElementById('repPhone').value = rep.phone;
            document.getElementById('repEmail').value = rep.email;
            
            document.getElementById('repModal').classList.add('show');
        }
    });
}

function closeModal() {
    document.getElementById('repModal').classList.remove('show');
}

function deleteRep(id) {
    if(!confirm('هل أنت متأكد من حذف هذا المندوب؟')) return;
    
    fetch('<?= url('representatives/delete/') ?>' + id, {
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
