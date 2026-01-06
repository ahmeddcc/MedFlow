<?php
$pageTitle = __('waiting_list');
ob_start();
?>

<div class="waiting-page">
    <!-- الإحصائيات -->
    <div class="waiting-stats">
        <div class="stat-card stat-waiting">
            <div class="stat-number" id="statWaiting"><?= $stats['waiting'] ?></div>
            <div class="stat-label">في الانتظار</div>
        </div>
        <div class="stat-card stat-called">
            <div class="stat-number" id="statCalled"><?= $stats['called'] + $stats['entered'] ?></div>
            <div class="stat-label">في الكشف</div>
        </div>
        <div class="stat-card stat-completed">
            <div class="stat-number" id="statCompleted"><?= $stats['completed'] ?></div>
            <div class="stat-label">منتهي</div>
        </div>
        <div class="stat-card stat-total">
            <div class="stat-number" id="statTotal"><?= $stats['total'] ?></div>
            <div class="stat-label">إجمالي اليوم</div>
        </div>
    </div>
    
    <!-- شريط التحكم -->
    <div class="waiting-controls">
        <div class="controls-right">
            <button type="button" class="btn btn-primary btn-lg" id="btnAddPatient" onclick="showAddPatientModal()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                </svg>
                إضافة مريض
            </button>
            
            <button type="button" class="btn btn-success btn-lg" id="btnCallNext" onclick="callNext()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                    <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                    <path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path>
                </svg>
                استدعاء التالي
            </button>
            
            <button type="button" class="btn btn-secondary" id="btnRecall" onclick="recallCurrent()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                </svg>
                إعادة النداء
            </button>
        </div>
        
        <div class="controls-left">
            <?php if (hasRole('doctor', 'admin')): ?>
            <button type="button" class="btn <?= $settings['is_paused'] === '1' ? 'btn-success' : 'btn-warning' ?>" 
                    id="btnPause" onclick="togglePause()">
                <?php if ($settings['is_paused'] === '1'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                </svg>
                استئناف
                <?php else: ?>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <rect x="6" y="4" width="4" height="16"></rect>
                    <rect x="14" y="4" width="4" height="16"></rect>
                </svg>
                إيقاف مؤقت
                <?php endif; ?>
            </button>
            
            <a href="<?= url('waiting-list/display') ?>" target="_blank" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                    <line x1="8" y1="21" x2="16" y2="21"></line>
                    <line x1="12" y1="17" x2="12" y2="21"></line>
                </svg>
                شاشة العرض
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- حالة الإيقاف -->
    <?php if ($settings['is_paused'] === '1'): ?>
    <div class="pause-banner">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="24" height="24">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="10" y1="15" x2="10" y2="9"></line>
            <line x1="14" y1="15" x2="14" y2="9"></line>
        </svg>
        <span>قائمة الانتظار متوقفة مؤقتاً</span>
    </div>
    <?php endif; ?>
    
    <!-- الدور الحالي -->
    <div class="current-call-card" id="currentCallCard" style="<?= empty(array_filter($waitingList, fn($w) => $w['status'] === 'called')) ? 'display:none' : '' ?>">
        <div class="current-call-label">الدور الحالي</div>
        <div class="current-call-number" id="currentTurnNumber">
            <?php 
            $current = array_filter($waitingList, fn($w) => $w['status'] === 'called');
            echo !empty($current) ? reset($current)['turn_number'] : '-';
            ?>
        </div>
        <div class="current-call-actions">
            <button type="button" class="btn btn-success" onclick="enterPatient()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                    <polyline points="10 17 15 12 10 7"></polyline>
                    <line x1="15" y1="12" x2="3" y2="12"></line>
                </svg>
                دخول
            </button>
            <button type="button" class="btn btn-danger" onclick="skipCurrent()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <polygon points="5 4 15 12 5 20 5 4"></polygon>
                    <line x1="19" y1="5" x2="19" y2="19"></line>
                </svg>
                تخطي
            </button>
        </div>
    </div>
    
    <!-- قائمة الانتظار -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">قائمة الانتظار</h2>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($waitingList)): ?>
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="64" height="64">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <p>لا يوجد مرضى في قائمة الانتظار</p>
                <button type="button" class="btn btn-primary" onclick="showAddPatientModal()">
                    إضافة مريض
                </button>
            </div>
            <?php else: ?>
            <div class="waiting-list" id="waitingListContainer">
                <?php foreach ($waitingList as $item): ?>
                <div class="waiting-item status-<?= $item['status'] ?>" data-id="<?= $item['id'] ?>">
                    <div class="waiting-number"><?= $item['turn_number'] ?></div>
                    <div class="waiting-info">
                        <div class="waiting-name"><?= $item['patient_name'] ?></div>
                        <div class="waiting-meta">
                            <span class="badge badge-<?= $item['visit_type'] === 'emergency' ? 'danger' : 'info' ?>">
                                <?= $item['visit_type_text'] ?>
                            </span>
                            <?php if ($item['priority_level'] === 'urgent'): ?>
                            <span class="badge badge-danger">عاجل</span>
                            <?php elseif ($item['priority_level'] === 'vip'): ?>
                            <span class="badge badge-warning">VIP</span>
                            <?php endif; ?>
                            <span><?= $item['electronic_number'] ?></span>
                        </div>
                    </div>
                    <div class="waiting-status">
                        <span class="status-badge status-<?= $item['status'] ?>"><?= $item['status_text'] ?></span>
                    </div>
                    <div class="waiting-actions">
                        <?php if ($item['status'] === 'waiting'): ?>
                        <button type="button" class="btn btn-ghost btn-sm" onclick="callSpecific(<?= $item['id'] ?>)" title="استدعاء">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                            </svg>
                        </button>
                        <?php endif; ?>
                        <?php if ($item['status'] === 'called'): ?>
                        <button type="button" class="btn btn-success btn-sm" onclick="enterPatientById(<?= $item['id'] ?>)">
                            دخول
                        </button>
                        <?php endif; ?>
                        <?php if ($item['status'] === 'entered'): ?>
                        <button type="button" class="btn btn-primary btn-sm" onclick="completeVisit(<?= $item['id'] ?>)">
                            إنهاء
                        </button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-ghost btn-sm text-danger" onclick="cancelTurn(<?= $item['id'] ?>)" title="إلغاء">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
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

<!-- مودال إضافة مريض -->
<div class="modal-overlay" id="addPatientModal">
    <div class="modal">
        <div class="modal-header">
            <h3>إضافة مريض لقائمة الانتظار</h3>
            <button type="button" class="modal-close" onclick="closeAddPatientModal()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">البحث عن مريض</label>
                <input type="text" class="form-control" id="patientSearchInput" 
                       placeholder="اكتب اسم المريض أو رقمه..." autocomplete="off">
                <div class="search-results" id="patientSearchResults"></div>
            </div>
            
            <div id="selectedPatientInfo" class="selected-patient hidden">
                <input type="hidden" id="selectedPatientId">
                <div class="selected-patient-card">
                    <div class="patient-avatar" id="selectedPatientAvatar">أ</div>
                    <div class="patient-info">
                        <div class="patient-name" id="selectedPatientName">اسم المريض</div>
                        <div class="patient-number" id="selectedPatientNumber">MF1001</div>
                    </div>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="clearSelectedPatient()">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">نوع الزيارة</label>
                <select class="form-control" id="visitType">
                    <option value="checkup">كشف</option>
                    <option value="followup">متابعة</option>
                    <option value="consultation">استشارة</option>
                    <option value="emergency">طوارئ</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">الأولوية</label>
                <select class="form-control" id="priorityLevel">
                    <option value="normal">عادي</option>
                    <option value="urgent" style="color:red; font-weight:bold;">عاجل (Urgent)</option>
                    <option value="vip" style="color:gold; font-weight:bold;">VIP</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">ملاحظات</label>
                <textarea class="form-control" id="visitNotes" rows="2" placeholder="ملاحظات إضافية..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeAddPatientModal()">إلغاء</button>
            <button type="button" class="btn btn-primary" id="btnConfirmAdd" onclick="confirmAddPatient()" disabled>
                إضافة للقائمة
            </button>
</div>
    </div>
</div>

<!-- شاشة النداء المنبثقة -->
<div class="call-popup-overlay" id="callPopupOverlay">
    <div class="call-popup">
        <div class="call-popup-header">
            <span class="call-popup-badge">نداء جديد</span>
            <button class="call-popup-close" onclick="closeCallPopup()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="call-popup-body">
            <div class="call-popup-number-label">رقم الدور</div>
            <div class="call-popup-number" id="popupTurnNumber">1</div>
            <div class="call-popup-patient">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span id="popupPatientName">اسم المريض</span>
            </div>
        </div>
        <div class="call-popup-actions">
            <button class="call-popup-btn call-popup-btn-repeat" onclick="repeatCallFromPopup()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                </svg>
                تكرار النداء
            </button>
            <button class="call-popup-btn call-popup-btn-skip" onclick="skipFromPopup()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <polygon points="5 4 15 12 5 20 5 4"></polygon>
                    <line x1="19" y1="5" x2="19" y2="19"></line>
                </svg>
                تخطي
            </button>
            <button class="call-popup-btn call-popup-btn-enter" onclick="enterFromPopup()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                    <polyline points="10 17 15 12 10 7"></polyline>
                    <line x1="15" y1="12" x2="3" y2="12"></line>
                </svg>
                دخول
            </button>
        </div>
    </div>
</div>

<!-- Audio للنداء -->
<audio id="callAudio" preload="auto"></audio>

<style>
.waiting-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-xl);
}

.waiting-stats .stat-card {
    text-align: center;
    padding: var(--spacing-lg);
    border-radius: var(--radius-lg);
    background: var(--bg-card);
    box-shadow: var(--shadow-card);
}

.stat-waiting { border-top: 3px solid var(--warning); }
.stat-called { border-top: 3px solid var(--primary); }
.stat-completed { border-top: 3px solid var(--success); }
.stat-total { border-top: 3px solid var(--info); }

.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--text-primary);
}

.stat-label {
    font-size: var(--font-size-sm);
    color: var(--text-muted);
}

.waiting-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-xl);
    flex-wrap: wrap;
    gap: var(--spacing-md);
}

.controls-right, .controls-left {
    display: flex;
    gap: var(--spacing-sm);
    flex-wrap: wrap;
}

.pause-banner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    background: rgba(255, 165, 2, 0.1);
    border: 1px solid rgba(255, 165, 2, 0.2);
    border-radius: var(--radius-md);
    color: var(--warning);
    margin-bottom: var(--spacing-xl);
    font-weight: 600;
}

.current-call-card {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border-radius: var(--radius-xl);
    padding: var(--spacing-2xl);
    text-align: center;
    color: white;
    margin-bottom: var(--spacing-xl);
    box-shadow: var(--shadow-primary);
}

.current-call-label {
    font-size: var(--font-size-lg);
    opacity: 0.9;
    margin-bottom: var(--spacing-sm);
}

.current-call-number {
    font-size: 5rem;
    font-weight: 800;
    line-height: 1;
    margin-bottom: var(--spacing-lg);
    text-shadow: 0 4px 20px rgba(0,0,0,0.2);
}

.current-call-actions {
    display: flex;
    justify-content: center;
    gap: var(--spacing-md);
}

.current-call-actions .btn {
    min-width: 120px;
}

.waiting-list {
    display: flex;
    flex-direction: column;
}

.waiting-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-lg);
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--border-light);
    transition: background var(--transition-fast);
}

.waiting-item:hover {
    background: var(--bg-secondary);
}

.waiting-item:last-child {
    border-bottom: none;
}

.waiting-item.status-called {
    background: rgba(78, 205, 196, 0.1);
}

.waiting-item.status-entered {
    background: rgba(0, 217, 165, 0.1);
}

.waiting-number {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-secondary);
    border-radius: var(--radius-md);
    font-size: var(--font-size-xl);
    font-weight: 700;
    color: var(--text-primary);
}

.status-called .waiting-number {
    background: var(--primary);
    color: white;
}

.waiting-info {
    flex: 1;
}

.waiting-name {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 4px;
}

.waiting-meta {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    font-size: var(--font-size-sm);
    color: var(--text-muted);
}

.status-badge {
    padding: 4px 8px;
    border-radius: var(--radius-sm);
    font-size: var(--font-size-xs);
    font-weight: 600;
}

.status-badge.status-waiting {
    background: rgba(255, 165, 2, 0.15);
    color: #CC8400;
}

.status-badge.status-called {
    background: rgba(78, 205, 196, 0.15);
    color: var(--primary-dark);
}

.status-badge.status-entered {
    background: rgba(0, 217, 165, 0.15);
    color: #00A87D;
}

.waiting-actions {
    display: flex;
    gap: var(--spacing-xs);
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

.empty-state p {
    margin-bottom: var(--spacing-lg);
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
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
}

.modal-close {
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: var(--spacing-xs);
}

.modal-body {
    padding: var(--spacing-lg);
    overflow-y: auto;
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
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-lg);
    max-height: 200px;
    overflow-y: auto;
    z-index: 10;
    display: none;
}

.search-results.show {
    display: block;
}

.search-result-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    cursor: pointer;
    transition: background var(--transition-fast);
}

.search-result-item:hover {
    background: var(--bg-secondary);
}

.selected-patient-card {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    background: rgba(78, 205, 196, 0.1);
    border: 1px solid rgba(78, 205, 196, 0.2);
    border-radius: var(--radius-md);
}

.form-group {
    position: relative;
}

@media (max-width: 768px) {
    .waiting-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .current-call-number {
        font-size: 3rem;
    }
}

/* شاشة النداء المنبثقة */
.call-popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(8px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    padding: 20px;
}

.call-popup-overlay.show {
    display: flex;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.call-popup {
    background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 28px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.1);
    animation: popupSlide 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    overflow: hidden;
}

@keyframes popupSlide {
    from {
        opacity: 0;
        transform: scale(0.8) translateY(30px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.call-popup-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.call-popup-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    font-size: 13px;
    font-weight: 600;
    border-radius: 20px;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(78, 205, 196, 0.5); }
    50% { box-shadow: 0 0 0 12px rgba(78, 205, 196, 0); }
}

.call-popup-close {
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 8px;
    border-radius: 10px;
    transition: all 0.2s ease;
}

.call-popup-close:hover {
    background: var(--bg-secondary);
    color: var(--text-primary);
}

.call-popup-body {
    padding: 40px 24px;
    text-align: center;
    background: linear-gradient(180deg, rgba(78, 205, 196, 0.05) 0%, transparent 100%);
}

.call-popup-number-label {
    font-size: 16px;
    color: var(--text-muted);
    margin-bottom: 12px;
}

.call-popup-number {
    font-size: 8rem;
    font-weight: 800;
    line-height: 1;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 20px;
    animation: numberPulse 1s ease-in-out infinite alternate;
}

@keyframes numberPulse {
    from { transform: scale(1); }
    to { transform: scale(1.02); }
}

.call-popup-patient {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    color: var(--text-primary);
    font-size: 16px;
    font-weight: 600;
}

.call-popup-patient svg {
    color: var(--primary);
}

.call-popup-actions {
    display: flex;
    gap: 12px;
    padding: 24px;
    background: var(--bg-secondary);
}

.call-popup-btn {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 16px 12px;
    border: none;
    border-radius: 14px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.call-popup-btn svg {
    width: 24px;
    height: 24px;
}

.call-popup-btn-repeat {
    background: white;
    color: var(--text-primary);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.call-popup-btn-repeat:hover {
    background: var(--bg-secondary);
    transform: translateY(-2px);
}

.call-popup-btn-skip {
    background: rgba(255, 165, 2, 0.15);
    color: #CC8400;
}

.call-popup-btn-skip:hover {
    background: rgba(255, 165, 2, 0.25);
    transform: translateY(-2px);
}

.call-popup-btn-enter {
    background: linear-gradient(135deg, var(--success) 0%, #00C095 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(0, 217, 165, 0.3);
}

.call-popup-btn-enter:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 217, 165, 0.4);
}

@media (max-width: 480px) {
    .call-popup-number {
        font-size: 5rem;
    }
    
    .call-popup-actions {
        flex-direction: column;
    }
}
</style>

<script>
let currentWaitingId = null;

// إظهار مودال إضافة مريض
function showAddPatientModal() {
    document.getElementById('addPatientModal').classList.add('show');
    document.getElementById('patientSearchInput').focus();
}

// إغلاق المودال
function closeAddPatientModal() {
    document.getElementById('addPatientModal').classList.remove('show');
    clearSelectedPatient();
    document.getElementById('patientSearchInput').value = '';
    document.getElementById('patientSearchResults').innerHTML = '';
}

// البحث عن مريض
let searchTimeout;
document.getElementById('patientSearchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();
    
    if (query.length < 2) {
        document.getElementById('patientSearchResults').classList.remove('show');
        return;
    }
    
    searchTimeout = setTimeout(() => {
        fetch('<?= url('waiting-list/search-patient') ?>?q=' + encodeURIComponent(query), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            const results = document.getElementById('patientSearchResults');
            if (data.patients.length > 0) {
                results.innerHTML = data.patients.map(p => `
                    <div class="search-result-item" onclick="selectPatient(${p.id}, '${p.full_name}', '${p.electronic_number}')">
                        <div class="patient-avatar">${p.full_name.charAt(0)}</div>
                        <div>
                            <div style="font-weight:600">${p.full_name}</div>
                            <div style="font-size:12px;color:var(--text-muted)">${p.electronic_number} | ${p.phone || '-'}</div>
                        </div>
                    </div>
                `).join('');
                results.classList.add('show');
            } else {
                results.innerHTML = '<div style="padding:1rem;text-align:center;color:var(--text-muted)">لا توجد نتائج</div>';
                results.classList.add('show');
            }
        });
    }, 300);
});

// اختيار مريض
function selectPatient(id, name, number) {
    document.getElementById('selectedPatientId').value = id;
    document.getElementById('selectedPatientName').textContent = name;
    document.getElementById('selectedPatientNumber').textContent = number;
    document.getElementById('selectedPatientAvatar').textContent = name.charAt(0);
    document.getElementById('selectedPatientInfo').classList.remove('hidden');
    document.getElementById('patientSearchInput').classList.add('hidden');
    document.getElementById('patientSearchResults').classList.remove('show');
    document.getElementById('btnConfirmAdd').disabled = false;
}

// مسح الاختيار
function clearSelectedPatient() {
    document.getElementById('selectedPatientId').value = '';
    document.getElementById('selectedPatientInfo').classList.add('hidden');
    document.getElementById('patientSearchInput').classList.remove('hidden');
    document.getElementById('patientSearchInput').value = '';
    document.getElementById('btnConfirmAdd').disabled = true;
}

// تأكيد إضافة المريض
function confirmAddPatient() {
    const patientId = document.getElementById('selectedPatientId').value;
    const visitType = document.getElementById('visitType').value;
    const priority = document.getElementById('priorityLevel').value;
    const notes = document.getElementById('visitNotes').value;
    
    const btn = document.getElementById('btnConfirmAdd');
    btn.disabled = true;
    btn.textContent = 'جاري الإضافة...';
    
    const formData = new FormData();
    formData.append('patient_id', patientId);
    formData.append('visit_type', visitType);
    formData.append('priority', priority);
    formData.append('notes', notes);
    
    fetch('<?= url('waiting-list/add') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message + ' - الدور رقم: ' + data.turn_number);
            closeAddPatientModal();
            location.reload();
        } else {
            showAlert('error', data.error);
        }
    });
}

// استدعاء التالي
function callNext() {
    fetch('<?= url('waiting-list/call-next') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            currentWaitingId = data.id;
            showCallPopup(data.turn_number, data.patient_name, data.id);
            playCallAudio(data.turn_number, data.patient_name);
        } else if (data.empty) {
            notify.info('لا يوجد مرضى في قائمة الانتظار');
        } else {
            notify.error(data.error);
        }
    });
}

// إعادة النداء
function recallCurrent() {
    fetch('<?= url('waiting-list/recall') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            playCallAudio(data.turn_number, data.patient_name);
            notify.success('تم إعادة النداء');
        } else {
            notify.error(data.error);
        }
    });
}

// إظهار شاشة النداء المنبثقة
function showCallPopup(turnNumber, patientName, waitingId) {
    currentWaitingId = waitingId;
    document.getElementById('popupTurnNumber').textContent = turnNumber;
    document.getElementById('popupPatientName').textContent = patientName || 'مريض';
    document.getElementById('callPopupOverlay').classList.add('show');
}

// إغلاق شاشة النداء
function closeCallPopup() {
    document.getElementById('callPopupOverlay').classList.remove('show');
    location.reload();
}

// تكرار النداء من الشاشة المنبثقة
function repeatCallFromPopup() {
    const number = document.getElementById('popupTurnNumber').textContent;
    const name = document.getElementById('popupPatientName').textContent;
    playCallAudio(number, name);
}

// تخطي من الشاشة المنبثقة
function skipFromPopup() {
    closeCallPopup();
    setTimeout(() => callNext(), 300);
}

// دخول من الشاشة المنبثقة
function enterFromPopup() {
    if (currentWaitingId) {
        fetch('<?= url('waiting-list/enter/') ?>' + currentWaitingId, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closeCallPopup();
            }
        });
    }
}

// =====================================================
// نظام النداء الصوتي (ملفات صوتية مسجلة)
// =====================================================
const audioBasePath = '<?= url('assets/audio/numbers/') ?>';
let audioQueue = [];
let isPlaying = false;

function playCallAudio(number, name) {
    // إيقاف أي صوت حالي
    if (window.currentAudio) {
        window.currentAudio.pause();
        window.currentAudio = null;
    }
    
    audioQueue = [];
    
    // 1. "رقم"
    audioQueue.push(audioBasePath + 'raqm.mp3');
    
    // 2. ملفات الرقم
    const numberFiles = getNumberAudioFiles(number);
    audioQueue = audioQueue.concat(numberFiles);
    
    // 3. "تفضل بالدخول"
    audioQueue.push(audioBasePath + 'tafaddal.mp3');
    
    // تشغيل القائمة
    playNextInQueue();
}

function getNumberAudioFiles(number) {
    const files = [];
    number = parseInt(number);
    
    if (number <= 20) {
        files.push(audioBasePath + number + '.mp3');
    } else if (number < 100) {
        const tens = Math.floor(number / 10) * 10;
        const ones = number % 10;
        
        if (ones === 0) {
            files.push(audioBasePath + tens + '.mp3');
        } else {
            files.push(audioBasePath + ones + '.mp3');
            files.push(audioBasePath + 'wa.mp3');
            files.push(audioBasePath + tens + '.mp3');
        }
    } else if (number === 100) {
        files.push(audioBasePath + '100.mp3');
    } else {
        // للأعداد أكبر من 100 - تفكيك بسيط حالياً
        files.push(audioBasePath + (number % 100 || 100) + '.mp3');
    }
    
    return files;
}

function playNextInQueue() {
    if (audioQueue.length === 0) {
        isPlaying = false;
        return;
    }
    
    isPlaying = true;
    const audio = new Audio(audioQueue.shift());
    window.currentAudio = audio; // للاحتفاظ بالمرجع للإيقاف
    
    audio.onended = () => {
        playNextInQueue();
    };
    
    audio.onerror = (e) => {
        console.warn('ملف صوتي مفقود:', audio.src);
        playNextInQueue();
    };
    
    audio.play().catch(err => {
        console.error('خطأ في التشغيل:', err);
        playNextInQueue();
    });
}

// إيقاف/استئناف
function togglePause() {
    fetch('<?= url('waiting-list/toggle-pause') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

// دخول المريض
function enterPatient() {
    const id = document.querySelector('.waiting-item.status-called')?.dataset.id;
    if (id) enterPatientById(id);
}

function enterPatientById(id) {
    fetch('<?= url('waiting-list/enter/') ?>' + id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

// إنهاء الكشف
function completeVisit(id) {
    fetch('<?= url('waiting-list/complete/') ?>' + id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

// إلغاء الدور
function cancelTurn(id) {
    if (!confirm('هل تريد إلغاء هذا الدور؟')) return;
    
    fetch('<?= url('waiting-list/cancel/') ?>' + id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

// تخطي الدور الحالي
function skipCurrent() {
    callNext();
}
</script>

<?php
$content = ob_get_clean();
require VIEWS_PATH . 'layouts/main.php';
?>
