<?php
$pageTitle = 'التحاليل الطبية';
ob_start();
?>

<div class="lab-orders-page">
    <!-- شريط التحكم -->
    <div class="lab-controls">
        <div class="controls-right">
            <button class="btn btn-primary" onclick="showNewLabOrderModal()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                طلب تحليل جديد
            </button>
            <a href="<?= url('prescriptions') ?>" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                الروشتة
            </a>
        </div>
        <div class="controls-left">
            <input type="date" class="form-control" id="filterDate" value="<?= date('Y-m-d') ?>" onchange="filterByDate()">
            <select class="form-control" id="filterStatus" onchange="filterByStatus()">
                <option value="all">الكل</option>
                <option value="pending">معلق</option>
                <option value="completed">مكتمل</option>
            </select>
        </div>
    </div>
    
    <!-- قائمة الطلبات -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">طلبات اليوم</h2>
        </div>
        <div class="card-body">
            <?php if (empty($orders)): ?>
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="64" height="64">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                </svg>
                <p>لا توجد طلبات تحاليل</p>
            </div>
            <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                <div class="order-item status-<?= $order['status'] ?>">
                    <div class="order-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                        </svg>
                    </div>
                    <div class="order-info">
                        <div class="order-patient">
                            <?= $order['patient_name'] ?> 
                            <span class="order-number">#<?= $order['order_number'] ?></span>
                        </div>
                        <div class="order-test">
                            <?= $order['test_name'] ?>
                            <?php if ($order['status'] === 'completed'): ?>
                            <span class="result-badge status-<?= $order['result_status'] ?>">
                                النتيجة: <?= $order['result_value'] ?> <?= $order['unit'] ?? '' ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <div class="order-meta">
                            <span><?= date('h:i A', strtotime($order['created_at'])) ?></span>
                            <?php if ($order['notes']): ?>
                            <span class="order-notes">• <?= $order['notes'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="order-actions">
                        <?php if ($order['status'] === 'pending'): ?>
                        <button class="btn btn-primary btn-sm" onclick="showResultModal(<?= $order['id'] ?>, '<?= addslashes($order['test_name']) ?>', '<?= addslashes($order['patient_name']) ?>')">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            إدخال النتيجة
                        </button>
                        <?php else: ?>
                        <button class="btn btn-ghost btn-sm" onclick="showResultModal(<?= $order['id'] ?>, '<?= addslashes($order['test_name']) ?>', '<?= addslashes($order['patient_name']) ?>', true)">
                            تعديل
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="printReport(<?= $order['id'] ?>)">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                <rect x="6" y="14" width="12" height="8"></rect>
                            </svg>
                            طباعة
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- مودال طلب جديد -->
<div class="modal-overlay" id="newOrderModal">
    <div class="modal" style="max-width: 500px;">
        <div class="modal-header">
            <h3>طلب تحليل جديد</h3>
            <button type="button" class="modal-close" onclick="closeNewOrderModal()">
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
                <input type="text" class="form-control" id="patientSearch" placeholder="ابحث باسم المريض..." autocomplete="off">
                <input type="hidden" id="selectedPatientId">
                <div class="search-results" id="patientResults"></div>
                <div class="selected-patient" id="selectedPatientInfo" style="display: none;">
                    <span id="selectedPatientName"></span>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="clearPatient()">✕</button>
                </div>
            </div>
            
            <!-- التحليل -->
            <div class="form-group">
                <label class="form-label">التحليل المطلوب <span class="required">*</span></label>
                <select class="form-control" id="labTest">
                    <option value="">اختر التحليل...</option>
                    <?php foreach ($tests as $test): ?>
                    <option value="<?= $test['id'] ?>"><?= $test['name'] ?> (<?= $test['price'] ?> ج.م)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- ملاحظات -->
            <div class="form-group">
                <label class="form-label">ملاحظات</label>
                <textarea class="form-control" id="orderNotes" rows="2"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeNewOrderModal()">إلغاء</button>
            <button type="button" class="btn btn-primary" onclick="saveOrder()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                حفظ الطلب
            </button>
        </div>
    </div>
</div>

<!-- مودال النتائج -->
<div class="modal-overlay" id="resultModal">
    <div class="modal" style="max-width: 500px;">
        <div class="modal-header">
            <h3>نتيجة التحليل</h3>
            <button type="button" class="modal-close" onclick="closeResultModal()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="resultOrderId">
            
            <div class="alert alert-info-soft mb-3">
                <span id="resultInfoText"></span>
            </div>
            
            <div class="form-row">
                <div class="form-group col-8">
                    <label class="form-label">القيمة</label>
                    <input type="text" class="form-control" id="resultValue" placeholder="أدخل النتيجة...">
                </div>
                <div class="form-group col-4">
                    <label class="form-label">الحالة</label>
                    <select class="form-control" id="resultStatus">
                        <option value="normal">طبيعي</option>
                        <option value="high">مرتفع</option>
                        <option value="low">منخفض</option>
                        <option value="abnormal">غير طبيعي</option>
                    </select>
                </div>
            </div>
            
                <div class="form-group">
                    <label class="form-label">تفاصيل</label>
                    <textarea class="form-control" id="resultDetails" rows="3" placeholder="تفاصيل النتيجة..."></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">مرفق النتيجة (صورة / PDF)</label>
                    <input type="file" id="resultAttachment" class="form-control" accept="image/*,.pdf">
                    <small class="text-muted">يمكنك رفع صورة التحليل أو ملف PDF</small>
                </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeResultModal()">إلغاء</button>
            <button type="button" class="btn btn-success" onclick="saveResult()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                حفظ النتيجة
            </button>
        </div>
    </div>
</div>

<style>
.lab-orders-page {
    max-width: 1000px;
    margin: 0 auto;
}

.lab-controls {
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

.orders-list {
    display: flex;
    flex-direction: column;
}

.order-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-lg);
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--border-light);
    transition: background var(--transition-fast);
}

.order-item:hover {
    background: var(--bg-secondary);
}

.order-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-full);
    background: rgba(78, 205, 196, 0.1);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
}

.order-info {
    flex: 1;
}

.order-patient {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 4px;
}

.order-number {
    font-family: monospace;
    font-size: var(--font-size-xs);
    background: var(--bg-secondary);
    padding: 2px 6px;
    border-radius: var(--radius-sm);
    color: var(--text-muted);
    font-weight: normal;
}

.order-test {
    font-size: var(--font-size-md);
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.result-badge {
    font-size: var(--font-size-sm);
    padding: 2px 8px;
    border-radius: var(--radius-full);
    background: var(--bg-secondary);
}

.result-badge.status-high,
.result-badge.status-low,
.result-badge.status-abnormal {
    background: rgba(255, 107, 107, 0.15);
    color: #E85555;
}

.result-badge.status-normal {
    background: rgba(0, 217, 165, 0.15);
    color: var(--success);
}

.order-meta {
    font-size: var(--font-size-sm);
    color: var(--text-muted);
    margin-top: 4px;
}

.order-actions {
    display: flex;
    gap: var(--spacing-sm);
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

.form-row {
    display: flex;
    gap: var(--spacing-md);
}

.form-group.col-8 { flex: 2; }
.form-group.col-4 { flex: 1; }

.empty-state {
    text-align: center;
    padding: var(--spacing-2xl);
    color: var(--text-muted);
}

.empty-state svg {
    opacity: 0.3;
    margin-bottom: var(--spacing-lg);
}
</style>

<script>
// مودال طلب جديد
function showNewLabOrderModal() {
    document.getElementById('patientSearch').value = '';
    document.getElementById('selectedPatientId').value = '';
    document.getElementById('selectedPatientInfo').style.display = 'none';
    document.getElementById('labTest').value = '';
    document.getElementById('orderNotes').value = '';
    document.getElementById('newOrderModal').classList.add('show');
}

function closeNewOrderModal() {
    document.getElementById('newOrderModal').classList.remove('show');
}

// البحث عن مريض
let searchTimeout;
document.getElementById('patientSearch').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value;
    
    if (query.length < 2) {
        document.getElementById('patientResults').classList.remove('show');
        return;
    }
    
    searchTimeout = setTimeout(() => {
        fetch('<?= url('prescriptions/search-patient') ?>?q=' + encodeURIComponent(query), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            const results = document.getElementById('patientResults');
            if (data.results.length === 0) {
                results.innerHTML = '<div class="search-result-item">لا توجد نتائج</div>';
            } else {
                results.innerHTML = data.results.map(p => 
                    `<div class="search-result-item" onclick="selectPatient(${p.id}, '${p.full_name}')">
                        <strong>${p.full_name}</strong> - ${p.electronic_number}
                    </div>`
                ).join('');
            }
            results.classList.add('show');
        });
    }, 300);
});

function selectPatient(id, name) {
    document.getElementById('selectedPatientId').value = id;
    document.getElementById('selectedPatientName').textContent = name;
    document.getElementById('selectedPatientInfo').style.display = 'flex';
    document.getElementById('patientSearch').style.display = 'none';
    document.getElementById('patientResults').classList.remove('show');
}

function clearPatient() {
    document.getElementById('selectedPatientId').value = '';
    document.getElementById('selectedPatientInfo').style.display = 'none';
    document.getElementById('patientSearch').style.display = 'block';
    document.getElementById('patientSearch').value = '';
}

function saveOrder() {
    const patientId = document.getElementById('selectedPatientId').value;
    const testId = document.getElementById('labTest').value;
    
    if (!patientId || !testId) {
        notify.error('يرجى اختيار المريض والتحليل');
        return;
    }
    
    const formData = new FormData();
    formData.append('patient_id', patientId);
    formData.append('test_id', testId);
    formData.append('notes', document.getElementById('orderNotes').value);
    
    fetch('<?= url('prescriptions/lab-order') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            notify.success(data.message);
            closeNewOrderModal();
            location.reload();
        } else {
            notify.error(data.error);
        }
    });
}

// مودال النتائج
function showResultModal(orderId, testName, patientName) {
    document.getElementById('resultOrderId').value = orderId;
    document.getElementById('resultInfoText').textContent = `${testName} - ${patientName}`;
    document.getElementById('resultValue').value = '';
    document.getElementById('resultStatus').value = 'normal';
    document.getElementById('resultDetails').value = '';
    document.getElementById('resultModal').classList.add('show');
}

function closeResultModal() {
    document.getElementById('resultModal').classList.remove('show');
}

function saveResult() {
    const formData = new FormData();
    formData.append('order_id', document.getElementById('resultOrderId').value);
    formData.append('result_value', document.getElementById('resultValue').value);
    formData.append('result_status', document.getElementById('resultStatus').value);
    formData.append('result', document.getElementById('resultDetails').value);
    
    const attachment = document.getElementById('resultAttachment').files[0];
    if (attachment) {
        formData.append('attachment', attachment);
    }
    
    fetch('<?= url('prescriptions/lab-result') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            notify.success(data.message);
            closeResultModal();
            location.reload();
        } else {
            notify.error(data.error);
        }
    });
}

function printReport(id) {
    window.open('<?= url('print/lab-result') ?>?id=' + id, '_blank', 'width=400,height=600');
}

function filterByDate() {
    const date = document.getElementById('filterDate').value;
    const status = document.getElementById('filterStatus').value;
    window.location.href = `<?= url('prescriptions/lab') ?>?date=${date}&filter=${status}`;
}

function filterByStatus() {
    filterByDate();
}
</script>

<?php
$content = ob_get_clean();
require VIEWS_PATH . 'layouts/main.php';
?>
