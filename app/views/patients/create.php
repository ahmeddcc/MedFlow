<?php
$pageTitle = __('new_patient');
ob_start();
?>

<div class="patient-form-page">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><?= __('new_patient') ?></h2>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= url('patients/create') ?>" enctype="multipart/form-data">
                <?= csrfField() ?>
                
                <div class="form-grid">
                    <!-- الاسم الكامل -->
                    <div class="form-group form-group-full">
                        <label class="form-label">
                            <?= __('full_name') ?>
                            <span class="required">*</span>
                        </label>
                        <input type="text" name="full_name" class="form-control" required autofocus
                               placeholder="أدخل اسم المريض بالكامل">
                    </div>
                    
                    <!-- رقم الملف الورقي -->
                    <div class="form-group">
                        <label class="form-label"><?= __('paper_file_number') ?></label>
                        <input type="text" name="paper_file_number" id="paperFileNumber" class="form-control"
                               placeholder="رقم الملف الورقي (اختياري)">
                    </div>
                    
                    <!-- الرقم الإلكتروني -->
                    <div class="form-group">
                        <label class="form-label">الرقم الإلكتروني</label>
                        <input type="text" name="electronic_number" id="electronicNumber" class="form-control" 
                               readonly dir="ltr" placeholder="سيتم توليده تلقائياً">
                        <small class="form-hint">يتولد تلقائياً عند كتابة رقم الملف الورقي</small>
                    </div>
                    
                    <!-- الباركود -->
                    <div class="form-group form-group-full">
                        <label class="form-label">الباركود</label>
                        <div class="barcode-container">
                            <input type="text" name="barcode" id="barcodeField" class="form-control barcode-input" 
                                   readonly dir="ltr" placeholder="سيتم توليده تلقائياً">
                            <div class="barcode-display" id="barcodeDisplay">
                                <svg id="barcodePreview"></svg>
                            </div>
                        </div>
                        <small class="form-hint">يتولد تلقائياً عند كتابة أول حرف من الاسم (Code 128)</small>
                    </div>
                    
                    <!-- رقم الهاتف -->
                    <div class="form-group">
                        <label class="form-label"><?= __('phone') ?></label>
                        <input type="tel" name="phone" class="form-control" dir="ltr"
                               placeholder="01xxxxxxxxx">
                    </div>
                    
                    <!-- هاتف بديل -->
                    <div class="form-group">
                        <label class="form-label"><?= __('secondary_phone') ?></label>
                        <input type="tel" name="secondary_phone" class="form-control" dir="ltr"
                               placeholder="هاتف بديل (اختياري)">
                    </div>
                    
                    <!-- تاريخ الميلاد -->
                    <div class="form-group">
                        <label class="form-label"><?= __('date_of_birth') ?></label>
                        <input type="date" name="date_of_birth" class="form-control">
                    </div>
                    
                    <!-- الجنس -->
                    <div class="form-group">
                        <label class="form-label"><?= __('gender') ?></label>
                        <select name="gender" class="form-control">
                            <option value="">اختر الجنس</option>
                            <option value="male"><?= __('male') ?></option>
                            <option value="female"><?= __('female') ?></option>
                        </select>
                    </div>
                    
                    <!-- العنوان -->
                    <div class="form-group form-group-full">
                        <label class="form-label"><?= __('address') ?></label>
                        <input type="text" name="address" class="form-control"
                               placeholder="العنوان (اختياري)">
                    </div>
                    
                    <!-- التاريخ الطبي -->
                    <div class="form-group form-group-full">
                        <label class="form-label"><?= __('medical_history') ?></label>
                        <textarea name="medical_history" class="form-control" rows="3"
                                  placeholder="التاريخ الطبي للمريض (الأمراض المزمنة، الحساسية، العمليات السابقة...)"></textarea>
                    </div>
                    
                    <!-- ملاحظات -->
                    <div class="form-group form-group-full">
                        <label class="form-label"><?= __('notes') ?></label>
                        <textarea name="notes" class="form-control" rows="2"
                                  placeholder="ملاحظات إضافية"></textarea>
                    </div>
                    
                    <!-- المرفقات -->
                    <div class="form-group form-group-full">
                        <label class="form-label"><?= __('attachments') ?></label>
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
                                <small>JPG, PNG, PDF, DOC - أقصى حجم 10 ميجا</small>
                            </div>
                        </div>
                        <div class="file-list" id="fileList"></div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer">
            <a href="<?= url('patients') ?>" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <?= __('cancel') ?>
            </a>
            <button type="submit" form="patientForm" class="btn btn-primary" onclick="document.querySelector('form').submit()">
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
    padding: var(--spacing-2xl);
    text-align: center;
    cursor: pointer;
    transition: all var(--transition-normal);
}

.file-upload-area:hover,
.file-upload-area.dragover {
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

.file-upload-content p {
    margin-bottom: var(--spacing-sm);
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

.file-item button {
    background: none;
    border: none;
    color: var(--danger);
    cursor: pointer;
    padding: 0;
    display: flex;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}

.form-hint {
    color: var(--text-muted);
    font-size: var(--font-size-xs);
    margin-top: 4px;
    display: block;
}

.barcode-container {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.barcode-input {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    letter-spacing: 2px;
}

.barcode-display {
    display: none;
    background: #fff;
    padding: var(--spacing-md);
    border-radius: var(--radius-md);
    border: 1px solid var(--border-light);
    text-align: center;
}

.barcode-display.show {
    display: block;
}

.barcode-display svg {
    max-width: 100%;
    height: auto;
}

input[readonly] {
    background: var(--bg-secondary);
    cursor: not-allowed;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ملفات التحميل
    const fileInput = document.getElementById('fileInput');
    const fileUploadArea = document.getElementById('fileUploadArea');
    const fileList = document.getElementById('fileList');
    
    if (fileUploadArea) {
        fileUploadArea.addEventListener('click', () => fileInput.click());
        
        fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadArea.classList.add('dragover');
        });
        
        fileUploadArea.addEventListener('dragleave', () => {
            fileUploadArea.classList.remove('dragover');
        });
        
        fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadArea.classList.remove('dragover');
            fileInput.files = e.dataTransfer.files;
            updateFileList();
        });
        
        fileInput.addEventListener('change', updateFileList);
    }
    
    function updateFileList() {
        fileList.innerHTML = '';
        Array.from(fileInput.files).forEach((file, index) => {
            const item = document.createElement('div');
            item.className = 'file-item';
            item.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                <span>${file.name}</span>
                <small>(${formatFileSize(file.size)})</small>
            `;
            fileList.appendChild(item);
        });
    }
    
    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }
    
    // توليد الباركود
    const fullNameInput = document.querySelector('input[name="full_name"]');
    const barcodeField = document.getElementById('barcodeField');
    const barcodeDisplay = document.getElementById('barcodeDisplay');
    
    let barcodeGenerated = false;
    
    fullNameInput.addEventListener('input', function() {
        if (this.value.length >= 1 && !barcodeGenerated) {
            // توليد باركود فريد
            const timestamp = Date.now();
            const random = Math.floor(Math.random() * 9000) + 1000;
            const firstChar = this.value.charAt(0).toUpperCase();
            
            // تحويل الحروف العربية
            const arabicToEnglish = {
                'أ': 'A', 'ا': 'A', 'إ': 'A', 'آ': 'A',
                'ب': 'B', 'ت': 'T', 'ث': 'TH',
                'ج': 'G', 'ح': 'H', 'خ': 'KH',
                'د': 'D', 'ذ': 'TH', 'ر': 'R', 'ز': 'Z',
                'س': 'S', 'ش': 'SH', 'ص': 'S', 'ض': 'D',
                'ط': 'T', 'ظ': 'Z', 'ع': 'A', 'غ': 'GH',
                'ف': 'F', 'ق': 'Q', 'ك': 'K', 'ل': 'L',
                'م': 'M', 'ن': 'N', 'ه': 'H', 'و': 'W',
                'ي': 'Y', 'ى': 'Y', 'ة': 'H', 'ئ': 'Y',
                'ؤ': 'W', 'ء': 'A'
            };
            
            const letter = arabicToEnglish[firstChar] || (firstChar.match(/[a-z]/i) ? firstChar : 'X');
            const barcode = 'MF-' + letter + timestamp.toString().slice(-6) + random;
            
            barcodeField.value = barcode;
            generateBarcodeImage(barcode);
            barcodeGenerated = true;
        }
        
        if (this.value.length === 0) {
            barcodeField.value = '';
            barcodeDisplay.classList.remove('show');
            barcodeGenerated = false;
        }
    });

    function generateBarcodeImage(value) {
        if (typeof JsBarcode !== 'undefined') {
            JsBarcode("#barcodePreview", value, {
                format: "CODE128",
                lineColor: "#000",
                width: 2,
                height: 40,
                displayValue: true,
                fontSize: 14,
                fontOptions: "bold",
                margin: 0
            });
            barcodeDisplay.classList.add('show');
        }
    }
    
    // توليد الرقم الإلكتروني عند كتابة رقم الملف الورقي فقط
    const paperFileInput = document.getElementById('paperFileNumber');
    const electronicInput = document.getElementById('electronicNumber');
    
    paperFileInput.addEventListener('input', function() {
        if (this.value.length >= 1) {
            const year = new Date().getFullYear();
            // الرقم الإلكتروني يعتمد على رقم الملف الورقي
            // مثال: E2026-1005
            const electronic = 'E' + year + '-' + this.value;
            
            electronicInput.value = electronic;
        } else {
            electronicInput.value = '';
        }
    });
});
</script>
});
</script>

<?php
$content = ob_get_clean();
require VIEWS_PATH . 'layouts/main.php';
?>
