<?php
$pageTitle = __('edit_patient') . ' - ' . $patient['full_name'];
ob_start();
?>

<div class="patient-form-page">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><?= __('edit_patient') ?></h2>
            <div class="d-flex gap-2">
                <span class="badge badge-primary"><?= $patient['electronic_number'] ?></span>
                <span class="badge badge-info"><?= $patient['barcode'] ?></span>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= url('patients/' . $patient['id'] . '/edit') ?>" enctype="multipart/form-data">
                <?= csrfField() ?>
                
                <div class="form-grid">
                    <!-- الاسم الكامل -->
                    <div class="form-group form-group-full">
                        <label class="form-label">
                            <?= __('full_name') ?>
                            <span class="required">*</span>
                        </label>
                        <input type="text" name="full_name" class="form-control" required
                               value="<?= $patient['full_name'] ?>">
                    </div>
                    
                    <!-- رقم الملف الورقي -->
                    <div class="form-group">
                        <label class="form-label"><?= __('paper_file_number') ?></label>
                        <input type="text" name="paper_file_number" class="form-control"
                               value="<?= $patient['paper_file_number'] ?>">
                    </div>
                    
                    <!-- رقم الهاتف -->
                    <div class="form-group">
                        <label class="form-label"><?= __('phone') ?></label>
                        <input type="tel" name="phone" class="form-control" dir="ltr"
                               value="<?= $patient['phone'] ?>">
                    </div>
                    
                    <!-- هاتف بديل -->
                    <div class="form-group">
                        <label class="form-label"><?= __('secondary_phone') ?></label>
                        <input type="tel" name="secondary_phone" class="form-control" dir="ltr"
                               value="<?= $patient['secondary_phone'] ?>">
                    </div>
                    
                    <!-- تاريخ الميلاد -->
                    <div class="form-group">
                        <label class="form-label"><?= __('date_of_birth') ?></label>
                        <input type="date" name="date_of_birth" class="form-control"
                               value="<?= $patient['date_of_birth'] ?>">
                    </div>
                    
                    <!-- الجنس -->
                    <div class="form-group">
                        <label class="form-label"><?= __('gender') ?></label>
                        <select name="gender" class="form-control">
                            <option value="">اختر الجنس</option>
                            <option value="male" <?= $patient['gender'] === 'male' ? 'selected' : '' ?>><?= __('male') ?></option>
                            <option value="female" <?= $patient['gender'] === 'female' ? 'selected' : '' ?>><?= __('female') ?></option>
                        </select>
                    </div>
                    
                    <!-- العنوان -->
                    <div class="form-group form-group-full">
                        <label class="form-label"><?= __('address') ?></label>
                        <input type="text" name="address" class="form-control"
                               value="<?= $patient['address'] ?>">
                    </div>
                    
                    <!-- التاريخ الطبي -->
                    <div class="form-group form-group-full">
                        <label class="form-label"><?= __('medical_history') ?></label>
                        <textarea name="medical_history" class="form-control" rows="3"><?= $patient['medical_history'] ?></textarea>
                    </div>
                    
                    <!-- ملاحظات -->
                    <div class="form-group form-group-full">
                        <label class="form-label"><?= __('notes') ?></label>
                        <textarea name="notes" class="form-control" rows="2"><?= $patient['notes'] ?></textarea>
                    </div>
                    
                    <!-- المرفقات الجديدة -->
                    <div class="form-group form-group-full">
                        <label class="form-label">إضافة مرفقات جديدة</label>
                        <div class="file-upload-area" id="fileUploadArea">
                            <input type="file" name="attachments[]" id="fileInput" multiple 
                                   accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx" class="hidden">
                            <div class="file-upload-content">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="40" height="40">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                                <p>اسحب الملفات هنا أو <span class="text-primary">انقر للاختيار</span></p>
                            </div>
                        </div>
                        <div class="file-list" id="fileList"></div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer">
            <a href="<?= url('patients/' . $patient['id']) ?>" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <?= __('cancel') ?>
            </a>
            
            <?php if (hasRole('doctor', 'admin')): ?>
            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
                <?= __('delete') ?>
            </button>
            <?php endif; ?>
            
            <button type="submit" class="btn btn-primary" onclick="document.querySelector('form').submit()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                <?= __('save') ?>
            </button>
        </div>
    </div>
</div>

<style>
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--spacing-lg);
}

.form-group-full {
    grid-column: 1 / -1;
}

.file-upload-area {
    border: 2px dashed var(--border-color);
    border-radius: var(--radius-md);
    padding: var(--spacing-xl);
    text-align: center;
    cursor: pointer;
    transition: all var(--transition-normal);
}

.file-upload-area:hover {
    border-color: var(--primary);
    background: rgba(78, 205, 196, 0.05);
}

.file-upload-content {
    color: var(--text-muted);
}

.file-upload-content svg {
    margin-bottom: var(--spacing-md);
    opacity: 0.5;
}

.file-list {
    margin-top: var(--spacing-md);
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-sm);
}

.file-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-sm) var(--spacing-md);
    background: var(--bg-secondary);
    border-radius: var(--radius-sm);
    font-size: var(--font-size-sm);
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('fileInput');
    const fileUploadArea = document.getElementById('fileUploadArea');
    const fileList = document.getElementById('fileList');
    
    fileUploadArea.addEventListener('click', () => fileInput.click());
    
    fileInput.addEventListener('change', function() {
        fileList.innerHTML = '';
        Array.from(this.files).forEach(file => {
            const item = document.createElement('div');
            item.className = 'file-item';
            item.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                <span>${file.name}</span>
            `;
            fileList.appendChild(item);
        });
    });
});

function confirmDelete() {
    if (confirm('هل أنت متأكد من حذف هذا المريض؟ لا يمكن التراجع عن هذا الإجراء.')) {
        window.location.href = '<?= url('patients/' . $patient['id'] . '/delete') ?>';
    }
}
</script>

<?php
$content = ob_get_clean();
require VIEWS_PATH . 'layouts/main.php';
?>
