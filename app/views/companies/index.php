<?php
$pageTitle = 'إدارة الشركات';
ob_start();
?>

<div class="card">
    <div class="card-header">
        <div class="card-title-wrap">
            <h2 class="card-title">إدارة شركات الأدوية</h2>
            <div class="card-subtitle">إدارة الشركات والأحرف المميزة لها</div>
        </div>
        <div class="card-actions">
            <button type="button" class="btn btn-primary" onclick="showAddModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                إضافة شركة
            </button>
        </div>
    </div>
    
    <div class="card-body">
        <?php if (empty($companies)): ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="21" width="18" height="2"></rect>
                <rect x="5" y="3" width="14" height="18" rx="2"></rect>
            </svg>
            <h3>لا توجد شركات مسجلة</h3>
            <p>ابدأ بإضافة شركات الأدوية للتعامل مع المندوبين</p>
            <button class="btn btn-primary" onclick="showAddModal()">إضافة شركة</button>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>الشعار</th>
                        <th>اسم الشركة</th>
                        <th>الحرف المميز</th>
                        <th>معلومات الاتصال</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($companies as $company): ?>
                    <tr>
                        <td style="width: 60px;">
                            <?php if ($company['logo']): ?>
                                <img src="<?= url($company['logo']) ?>" alt="<?= $company['name'] ?>" class="company-logo-thumb">
                            <?php else: ?>
                                <div class="company-logo-placeholder"><?= mb_substr($company['name'], 0, 1) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="font-bold"><?= $company['name'] ?></td>
                        <td><span class="badge badge-primary"><?= $company['letter'] ?></span></td>
                        <td>
                            <?php if ($company['phone']): ?>
                                <div><small class="text-muted">هـ:</small> <?= $company['phone'] ?></div>
                            <?php endif; ?>
                            <?php if ($company['email']): ?>
                                <div><small class="text-muted">ب:</small> <?= $company['email'] ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($company['is_active']): ?>
                                <span class="badge badge-success">نشط</span>
                            <?php else: ?>
                                <span class="badge badge-danger">متوقف</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="btn btn-ghost btn-sm" onclick="showEditModal(<?= $company['id'] ?>)">
                                    تعديل
                                </button>
                                <button class="btn btn-ghost btn-sm text-danger" onclick="deleteCompany(<?= $company['id'] ?>)">
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
<div class="modal-overlay" id="companyModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">إضافة شركة جديدة</h3>
            <button type="button" class="modal-close" onclick="closeModal()">×</button>
        </div>
        <form id="companyForm" action="<?= url('companies/store') ?>" method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="id" id="companyId">
                
                <div class="form-group">
                    <label class="form-label required">اسم الشركة</label>
                    <input type="text" name="name" id="companyName" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label required">الحرف المميز (لترتيب الدور)</label>
                    <input type="text" name="letter" id="companyLetter" class="form-control" maxlength="2" required placeholder="مثلاً A, B, C">
                    <small class="text-muted">سيستخدم هذا الحرف في أرقام انتظار المندوبين (مثلاً A001)</small>
                </div>
                
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">رقم الهاتف</label>
                            <input type="text" name="phone" id="companyPhone" class="form-control">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="email" id="companyEmail" class="form-control">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">شعار الشركة</label>
                    <input type="file" name="logo" id="companyLogo" class="form-control" accept="image/*">
                </div>
                
                <div class="form-group hidden" id="statusGroup">
                    <label class="label-checkbox">
                        <input type="checkbox" name="is_active" id="companyActive" value="1" checked>
                        <span>الشركة نشطة</span>
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

<style>
.company-logo-thumb {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid var(--border-light);
}
.company-logo-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--bg-secondary);
    color: var(--text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
}
</style>

<script>
function showAddModal() {
    document.getElementById('modalTitle').textContent = 'إضافة شركة جديدة';
    document.getElementById('companyForm').action = '<?= url('companies/store') ?>';
    document.getElementById('companyForm').reset();
    document.getElementById('companyId').value = '';
    document.getElementById('statusGroup').classList.add('hidden');
    document.getElementById('companyModal').classList.add('show');
}

function showEditModal(id) {
    document.getElementById('modalTitle').textContent = 'تعديل بيانات الشركة';
    document.getElementById('companyForm').action = '<?= url('companies/update/') ?>' + id;
    
    fetch('<?= url('companies/show/') ?>' + id, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            const co = data.company;
            document.getElementById('companyId').value = co.id;
            document.getElementById('companyName').value = co.name;
            document.getElementById('companyLetter').value = co.letter;
            document.getElementById('companyPhone').value = co.phone;
            document.getElementById('companyEmail').value = co.email;
            document.getElementById('companyActive').checked = co.is_active == 1;
            document.getElementById('statusGroup').classList.remove('hidden');
            
            document.getElementById('companyModal').classList.add('show');
        }
    });
}

function closeModal() {
    document.getElementById('companyModal').classList.remove('show');
}

function deleteCompany(id) {
    if(!confirm('هل أنت متأكد من حذف هذه الشركة؟')) return;
    
    fetch('<?= url('companies/delete/') ?>' + id, {
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
