<?php
$pageTitle = $patient['full_name'];
ob_start();
?>

<div class="patient-show-page">
    <!-- الهيدر -->
    <div class="patient-header-card">
        <div class="patient-header-bg"></div>
        <div class="patient-header-content">
            <div class="patient-avatar-large">
                <?= mb_substr($patient['full_name'], 0, 1, 'UTF-8') ?>
            </div>
            <div class="patient-header-info">
                <h1 class="patient-header-name"><?= $patient['full_name'] ?></h1>
                <div class="patient-header-badges">
                    <span class="badge badge-primary">ID: <?= $patient['electronic_number'] ?></span>
                    <span class="badge badge-info"><?= $patient['barcode'] ?></span>
                    <?php if ($patient['gender']): ?>
                    <span class="badge <?= $patient['gender'] === 'male' ? 'badge-info' : 'badge-warning' ?>">
                        <?= $patient['gender'] === 'male' ? __('male') : __('female') ?>
                    </span>
                    <?php endif; ?>
                    <span class="badge badge-secondary"><?= $patient['age'] ?> سنة</span>
                </div>
            </div>
            <div class="patient-header-actions">
                <?php if ($permissions['can_edit_patient']): ?>
                <a href="<?= url('patients/' . $patient['id'] . '/edit') ?>" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    <?= __('edit') ?>
                </a>
                <?php endif; ?>
                
                <button type="button" class="btn btn-primary" onclick="addToWaitingList(<?= $patient['id'] ?>)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    حجز كشف
                </button>
            </div>
        </div>
    </div>
    
    <div class="patient-content-grid">
        <!-- القائمة الجانبية (معلومات + مرفقات) -->
        <div class="patient-sidebar">
            <!-- كارت المعلومات basic info -->
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">معلومات الاتصال</h3></div>
                <div class="card-body p-0">
                    <div class="info-list">
                        <div class="info-item">
                            <span class="label">الهاتف</span>
                            <span class="value" dir="ltr"><?= $patient['phone'] ?: '-' ?></span>
                        </div>
                        <?php if($patient['secondary_phone']): ?>
                        <div class="info-item">
                            <span class="label">هاتف 2</span>
                            <span class="value" dir="ltr"><?= $patient['secondary_phone'] ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="info-item">
                            <span class="label">العنوان</span>
                            <span class="value"><?= $patient['address'] ?: '-' ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- كارت الملاحظات -->
            <?php if ($patient['notes']): ?>
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">ملاحظات هامة</h3></div>
                <div class="card-body">
                    <p class="text-muted mb-0"><?= nl2br($patient['notes']) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- كارت التاريخ الطبي -->
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">التاريخ الطبي</h3></div>
                <div class="card-body">
                    <p class="mb-0"><?= nl2br($patient['medical_history'] ?: 'لا يوجد سجل طبي') ?></p>
                </div>
            </div>
        </div>

        <!-- التايم لاين (Timeline) -->
        <div class="patient-timeline-area">
            
            <!-- الزيارة النشطة -->
            <?php if ($activeVisit): ?>
            <div class="active-visit-card mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h3 class="m-0 text-primary">زيارة حالية (دور رقم <?= $activeVisit['turn_number'] ?>)</h3>
                    <span class="badge badge-warning"><?= __($activeVisit['status']) ?></span>
                </div>
                <div class="visit-meta text-muted">
                    <small>وقت الحضور: <?= date('h:i A', strtotime($activeVisit['created_at'])) ?></small>
                </div>
                <?php if($role == 'doctor' && $activeVisit['status'] == 'entered'): ?>
                <div class="mt-3">
                    <a href="<?= url('doctor') ?>" class="btn btn-sm btn-primary">الذهاب للكشف</a>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- إضافة مرفق سريع -->
            <?php if ($permissions['can_add_attachment']): ?>
            <div class="card mb-4 p-3 dashed-border cursor-pointer hover-bg-light" onclick="document.getElementById('newAttachment').click()">
                <div class="d-flex align-items-center justify-content-center gap-2 text-muted">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                    <span>إضافة مرفق جديد (صورة، تحليل، أشعة)</span>
                </div>
                <form id="uploadForm" class="d-none">
                    <input type="file" id="newAttachment" name="attachments[]" multiple onchange="uploadFiles(this)">
                </form>
            </div>
            <?php endif; ?>

            <!-- التايم لاين -->
            <div class="timeline-container">
                <?php if (empty($timeline)): ?>
                    <div class="empty-state text-center py-5">
                        <p class="text-muted">لا يوجد سجلات سابقة للمريض</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($timeline as $date => $events): ?>
                    <div class="timeline-group">
                        <div class="timeline-date">
                            <span><?= formatDateArabic($date) ?></span>
                        </div>
                        
                        <?php foreach ($events as $event): ?>
                            <!-- كارت الزيارة -->
                            <?php if ($event['type'] == 'visit'): ?>
                            <div class="timeline-item type-visit">
                                <div class="timeline-icon bg-primary-soft text-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between">
                                        <h4>زيارة عيادة</h4>
                                        <span class="time"><?= date('h:i A', strtotime($event['created_at'])) ?></span>
                                    </div>
                                    <?php if ($event['doctor_notes']): ?>
                                        <p class="mb-0 mt-2 text-dark bg-light p-2 rounded"><?= $event['doctor_notes'] ?></p>
                                    <?php else: ?>
                                        <p class="text-muted mb-0">لا توجد ملاحظات طبية</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- كارت المرفق -->
                            <?php elseif ($event['type'] == 'attachment'): ?>
                            <div class="timeline-item type-attachment" id="att-<?= $event['id'] ?>">
                                <div class="timeline-icon bg-info-soft text-info">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h4 class="mb-1">مرفق جديد</h4>
                                            <a href="<?= url('uploads/' . $event['file_path']) ?>" target="_blank" class="text-primary text-decoration-none">
                                                <?= $event['file_name'] ?>
                                            </a>
                                        </div>
                                        <?php if ($permissions['can_delete_attachment']): ?>
                                        <button class="btn btn-sm btn-ghost text-danger" onclick="deleteAttachment(<?= $event['id'] ?>)">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- كارت الفاتورة -->
                            <?php elseif ($event['type'] == 'invoice'): ?>
                            <div class="timeline-item type-invoice">
                                <div class="timeline-icon bg-success-soft text-success">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between">
                                        <h4>فاتورة #<?= $event['invoice_number'] ?></h4>
                                        <span class="badge badge-<?= $event['status'] == 'paid' ? 'success' : 'warning' ?>">
                                            <?= $event['status'] == 'paid' ? 'مدفوع' : 'معلق' ?>
                                        </span>
                                    </div>
                                    <p class="mb-0 font-weight-bold"><?= number_format($event['total']) ?> ج.م</p>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.patient-content-grid {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 1.5rem;
    margin-top: -20px;
    padding: 0 1.5rem 1.5rem;
}

/* Sidebar Styles */
.patient-sidebar .info-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--border-light);
    display: flex;
    justify-content: space-between;
}
.patient-sidebar .info-item:last-child { border-bottom: none; }
.patient-sidebar .label { color: var(--text-muted); font-size: 0.9em; }
.patient-sidebar .value { font-weight: 500; }

/* Timeline Styles */
.timeline-container { position: relative; }
.timeline-container::before {
    content: '';
    position: absolute;
    right: 24px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--border-light);
}

.timeline-date {
    margin: 1.5rem 0 1rem;
    position: relative;
    padding-right: 3rem;
    font-weight: bold;
    color: var(--text-muted);
}
.timeline-date::before {
    content: '';
    position: absolute;
    right: 20px;
    top: 50%;
    width: 10px;
    height: 10px;
    background: var(--border-light);
    border-radius: 50%;
    transform: translateY(-50%);
    border: 2px solid white;
}

.timeline-item {
    position: relative;
    padding-right: 3.5rem;
    margin-bottom: 1rem;
}

.timeline-icon {
    position: absolute;
    right: 15px;
    top: 0;
    width: 36px;
    height: 36px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    z-index: 1;
}

.timeline-content {
    background: white;
    border-radius: var(--radius-md);
    padding: 1rem;
    border: 1px solid var(--border-light);
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
}

.dashed-border {
    border: 2px dashed var(--border-light);
    transition: all 0.2s;
}
.dashed-border:hover {
    border-color: var(--primary);
    background: var(--bg-secondary);
}

.bg-primary-soft { background: rgba(var(--primary-rgb), 0.1); }
.bg-info-soft { background: rgba(59, 138, 232, 0.1); }
.bg-success-soft { background: rgba(46, 204, 113, 0.1); }

/* Header Styles re-used but improved */
.patient-header-card {
    background: white;
    border-radius: var(--radius-lg);
    overflow: hidden;
    margin: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.patient-header-bg {
    height: 120px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
}
.patient-header-content {
    display: flex;
    align-items: flex-end;
    padding: 0 2rem 1.5rem;
    margin-top: -40px;
    gap: 1.5rem;
}
.patient-avatar-large {
    width: 100px;
    height: 100px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: bold;
    color: var(--primary);
    border: 4px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.patient-header-info { flex: 1; margin-bottom: 0.5rem; }
.patient-header-name { margin: 0 0 0.5rem; font-size: 1.8rem; }
.patient-header-badges .badge { margin-left: 0.5rem; font-size: 0.9rem; padding: 0.4em 0.8em; }

@media (max-width: 900px) {
    .patient-content-grid { grid-template-columns: 1fr; }
    .patient-header-content { flex-direction: column; align-items: center; text-align: center; margin-top: -50px; }
    .patient-header-info { margin-bottom: 1rem; }
    .timeline-container::before { right: 20px; }
}
</style>

<script>
function addToWaitingList(patientId) {
    if(!confirm('إضافة لقائمة الانتظار؟')) return;
    
    // استخدام fetch لإرسال الطلب
    let formData = new FormData();
    formData.append('patient_id', patientId);
    
    fetch('<?= url("waiting-list/add") ?>', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if(res.success) {
            alert('تمت الإضافة بنجاح');
            location.reload();
        } else {
            alert(res.error || 'خطأ');
        }
    });
}

function uploadFiles(input) {
    if (input.files.length === 0) return;
    
    let formData = new FormData();
    formData.append('patient_id', <?= $patient['id'] ?>);
    for (let i = 0; i < input.files.length; i++) {
        formData.append('attachments[]', input.files[i]);
    }
    
    fetch('<?= url("patients/upload") ?>', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if(res.success) {
            location.reload();
        } else {
            alert('خطأ: ' + (res.error || 'فشل الرفع'));
        }
    })
    .catch(err => alert('حدث خطأ في الاتصال'));
}

function deleteAttachment(id) {
    if(!confirm('حذف المرفق؟')) return;
    
    fetch('<?= url("patients/attachments/delete/") ?>' + id, {
        method: 'POST'
    })
    .then(r => r.json())
    .then(res => {
        if(res.success) {
            document.getElementById('att-' + id).remove();
        } else {
            alert('خطأ: ' + (res.error || 'غير مصرح'));
        }
    });
}
</script>

<?php
$content = ob_get_clean();
require VIEWS_PATH . 'layouts/main.php';
?>
