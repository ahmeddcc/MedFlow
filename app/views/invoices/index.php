<?php
$pageTitle = 'الفواتير';
ob_start();
?>

<div class="invoices-page">
    <!-- الإحصائيات -->
    <div class="invoice-stats">
        <div class="stat-card stat-total">
            <div class="stat-number"><?= number_format($stats['total_amount'], 2) ?></div>
            <div class="stat-label">إجمالي اليوم</div>
        </div>
        <div class="stat-card stat-paid">
            <div class="stat-number"><?= number_format($stats['total_paid'], 2) ?></div>
            <div class="stat-label">المحصّل</div>
        </div>
        <div class="stat-card stat-pending">
            <div class="stat-number"><?= $stats['pending'] ?></div>
            <div class="stat-label">معلق</div>
        </div>
        <div class="stat-card stat-count">
            <div class="stat-number"><?= $stats['total_invoices'] ?></div>
            <div class="stat-label">فواتير اليوم</div>
        </div>
    </div>
    
    <!-- شريط التحكم -->
    <div class="invoice-controls">
        <div class="controls-right">
            <button class="btn btn-primary" onclick="showNewInvoiceModal()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                فاتورة جديدة
            </button>
        </div>
        <div class="controls-left">
            <input type="date" class="form-control" id="filterDate" value="<?= date('Y-m-d') ?>" onchange="filterByDate()">
            <select class="form-control" id="filterStatus" onchange="filterByStatus()">
                <option value="all">الكل</option>
                <option value="pending">معلق</option>
                <option value="partial">جزئي</option>
                <option value="paid">مسدد</option>
            </select>
        </div>
    </div>
    
    <!-- قائمة الفواتير -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">فواتير اليوم</h2>
        </div>
        <div class="card-body">
            <?php if (empty($invoices)): ?>
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="64" height="64">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                <p>لا توجد فواتير</p>
            </div>
            <?php else: ?>
            <div class="invoices-list">
                <?php foreach ($invoices as $invoice): ?>
                <div class="invoice-item status-<?= $invoice['status'] ?>">
                    <div class="invoice-number"><?= $invoice['invoice_number'] ?></div>
                    <div class="invoice-info">
                        <div class="invoice-patient"><?= $invoice['patient_name'] ?></div>
                        <div class="invoice-meta">
                            <span><?= $invoice['electronic_number'] ?></span>
                            <span><?= date('h:i A', strtotime($invoice['created_at'])) ?></span>
                        </div>
                    </div>
                    <div class="invoice-amounts">
                        <div class="invoice-total"><?= number_format($invoice['total'], 2) ?> ج.م</div>
                        <?php if ($invoice['remaining'] > 0): ?>
                        <div class="invoice-remaining">متبقي: <?= number_format($invoice['remaining'], 2) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="invoice-status">
                        <span class="status-badge status-<?= $invoice['status'] ?>">
                            <?php 
                            $statusText = [
                                'pending' => 'معلق',
                                'partial' => 'جزئي',
                                'paid' => 'مسدد',
                                'cancelled' => 'ملغي'
                            ];
                            echo $statusText[$invoice['status']] ?? $invoice['status'];
                            ?>
                        </span>
                    </div>
                    <div class="invoice-actions">
                        <button class="btn btn-ghost btn-sm" onclick="printInvoice(<?= $invoice['id'] ?>)" title="طباعة">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                <rect x="6" y="14" width="12" height="8"></rect>
                            </svg>
                        </button>
                        <?php if ($invoice['status'] !== 'paid' && $invoice['status'] !== 'cancelled'): ?>
                        <button class="btn btn-success btn-sm" onclick="showPaymentModal(<?= $invoice['id'] ?>, <?= $invoice['remaining'] ?>)" title="تسجيل دفعة">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
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

<!-- مودال فاتورة جديدة -->
<div class="modal-overlay" id="newInvoiceModal">
    <div class="modal" style="max-width: 700px;">
        <div class="modal-header">
            <h3>فاتورة جديدة</h3>
            <button type="button" class="modal-close" onclick="closeNewInvoiceModal()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <!-- البحث عن المريض -->
            <div class="form-group">
                <label class="form-label">المريض <span class="required">*</span></label>
                <input type="text" class="form-control" id="patientSearch" placeholder="ابحث باسم المريض أو الرقم..." autocomplete="off">
                <input type="hidden" id="selectedPatientId">
                <div class="search-results" id="patientResults"></div>
                <div class="selected-patient" id="selectedPatientInfo" style="display: none;">
                    <span id="selectedPatientName"></span>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="clearPatient()">✕</button>
                </div>
            </div>
            
            <!-- الخدمات -->
            <div class="form-group">
                <label class="form-label">الخدمات</label>
                <div class="services-grid" id="servicesGrid">
                    <?php foreach ($services as $service): ?>
                    <button type="button" class="service-btn" onclick="addService(<?= $service['id'] ?>, '<?= addslashes($service['name']) ?>', <?= $service['price'] ?>)">
                        <?= $service['name'] ?>
                        <small><?= number_format($service['price'], 2) ?></small>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- البنود -->
            <div class="invoice-items-container">
                <table class="invoice-items-table" id="invoiceItemsTable">
                    <thead>
                        <tr>
                            <th>الخدمة</th>
                            <th style="width: 80px;">الكمية</th>
                            <th style="width: 100px;">السعر</th>
                            <th style="width: 100px;">الإجمالي</th>
                            <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="invoiceItems"></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: left;">الإجمالي الفرعي:</td>
                            <td colspan="2"><strong id="subtotalDisplay">0.00</strong> ج.م</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: left;">الخصم:</td>
                            <td>
                                <input type="number" class="form-control form-control-sm" id="discountAmount" value="0" min="0" onchange="calculateTotal()">
                            </td>
                            <td colspan="2">
                                <select class="form-control form-control-sm" id="discountType" onchange="calculateTotal()">
                                    <option value="fixed">ج.م</option>
                                    <option value="percent">%</option>
                                </select>
                            </td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="3" style="text-align: left;">الإجمالي:</td>
                            <td colspan="2"><strong id="totalDisplay">0.00</strong> ج.م</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeNewInvoiceModal()">إلغاء</button>
            <button type="button" class="btn btn-primary" onclick="saveInvoice()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                حفظ الفاتورة
            </button>
        </div>
    </div>
</div>

<!-- مودال الدفع -->
<div class="modal-overlay" id="paymentModal">
    <div class="modal" style="max-width: 400px;">
        <div class="modal-header">
            <h3>تسجيل دفعة</h3>
            <button type="button" class="modal-close" onclick="closePaymentModal()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="paymentInvoiceId">
            
            <div class="form-group">
                <label class="form-label">المبلغ <span class="required">*</span></label>
                <input type="number" class="form-control" id="paymentAmount" step="0.01" min="0">
                <small class="form-text">المتبقي: <span id="remainingDisplay">0</span> ج.م</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">طريقة الدفع</label>
                <select class="form-control" id="paymentMethod">
                    <option value="cash">نقدي</option>
                    <option value="card">بطاقة</option>
                    <option value="transfer">تحويل</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">ملاحظات</label>
                <textarea class="form-control" id="paymentNotes" rows="2"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closePaymentModal()">إلغاء</button>
            <button type="button" class="btn btn-success" onclick="savePayment()">تسجيل الدفعة</button>
        </div>
    </div>
</div>

<style>
.invoices-page {
    max-width: 1200px;
    margin: 0 auto;
}

.invoice-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-xl);
}

.invoice-stats .stat-card {
    text-align: center;
    padding: var(--spacing-lg);
    border-radius: var(--radius-lg);
    background: var(--bg-card);
    box-shadow: var(--shadow-card);
}

.stat-total { border-top: 3px solid var(--primary); }
.stat-paid { border-top: 3px solid var(--success); }
.stat-pending { border-top: 3px solid var(--warning); }
.stat-count { border-top: 3px solid var(--info); }

.stat-number {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text-primary);
}

.stat-label {
    font-size: var(--font-size-sm);
    color: var(--text-muted);
}

.invoice-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-xl);
    flex-wrap: wrap;
    gap: var(--spacing-md);
}

.controls-left {
    display: flex;
    gap: var(--spacing-sm);
}

.invoices-list {
    display: flex;
    flex-direction: column;
}

.invoice-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-lg);
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--border-light);
    transition: background var(--transition-fast);
}

.invoice-item:hover {
    background: var(--bg-secondary);
}

.invoice-item:last-child {
    border-bottom: none;
}

.invoice-number {
    font-family: monospace;
    font-size: var(--font-size-sm);
    padding: 4px 8px;
    background: var(--bg-secondary);
    border-radius: var(--radius-sm);
    color: var(--text-muted);
}

.invoice-info {
    flex: 1;
}

.invoice-patient {
    font-weight: 600;
    color: var(--text-primary);
}

.invoice-meta {
    display: flex;
    gap: var(--spacing-md);
    font-size: var(--font-size-sm);
    color: var(--text-muted);
}

.invoice-amounts {
    text-align: left;
}

.invoice-total {
    font-size: var(--font-size-lg);
    font-weight: 700;
    color: var(--primary);
}

.invoice-remaining {
    font-size: var(--font-size-sm);
    color: var(--warning);
}

.invoice-actions {
    display: flex;
    gap: var(--spacing-xs);
}

.status-badge.status-pending { background: rgba(255, 165, 2, 0.15); color: #CC8400; }
.status-badge.status-partial { background: rgba(78, 205, 196, 0.15); color: var(--primary); }
.status-badge.status-paid { background: rgba(0, 217, 165, 0.15); color: var(--success); }
.status-badge.status-cancelled { background: rgba(255, 107, 107, 0.15); color: #E85555; }

/* مودال الفاتورة */
.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: var(--spacing-sm);
    margin-top: var(--spacing-sm);
}

.service-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: var(--spacing-md);
    background: var(--bg-secondary);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: var(--font-size-sm);
    font-weight: 600;
}

.service-btn:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.service-btn small {
    font-weight: 400;
    opacity: 0.8;
}

.invoice-items-container {
    margin-top: var(--spacing-lg);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-md);
    overflow: hidden;
}

.invoice-items-table {
    width: 100%;
    border-collapse: collapse;
}

.invoice-items-table th,
.invoice-items-table td {
    padding: var(--spacing-sm) var(--spacing-md);
    text-align: right;
    border-bottom: 1px solid var(--border-light);
}

.invoice-items-table th {
    background: var(--bg-secondary);
    font-weight: 600;
}

.invoice-items-table tfoot tr:last-child {
    background: rgba(78, 205, 196, 0.1);
}

.total-row td {
    font-size: var(--font-size-lg);
    color: var(--primary);
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

@media (max-width: 768px) {
    .invoice-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .invoice-item {
        flex-wrap: wrap;
    }
}
</style>

<script>
let invoiceItems = [];
let itemIdCounter = 0;

// مودال الفاتورة الجديدة
function showNewInvoiceModal() {
    invoiceItems = [];
    renderItems();
    document.getElementById('patientSearch').value = '';
    document.getElementById('selectedPatientId').value = '';
    document.getElementById('selectedPatientInfo').style.display = 'none';
    document.getElementById('discountAmount').value = 0;
    document.getElementById('newInvoiceModal').classList.add('show');
}

function closeNewInvoiceModal() {
    document.getElementById('newInvoiceModal').classList.remove('show');
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
        fetch('<?= url('invoices/search-patient') ?>?q=' + encodeURIComponent(query), {
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

// إضافة خدمة
function addService(serviceId, name, price) {
    invoiceItems.push({
        id: ++itemIdCounter,
        service_id: serviceId,
        description: name,
        quantity: 1,
        price: price
    });
    renderItems();
}

function renderItems() {
    const tbody = document.getElementById('invoiceItems');
    tbody.innerHTML = invoiceItems.map(item => `
        <tr>
            <td>${item.description}</td>
            <td><input type="number" class="form-control form-control-sm" value="${item.quantity}" min="1" onchange="updateQuantity(${item.id}, this.value)"></td>
            <td>${item.price.toFixed(2)}</td>
            <td>${(item.quantity * item.price).toFixed(2)}</td>
            <td><button type="button" class="btn btn-ghost btn-sm" onclick="removeItem(${item.id})" style="color: var(--danger);">✕</button></td>
        </tr>
    `).join('');
    calculateTotal();
}

function updateQuantity(id, qty) {
    const item = invoiceItems.find(i => i.id === id);
    if (item) {
        item.quantity = parseInt(qty) || 1;
        renderItems();
    }
}

function removeItem(id) {
    invoiceItems = invoiceItems.filter(i => i.id !== id);
    renderItems();
}

function calculateTotal() {
    const subtotal = invoiceItems.reduce((sum, item) => sum + (item.quantity * item.price), 0);
    const discount = parseFloat(document.getElementById('discountAmount').value) || 0;
    const discountType = document.getElementById('discountType').value;
    
    const discountAmount = discountType === 'percent' ? (subtotal * discount / 100) : discount;
    const total = Math.max(0, subtotal - discountAmount);
    
    document.getElementById('subtotalDisplay').textContent = subtotal.toFixed(2);
    document.getElementById('totalDisplay').textContent = total.toFixed(2);
}

function saveInvoice() {
    const patientId = document.getElementById('selectedPatientId').value;
    
    if (!patientId) {
        notify.error('يرجى اختيار المريض');
        return;
    }
    
    if (invoiceItems.length === 0) {
        notify.error('يرجى إضافة خدمة واحدة على الأقل');
        return;
    }
    
    const formData = new FormData();
    formData.append('patient_id', patientId);
    formData.append('items', JSON.stringify(invoiceItems));
    formData.append('discount', document.getElementById('discountAmount').value);
    formData.append('discount_type', document.getElementById('discountType').value);
    
    fetch('<?= url('invoices/create') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            notify.success(data.message);
            closeNewInvoiceModal();
            location.reload();
        } else {
            notify.error(data.error);
        }
    });
}

// مودال الدفع
function showPaymentModal(invoiceId, remaining) {
    document.getElementById('paymentInvoiceId').value = invoiceId;
    document.getElementById('paymentAmount').value = remaining;
    document.getElementById('paymentAmount').max = remaining;
    document.getElementById('remainingDisplay').textContent = remaining.toFixed(2);
    document.getElementById('paymentNotes').value = '';
    document.getElementById('paymentModal').classList.add('show');
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.remove('show');
}

function savePayment() {
    const formData = new FormData();
    formData.append('invoice_id', document.getElementById('paymentInvoiceId').value);
    formData.append('amount', document.getElementById('paymentAmount').value);
    formData.append('method', document.getElementById('paymentMethod').value);
    formData.append('notes', document.getElementById('paymentNotes').value);
    
    fetch('<?= url('invoices/add-payment') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            notify.success(data.message);
            closePaymentModal();
            location.reload();
        } else {
            notify.error(data.error);
        }
    });
}

function printInvoice(id) {
    window.open('<?= url('print/invoice') ?>?id=' + id, '_blank', 'width=400,height=600');
}

function filterByDate() {
    const date = document.getElementById('filterDate').value;
    window.location.href = '<?= url('invoices') ?>?date=' + date;
}

function filterByStatus() {
    const status = document.getElementById('filterStatus').value;
    const date = document.getElementById('filterDate').value;
    window.location.href = '<?= url('invoices') ?>?date=' + date + '&filter=' + status;
}
</script>

<?php
$content = ob_get_clean();
require VIEWS_PATH . 'layouts/main.php';
?>
