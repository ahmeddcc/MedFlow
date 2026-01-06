<?php
$pageTitle = 'الروشتة';
ob_start();
?>

<div class="prescriptions-page">
    <!-- شريط التحكم -->
    <div class="prescription-controls">
        <div class="controls-right">
            <button class="btn btn-primary" onclick="showNewPrescriptionModal()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                روشتة جديدة
            </button>
            <a href="<?= url('prescriptions/lab') ?>" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18l-2-3h-2l-2 3-2-3H8l-2 3z"></path>
                </svg>
                التحاليل
            </a>
        </div>
        <div class="controls-left">
            <input type="date" class="form-control" id="filterDate" value="<?= date('Y-m-d') ?>" onchange="filterByDate()">
        </div>
    </div>
    
    <!-- قائمة الروشتات -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">وصفات اليوم</h2>
        </div>
        <div class="card-body">
            <?php if (empty($prescriptions)): ?>
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="64" height="64">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                <p>لا توجد وصفات</p>
            </div>
            <?php else: ?>
            <div class="prescriptions-list">
                <?php foreach ($prescriptions as $rx): ?>
                <div class="prescription-item">
                    <div class="rx-number"><?= $rx['prescription_number'] ?></div>
                    <div class="rx-info">
                        <div class="rx-patient"><?= $rx['patient_name'] ?></div>
                        <div class="rx-meta">
                            <span><?= $rx['electronic_number'] ?></span>
                            <span><?= $rx['items_count'] ?> أدوية</span>
                            <span><?= date('h:i A', strtotime($rx['created_at'])) ?></span>
                        </div>
                    </div>
                    <div class="rx-actions">
                        <button class="btn btn-ghost btn-sm" onclick="viewPrescription(<?= $rx['id'] ?>)" title="عرض">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                        <button class="btn btn-ghost btn-sm" onclick="printPrescription(<?= $rx['id'] ?>)" title="طباعة">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                <rect x="6" y="14" width="12" height="8"></rect>
                            </svg>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- مودال روشتة جديدة -->
<div class="modal-overlay" id="newPrescriptionModal">
    <div class="modal" style="max-width: 700px;">
        <div class="modal-header">
            <h3>روشتة جديدة</h3>
            <button type="button" class="modal-close" onclick="closeNewPrescriptionModal()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <!-- البحث عن المريض -->
            <div class="form-group" style="position: relative;">
                <label class="form-label">المريض <span class="required">*</span></label>
                <input type="text" class="form-control" id="rxPatientSearch" placeholder="ابحث باسم المريض..." autocomplete="off">
                <input type="hidden" id="rxSelectedPatientId">
                <div class="search-results" id="rxPatientResults"></div>
                <div class="selected-patient" id="rxSelectedPatientInfo" style="display: none;">
                    <span id="rxSelectedPatientName"></span>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="clearRxPatient()">✕</button>
                </div>
            </div>
            
            <!-- التشخيص -->
            <div class="form-group">
                <label class="form-label">التشخيص</label>
                <input type="text" class="form-control" id="rxDiagnosis" placeholder="التشخيص...">
            </div>
            
            <!-- الأدوية -->
            <div class="form-group">
                <label class="form-label">الأدوية</label>
                <div class="rx-search-box">
                    <input type="text" class="form-control" id="medicationSearch" placeholder="ابحث عن دواء..." autocomplete="off">
                    <button type="button" class="btn btn-secondary btn-icon" onclick="showQuickAddMedModal()" title="إضافة دواء جديد للقاعدة">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </button>
                    <div class="search-results" id="medicationResults"></div>
                </div>
            </div>
            
            <!-- البنود -->
            <div class="rx-items-container">
                <table class="rx-items-table" id="rxItemsTable">
                    <thead>
                        <tr>
                            <th>الدواء</th>
                            <th style="width: 120px;">الجرعة</th>
                            <th style="width: 120px;">التكرار</th>
                            <th style="width: 80px;">المدة</th>
                            <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="rxItems"></tbody>
                </table>
            </div>
            
            <!-- ملاحظات للصيدلي -->
            <div class="form-group">
                <label class="form-label">ملاحظات للصيدلي</label>
                <textarea class="form-control" id="rxNotes" rows="2"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeNewPrescriptionModal()">إلغاء</button>
            <button type="button" class="btn btn-primary" onclick="savePrescription()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                حفظ الروشتة
            </button>
        </div>
    </div>
</div>

<style>
.prescriptions-page {
    max-width: 1000px;
    margin: 0 auto;
}

.prescription-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-xl);
    flex-wrap: wrap;
    gap: var(--spacing-md);
}

.controls-right {
    display: flex;
    gap: var(--spacing-sm);
}

.prescriptions-list {
    display: flex;
    flex-direction: column;
}

.prescription-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-lg);
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--border-light);
    transition: background var(--transition-fast);
}

.prescription-item:hover {
    background: var(--bg-secondary);
}

.rx-number {
    font-family: monospace;
    font-size: var(--font-size-sm);
    padding: 4px 8px;
    background: rgba(78, 205, 196, 0.1);
    border-radius: var(--radius-sm);
    color: var(--primary);
}

.rx-info {
    flex: 1;
}

.rx-patient {
    font-weight: 600;
    color: var(--text-primary);
}

.rx-meta {
    display: flex;
    gap: var(--spacing-md);
    font-size: var(--font-size-sm);
    color: var(--text-muted);
}

.rx-actions {
    display: flex;
    gap: var(--spacing-xs);
}

/* الأدوية */
.medications-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: var(--spacing-sm);
}

.med-btn {
    padding: var(--spacing-sm) var(--spacing-md);
    background: var(--bg-secondary);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: var(--font-size-sm);
}

.med-btn:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.rx-items-container {
    margin-top: var(--spacing-lg);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-md);
    overflow: hidden;
}

.rx-items-table {
    width: 100%;
    border-collapse: collapse;
}

.rx-items-table th,
.rx-items-table td {
    padding: var(--spacing-sm) var(--spacing-md);
    text-align: right;
    border-bottom: 1px solid var(--border-light);
}

.rx-items-table th {
    background: var(--bg-secondary);
    font-weight: 600;
}

.empty-state {
    text-align: center;
    padding: var(--spacing-2xl);
    color: var(--text-muted);
}

.empty-state svg {
    opacity: 0.3;
    margin-bottom: var(--spacing-lg);
}

/* المودال */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    padding: var(--spacing-lg);
}

.modal-overlay.show {
    display: flex;
}

.modal {
    background: var(--bg-card);
    border-radius: var(--radius-xl);
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--border-light);
}

.modal-header h3 {
    font-size: var(--font-size-lg);
    font-weight: 700;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: var(--spacing-xs);
    border-radius: var(--radius-sm);
}

.modal-body {
    padding: var(--spacing-lg);
    overflow-y: auto;
    max-height: 60vh;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: var(--spacing-sm);
    padding: var(--spacing-lg);
    border-top: 1px solid var(--border-light);
    background: var(--bg-secondary);
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--bg-card);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-lg);
    max-height: 200px;
    overflow-y: auto;
    z-index: 100;
    display: none;
}

.search-results.show {
    display: block;
}

.search-result-item {
    padding: var(--spacing-sm) var(--spacing-md);
    cursor: pointer;
    border-bottom: 1px solid var(--border-light);
}

.search-result-item:hover {
    background: var(--bg-secondary);
}

.selected-patient {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--spacing-sm) var(--spacing-md);
    background: rgba(78, 205, 196, 0.1);
    border-radius: var(--radius-md);
    margin-top: var(--spacing-sm);
    color: var(--primary);
    font-weight: 600;
}
</style>

<script>
let rxItems = [];
let rxItemIdCounter = 0;

// مودال الوصفة الجديدة
function showNewPrescriptionModal() {
    rxItems = [];
    renderRxItems();
    document.getElementById('rxPatientSearch').value = '';
    document.getElementById('rxSelectedPatientId').value = '';
    document.getElementById('rxSelectedPatientInfo').style.display = 'none';
    document.getElementById('rxDiagnosis').value = '';
    document.getElementById('rxNotes').value = '';
    document.getElementById('newPrescriptionModal').classList.add('show');
}

function closeNewPrescriptionModal() {
    document.getElementById('newPrescriptionModal').classList.remove('show');
}

// البحث عن مريض
let rxSearchTimeout;
document.getElementById('rxPatientSearch').addEventListener('input', function() {
    clearTimeout(rxSearchTimeout);
    const query = this.value;
    
    if (query.length < 2) {
        document.getElementById('rxPatientResults').classList.remove('show');
        return;
    }
    
    rxSearchTimeout = setTimeout(() => {
        fetch('<?= url('prescriptions/search-patient') ?>?q=' + encodeURIComponent(query), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            const results = document.getElementById('rxPatientResults');
            if (data.results.length === 0) {
                results.innerHTML = '<div class="search-result-item">لا توجد نتائج</div>';
            } else {
                results.innerHTML = data.results.map(p => 
                    `<div class="search-result-item" onclick="selectRxPatient(${p.id}, '${p.full_name}')">
                        <strong>${p.full_name}</strong> - ${p.electronic_number}
                    </div>`
                ).join('');
            }
            results.classList.add('show');
        });
    }, 300);
});

// البحث عن دواء (Autocomplete)
let medSearchTimeout;
document.getElementById('medicationSearch').addEventListener('input', function() {
    clearTimeout(medSearchTimeout);
    const query = this.value;
    
    if (query.length < 2) {
        document.getElementById('medicationResults').classList.remove('show');
        return;
    }
    
    medSearchTimeout = setTimeout(() => {
        fetch('<?= url('medications/search') ?>?q=' + encodeURIComponent(query), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            const results = document.getElementById('medicationResults');
            if (data.results.length === 0) {
                results.innerHTML = '<div class="search-result-item text-muted">لا توجد نتائج - أضف دواء جديد</div>';
            } else {
                results.innerHTML = data.results.map(m => 
                    `<div class="search-result-item" onclick="selectMedication(${m.id}, '${m.name.replace(/'/g, "\\'")}', '${m.default_dosage || ''}', '${m.default_frequency || ''}', '${m.default_duration || ''}')">
                        <strong>${m.name}</strong>
                    </div>`
                ).join('');
            }
            results.classList.add('show');
        });
    }, 300);
});

function selectMedication(id, name, dosage, freq, dur) {
    addMedication(id, name, dosage, freq, dur);
    document.getElementById('medicationSearch').value = '';
    document.getElementById('medicationResults').classList.remove('show');
}

function selectRxPatient(id, name) {
    document.getElementById('rxSelectedPatientId').value = id;
    document.getElementById('rxSelectedPatientName').textContent = name;
    document.getElementById('rxSelectedPatientInfo').style.display = 'flex';
    document.getElementById('rxPatientSearch').style.display = 'none';
    document.getElementById('rxPatientResults').classList.remove('show');
}

function clearRxPatient() {
    document.getElementById('rxSelectedPatientId').value = '';
    document.getElementById('rxSelectedPatientInfo').style.display = 'none';
    document.getElementById('rxPatientSearch').style.display = 'block';
    document.getElementById('rxPatientSearch').value = '';
}

// إضافة دواء
function addMedication(medId, name, dosage, frequency, duration) {
    rxItems.push({
        id: ++rxItemIdCounter,
        medication_id: medId,
        name: name,
        dosage: dosage,
        frequency: frequency,
        duration: duration
    });
    renderRxItems();
}

function renderRxItems() {
    const tbody = document.getElementById('rxItems');
    tbody.innerHTML = rxItems.map(item => `
        <tr>
            <td>${item.name}</td>
            <td><input type="text" class="form-control form-control-sm" value="${item.dosage}" onchange="updateRxItem(${item.id}, 'dosage', this.value)"></td>
            <td><input type="text" class="form-control form-control-sm" value="${item.frequency}" onchange="updateRxItem(${item.id}, 'frequency', this.value)"></td>
            <td><input type="text" class="form-control form-control-sm" value="${item.duration}" onchange="updateRxItem(${item.id}, 'duration', this.value)"></td>
            <td><button type="button" class="btn btn-ghost btn-sm" onclick="removeRxItem(${item.id})" style="color: var(--danger);">✕</button></td>
        </tr>
    `).join('');
}

function updateRxItem(id, field, value) {
    const item = rxItems.find(i => i.id === id);
    if (item) {
        item[field] = value;
    }
}

function removeRxItem(id) {
    rxItems = rxItems.filter(i => i.id !== id);
    renderRxItems();
}

function savePrescription() {
    const patientId = document.getElementById('rxSelectedPatientId').value;
    
    if (!patientId) {
        notify.error('يرجى اختيار المريض');
        return;
    }
    
    if (rxItems.length === 0) {
        notify.error('يرجى إضافة دواء واحد على الأقل');
        return;
    }
    
    const formData = new FormData();
    formData.append('patient_id', patientId);
    formData.append('diagnosis', document.getElementById('rxDiagnosis').value);
    formData.append('notes', document.getElementById('rxNotes').value);
    formData.append('items', JSON.stringify(rxItems));
    
    fetch('<?= url('prescriptions/create') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            notify.success(data.message);
            closeNewPrescriptionModal();
            location.reload();
        } else {
            notify.error(data.error);
        }
    });
}

function viewPrescription(id) {
    notify.info('عرض الوصفة #' + id);
}

function printPrescription(id) {
    window.open('<?= url('print/prescription') ?>?id=' + id, '_blank', 'width=400,height=600');
}

function filterByDate() {
    const date = document.getElementById('filterDate').value;
    window.location.href = '<?= url('prescriptions') ?>?date=' + date;
}

// إضافة دواء سريع
function showQuickAddMedModal() {
    document.getElementById('quickMedName').value = document.getElementById('medicationSearch').value;
    document.getElementById('quickMedDosage').value = '';
    document.getElementById('quickMedFreq').value = '';
    document.getElementById('quickMedDur').value = '';
    document.getElementById('quickAddMedModal').classList.add('show');
}

function closeQuickAddMedModal() {
    document.getElementById('quickAddMedModal').classList.remove('show');
}

function saveQuickMed() {
    const name = document.getElementById('quickMedName').value;
    const dosage = document.getElementById('quickMedDosage').value;
    const freq = document.getElementById('quickMedFreq').value;
    const dur = document.getElementById('quickMedDur').value;
    
    if(!name) {
        notify.error('اسم الدواء مطلوب');
        return;
    }
    
    // إرسال للباك اند
    const formData = new FormData();
    formData.append('name', name);
    formData.append('default_dosage', dosage);
    formData.append('default_frequency', freq);
    formData.append('default_duration', dur);
    
    fetch('<?= url('medications/store') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            // إضافة الدواء للقائمة الحالية
            addMedication(
                data.medication.id, 
                data.medication.name, 
                data.medication.dosage, 
                data.medication.frequency, 
                data.medication.duration
            );
            
            notify.success('تم حفظ الدواء وإضافته للروشتة');
            closeQuickAddMedModal();
            document.getElementById('medicationSearch').value = '';
        } else {
            notify.error('حدث خطأ أثناء حفظ الدواء');
        }
    });
}
</script>

<?php
$content = ob_get_clean();
require VIEWS_PATH . 'layouts/main.php';
?>
