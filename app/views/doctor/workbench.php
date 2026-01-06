<?php
$pageTitle = "مكتب الطبيب";
ob_start();
?>

<div class="workbench-container">
    
    <!-- Column 1: Queue -->
    <div class="wb-col wb-queue">
        <div class="wb-card">
            <div class="wb-header">
                <h3>قائمة الانتظار</h3>
                <div style="display:flex; gap:10px; align-items:center;">
                    <button class="btn btn-sm btn-danger pulse-animation" onclick="summonAssistant()" title="استدعاء مساعد">
                        <i class="fas fa-bell"></i>
                    </button>
                    <span class="badge badge-info"><?= count($waitingList) ?></span>
                </div>
            </div>
            
            <div class="queue-actions">
                <button class="btn btn-success btn-block" onclick="callNext()">
                    <i class="fas fa-bullhorn"></i> نداء التالي
                </button>
            </div>
            
            <div class="queue-list">
                <?php if (empty($waitingList)): ?>
                    <div class="empty-state">لا يوجد مرضى في الانتظار</div>
                <?php else: ?>
                    <?php foreach ($waitingList as $patient): ?>
                        <div class="queue-item status-<?= $patient['status'] ?>">
                            <div class="q-number"><?= $patient['turn_number'] ?></div>
                            <div class="q-info">
                                <div class="q-name"><?= $patient['full_name'] ?></div>
                                <div class="q-time"><?= date('h:i A', strtotime($patient['created_at'])) ?></div>
                            </div>
                            <?php if ($patient['status'] === 'called'): ?>
                                <button class="btn btn-sm btn-primary" onclick="enterPatient(<?= $patient['id'] ?>)">دخول</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Column 2: Workspace (Active Patient) -->
    <div class="wb-col wb-workspace">
        <?php if ($currentVisit): ?>
            <div class="wb-patient-header">
                <div class="p-avatar"><?= mb_substr($currentVisit['full_name'], 0, 1) ?></div>
                <div class="p-details">
                    <h1><?= $currentVisit['full_name'] ?></h1>
                    <div class="p-meta">
                        <span><i class="fas fa-file-medical"></i> <?= $currentVisit['file_number'] ?? 'جديد' ?></span>
                        <span><i class="fas fa-venus-mars"></i> <?= $currentVisit['gender'] == 'male' ? 'ذكر' : 'أنثى' ?></span>
                        <span><i class="fas fa-birthday-cake"></i> <?= $currentVisit['birth_date'] ?></span>
                    </div>
                </div>
                <div class="p-actions">
                    <button class="btn btn-warning ml-2" onclick="openTransferModal(<?= $currentVisit['id'] ?>)">
                        <i class="fas fa-exchange-alt"></i> تحويل / تعليق
                    </button>
                    <button class="btn btn-danger" onclick="finishVisit(<?= $currentVisit['id'] ?>)">
                        <i class="fas fa-check"></i> إنهاء الكشف
                    </button>
                </div>
            </div>

            <!-- Transfer Modal -->
            <div id="transferModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
                <div class="modal-content" style="background:white; padding:20px; border-radius:12px; width:400px; max-width:90%;">
                    <h3>🔄 تحويل المريض</h3>
                    <p>اختر وجهة التحويل:</p>
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-warning btn-block mb-2" onclick="transferPatient('hold')">
                            ✋ <b>تعليق (خروج مؤقت)</b><br>
                            <small>يعود لقائمة الانتظار بأولوية</small>
                        </button>
                        <button class="btn btn-outline-info btn-block mb-2" onclick="transferPatient('reception')">
                            🧾 <b>الاستقبال / الحسابات</b><br>
                            <small>إنهاء الكشف والتحويل للدفع</small>
                        </button>
                        <button class="btn btn-secondary btn-block" onclick="closeTransferModal()">إلغاء</button>
                    </div>
                </div>
            </div>

            <div class="wb-tabs">
                <button class="wb-tab active" onclick="switchTab('diagnosis')">التشخيص</button>
                <button class="wb-tab" onclick="switchTab('rx')">الوصفة</button>
                <button class="wb-tab" onclick="switchTab('vitals')">العلامات الحيوية</button>
            </div>

            <div class="wb-content">
                <div id="tab-diagnosis" class="tab-pane active">
                    <form id="diagnosisForm">
                        <input type="hidden" name="visit_id" value="<?= $currentVisit['id'] ?>">
                        <div class="form-group">
                            <label>الشكوى الرئيسية / التشخيص</label>
                            <textarea name="diagnosis" class="form-control" rows="5" placeholder="اكتب التشخيص هنا..." onblur="saveDraft()"><?= $currentVisit['diagnosis'] ?? '' ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>ملاحظات الطبيب (خاصة)</label>
                            <textarea name="doctor_notes" class="form-control" rows="3" placeholder="ملاحظات خاصة..." onblur="saveDraft()"><?= $currentVisit['doctor_notes'] ?? '' ?></textarea>
                        </div>
                    </form>
                </div>

                <div id="tab-rx" class="tab-pane">
                    <div class="rx-placeholder">
                        <p>قائمة الأدوية هنا...</p>
                        <a href="<?= url('prescriptions/create?patient_id=' . $currentVisit['patient_id']) ?>" target="_blank" class="btn btn-secondary">
                             فتح نموذج الوصفة الكامل
                        </a>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="no-patient-selected">
                <i class="fas fa-user-md fa-3x"></i>
                <h2>لا يوجد مريض حالي</h2>
                <p>قم باستدعاء مريض من القائمة للبدء</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Column 3: History -->
    <div class="wb-col wb-history">
        <div class="wb-card">
            <div class="wb-header">
                <h3>السجل المرضي</h3>
            </div>
            <div class="history-list">
                <?php if (!empty($history)): ?>
                    <?php foreach ($history as $record): ?>
                        <div class="history-item">
                            <div class="h-date"><?= date('Y-m-d', strtotime($record['created_at'])) ?></div>
                            <div class="h-desc">زيارة #<?= $record['id'] ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state small">لا يوجد سجلات سابقة</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.workbench-container {
    display: flex;
    gap: 20px;
    height: calc(100vh - 100px);
    overflow: hidden;
}

.wb-col {
    display: flex;
    flex-direction: column;
}

.wb-queue { flex: 0 0 250px; }
.wb-workspace { flex: 1; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; flex-direction: column; overflow: hidden; }
.wb-history { flex: 0 0 250px; }

.wb-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.wb-header {
    padding: 15px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.queue-actions { padding: 10px; }
.queue-list { flex: 1; overflow-y: auto; padding: 10px; }

.queue-item {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-left: 4px solid transparent;
}

.queue-item.status-called { border-left-color: var(--warning); background-color: #fff8e1; }
.queue-item.status-waiting { border-left-color: var(--secondary); }

.q-number {
    font-size: 1.2rem;
    font-weight: bold;
    background: #e9ecef;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.q-info { flex: 1; overflow: hidden; }
.q-name { font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.q-time { font-size: 0.8rem; color: #888; }

.wb-patient-header {
    padding: 20px;
    background: linear-gradient(to left, #f8f9fa, #fff);
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: center;
    gap: 15px;
}

.p-avatar {
    width: 60px;
    height: 60px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
}

.p-details h1 { margin: 0; font-size: 1.4rem; color: var(--text-primary); }
.p-meta { display: flex; gap: 15px; margin-top: 5px; color: #666; font-size: 0.9rem; }
.p-actions { margin-right: auto; }

.wb-tabs {
    display: flex;
    border-bottom: 1px solid #eee;
    padding: 0 20px;
}

.wb-tab {
    padding: 15px 20px;
    background: none;
    border: none;
    cursor: pointer;
    font-weight: 600;
    color: #666;
    border-bottom: 2px solid transparent;
}

.wb-tab.active { color: var(--primary); border-bottom-color: var(--primary); }

.wb-content { flex: 1; padding: 20px; overflow-y: auto; }
.tab-pane { display: none; }
.tab-pane.active { display: block; }

.no-patient-selected {
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #ccc;
}

.history-list { padding: 15px; flex: 1; overflow-y: auto; }
.history-item {
    border-left: 2px solid #ddd;
    padding-left: 15px;
    position: relative;
    margin-bottom: 20px;
}
.history-item::before {
    content: '';
    position: absolute;
    left: -6px;
    top: 0;
    width: 10px;
    height: 10px;
    background: #ddd;
    border-radius: 50%;
}
.h-date { font-weight: bold; font-size: 0.9rem; }
.h-desc { color: #666; font-size: 0.9rem; }

</style>

<script>
function callNext() {
    fetch('<?= url('waiting-list/call-next') ?>', {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) location.reload();
        else alert(data.error || 'لا يوجد مرضى');
    });
}

function enterPatient(id) {
    fetch('<?= url('waiting-list/enter/') ?>' + id, {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) location.reload();
    });
}

function finishVisit(id) {
    if(!confirm('إنهاء الكشف وحفظ البيانات؟')) return;
    
    // Save first
    saveDraft().then(() => {
        fetch('<?= url('waiting-list/complete/') ?>' + id, {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) location.reload();
        });
    });
}

function switchTab(tabId) {
    document.querySelectorAll('.wb-tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`.wb-tab[onclick="switchTab('${tabId}')"]`).classList.add('active');
    
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.getElementById('tab-' + tabId).classList.add('active');
}

function saveDraft() {
    const form = document.getElementById('diagnosisForm');
    if(!form) return Promise.resolve();
    
    const formData = new FormData(form);
    
    return fetch('<?= url('doctor/saveNotes') ?>', {
        method: 'POST',
        body: formData
    }).then(r => r.json());
}
</script>

<style>
@keyframes pulse-red {
    0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}
.pulse-animation {
    animation: pulse-red 2s infinite;
}
.d-grid { display: grid; }
.gap-2 { gap: 0.5rem; }
</style>

<script>
let currentVisitId = <?= $currentVisit['id'] ?? 0 ?>;

function summonAssistant() {
    if(!confirm('هل تود استدعاء المساعدة فعلاً؟ سيتم إرسال تنبيه للمشرفين.')) return;
    
    fetch('<?= url('doctor/summonAssistant') ?>', {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) alert('✅ ' + data.message);
        else alert('❌ حدث خطأ أثناء الإرسال');
    });
}

function openTransferModal(id) {
    currentVisitId = id;
    document.getElementById('transferModal').style.display = 'flex';
}

function closeTransferModal() {
    document.getElementById('transferModal').style.display = 'none';
}

function transferPatient(action) {
    if(!confirm('تأكيد الإجراء؟')) return;
    
    // Save drafts first if needed
    saveDraft().then(() => {
        const formData = new FormData();
        formData.append('visit_id', currentVisitId);
        formData.append('action', action);
        
        fetch('<?= url('doctor/transferPatient') ?>', {
            method: 'POST',
            body: formData,
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert('خطأ: ' + (data.error || 'فشلت العملية'));
            }
        });
    });
}


<?php
$content = ob_get_clean();
require VIEWS_PATH . 'layouts/main.php';
?>
