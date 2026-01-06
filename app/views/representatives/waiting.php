<?php
$pageTitle = 'قائمة انتظار المناديب';
ob_start();
?>

<div class="rep-waiting-page">
    <!-- الإحصائيات -->
    <div class="waiting-stats">
        <div class="stat-card stat-waiting">
            <div class="stat-number" id="statWaiting"><?= $stats['waiting'] ?></div>
            <div class="stat-label">في الانتظار</div>
        </div>
        <div class="stat-card stat-called">
            <div class="stat-number" id="statCalled"><?= $stats['called'] + $stats['entered'] ?></div>
            <div class="stat-label">في الغرفة</div>
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
            <button type="button" class="btn btn-primary btn-lg" onclick="showAddRepModal()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                </svg>
                إضافة مندوب
            </button>
            
            <button type="button" class="btn btn-success btn-lg" onclick="callNextRep()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                    <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                    <path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path>
                </svg>
                استدعاء التالي
            </button>
            
            <button type="button" class="btn btn-secondary" onclick="recallCurrentRep()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                </svg>
                إعادة النداء
            </button>
        </div>
        
        <div class="controls-left">
            <?php if (hasRole('doctor', 'admin')): ?>
            <button type="button" class="btn <?= ($settings['is_paused'] ?? '0') === '1' ? 'btn-success' : 'btn-warning' ?>" 
                    onclick="toggleRepPause()">
                <?php if (($settings['is_paused'] ?? '0') === '1'): ?>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                </svg>
                استئناف
                <?php else: ?>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <rect x="6" y="4" width="4" height="16"></rect>
                    <rect x="14" y="4" width="4" height="16"></rect>
                </svg>
                إيقاف مؤقت
                <?php endif; ?>
            </button>
            
            <a href="<?= url('rep-waiting/display') ?>" target="_blank" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
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
    <?php if (($settings['is_paused'] ?? '0') === '1'): ?>
    <div class="pause-banner">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="10" y1="15" x2="10" y2="9"></line>
            <line x1="14" y1="15" x2="14" y2="9"></line>
        </svg>
        <span>قائمة انتظار المناديب متوقفة مؤقتاً</span>
    </div>
    <?php endif; ?>
    
    <!-- الدور الحالي -->
    <?php
    $currentCall = array_filter($waitingList, fn($w) => $w['status'] === 'called');
    $currentCall = !empty($currentCall) ? reset($currentCall) : null;
    ?>
    <div class="current-call-card rep-call" id="currentCallCard" style="<?= !$currentCall ? 'display:none' : '' ?>">
        <div class="current-call-label">الدور الحالي</div>
        <div class="current-call-number" id="currentTurnNumber"><?= $currentCall['full_turn'] ?? '-' ?></div>
        <?php if ($currentCall): ?>
        <div class="current-call-info">
            <span class="company-badge"><?= $currentCall['company_name'] ?></span>
            <span class="visitor-name"><?= $currentCall['visitor_name'] ?></span>
        </div>
        <?php endif; ?>
        <div class="current-call-actions">
            <button type="button" class="btn btn-success" onclick="enterRep()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                    <polyline points="10 17 15 12 10 7"></polyline>
                    <line x1="15" y1="12" x2="3" y2="12"></line>
                </svg>
                دخول
            </button>
            <button type="button" class="btn btn-danger" onclick="skipCurrentRep()">
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
            <h2 class="card-title">قائمة انتظار المناديب</h2>
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
                <p>لا يوجد مناديب في قائمة الانتظار</p>
                <button type="button" class="btn btn-primary" onclick="showAddRepModal()">
                    إضافة مندوب
                </button>
            </div>
            <?php else: ?>
            <div class="waiting-list" id="waitingListContainer">
                <?php foreach ($waitingList as $item): ?>
                <div class="waiting-item status-<?= $item['status'] ?>" data-id="<?= $item['id'] ?>">
                    <div class="waiting-number rep-number"><?= $item['full_turn'] ?></div>
                    <div class="waiting-info">
                        <div class="waiting-name"><?= $item['visitor_name'] ?></div>
                        <div class="waiting-meta">
                            <span class="badge badge-info"><?= $item['company_name'] ?></span>
                        </div>
                    </div>
                    <div class="waiting-status">
                        <span class="status-badge status-<?= $item['status'] ?>"><?= $item['status_text'] ?></span>
                    </div>
                    <div class="waiting-actions">
                        <?php if ($item['status'] === 'waiting'): ?>
                        <button type="button" class="btn btn-ghost btn-sm" onclick="callSpecificRep(<?= $item['id'] ?>)" title="استدعاء">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                            </svg>
                        </button>
                        <?php endif; ?>
                        <?php if ($item['status'] === 'called'): ?>
                        <button type="button" class="btn btn-success btn-sm" onclick="enterRepById(<?= $item['id'] ?>)">
                            دخول
                        </button>
                        <?php endif; ?>
                        <?php if ($item['status'] === 'entered'): ?>
                        <button type="button" class="btn btn-primary btn-sm" onclick="completeRepVisit(<?= $item['id'] ?>)">
                            إنهاء
                        </button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-ghost btn-sm text-danger" onclick="cancelRepTurn(<?= $item['id'] ?>)" title="إلغاء">
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

<!-- مودال إضافة مندوب -->
<div class="modal-overlay" id="addRepModal">
    <div class="modal">
        <div class="modal-header">
            <h3>إضافة مندوب لقائمة الانتظار</h3>
            <button type="button" class="modal-close" onclick="closeAddRepModal()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">الشركة <span class="required">*</span></label>
                <select class="form-control" id="repCompanyId" onchange="onCompanyChange()">
                    <option value="">-- اختر الشركة --</option>
                    <?php foreach ($companies as $company): ?>
                    <option value="<?= $company['id'] ?>" data-letter="<?= $company['letter'] ?>">
                        <?= $company['letter'] ?> - <?= $company['name'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">اسم الزائر</label>
                <input type="text" class="form-control" id="repVisitorName" placeholder="اسم المندوب أو الزائر">
            </div>
            
            <div class="form-group">
                <label class="form-label">ملاحظات</label>
                <textarea class="form-control" id="repNotes" rows="2" placeholder="ملاحظات إضافية..."></textarea>
            </div>
            
            <div class="next-turn-preview" id="nextTurnPreview" style="display:none">
                <span>الرقم التالي:</span>
                <span class="turn-number" id="previewTurnNumber">A1</span>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeAddRepModal()">إلغاء</button>
            <button type="button" class="btn btn-primary" id="btnConfirmAddRep" onclick="confirmAddRep()" disabled>
                إضافة للقائمة
            </button>
        </div>
    </div>
</div>

<!-- شاشة النداء المنبثقة -->
<div class="call-popup-overlay" id="repCallPopupOverlay">
    <div class="call-popup">
        <div class="call-popup-header">
            <span class="call-popup-badge rep-badge">نداء مندوب</span>
            <button class="call-popup-close" onclick="closeRepCallPopup()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="call-popup-body">
            <div class="call-popup-number-label">رقم الدور</div>
            <div class="call-popup-number rep-turn" id="repPopupTurnNumber">A1</div>
            <div class="call-popup-patient">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
                <span id="repPopupCompanyName">شركة</span>
            </div>
        </div>
        <div class="call-popup-actions">
            <button class="call-popup-btn call-popup-btn-repeat" onclick="repeatRepCall()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                </svg>
                تكرار النداء
            </button>
            <button class="call-popup-btn call-popup-btn-skip" onclick="skipRepFromPopup()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <polygon points="5 4 15 12 5 20 5 4"></polygon>
                    <line x1="19" y1="5" x2="19" y2="19"></line>
                </svg>
                تخطي
            </button>
            <button class="call-popup-btn call-popup-btn-enter" onclick="enterRepFromPopup()">
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

<style>
/* =====================================================
   أنماط صفحة قائمة انتظار المناديب
   ===================================================== */
.rep-waiting-page {
    max-width: 1200px;
    margin: 0 auto;
}

/* الإحصائيات */
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
.stat-called { border-top: 3px solid #FF6B6B; }
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

/* شريط التحكم */
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

/* شريط الإيقاف */
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

/* بطاقة الدور الحالي */
.current-call-card {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border-radius: var(--radius-xl);
    padding: var(--spacing-2xl);
    text-align: center;
    color: white;
    margin-bottom: var(--spacing-xl);
    box-shadow: var(--shadow-primary);
}

.rep-call {
    background: linear-gradient(135deg, #FF6B6B 0%, #E85555 100%) !important;
    box-shadow: 0 10px 30px rgba(255, 107, 107, 0.3) !important;
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

.current-call-info {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.company-badge {
    background: rgba(255,255,255,0.2);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
}

.visitor-name {
    font-size: 1.1rem;
}

.current-call-actions {
    display: flex;
    justify-content: center;
    gap: var(--spacing-md);
}

.current-call-actions .btn {
    min-width: 120px;
}

/* قائمة الانتظار */
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
    background: rgba(255, 107, 107, 0.1);
}

.waiting-item.status-entered {
    background: rgba(0, 217, 165, 0.1);
}

.waiting-number {
    width: 60px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-secondary);
    border-radius: var(--radius-md);
    font-size: var(--font-size-lg);
    font-weight: 700;
    color: var(--text-primary);
}

.rep-number {
    background: linear-gradient(135deg, #FF6B6B 0%, #E85555 100%) !important;
    color: white !important;
}

.status-called .waiting-number {
    background: #FF6B6B;
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
    background: rgba(255, 107, 107, 0.15);
    color: #E85555;
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

/* =====================================================
   المودال
   ===================================================== */
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
    transition: all 0.2s ease;
}

.modal-close:hover {
    background: var(--bg-secondary);
    color: var(--text-primary);
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

/* معاينة الرقم التالي */
.next-turn-preview {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(255, 107, 107, 0.1);
    border: 1px dashed rgba(255, 107, 107, 0.3);
    border-radius: var(--radius-md);
    margin-top: 1rem;
}

.next-turn-preview .turn-number {
    font-size: 2rem;
    font-weight: 800;
    color: #FF6B6B;
}

/* =====================================================
   التجاوب
   ===================================================== */
@media (max-width: 768px) {
    .waiting-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .current-call-number {
        font-size: 3rem;
    }
    
    .waiting-item {
        flex-wrap: wrap;
    }
}

@media (max-width: 480px) {
    .waiting-stats {
        grid-template-columns: 1fr 1fr;
    }
    
    .controls-right, .controls-left {
        width: 100%;
        justify-content: center;
    }
}

/* =====================================================
   شاشة النداء المنبثقة
   ===================================================== */
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
    z-index: 10001;
    padding: 20px;
}

.call-popup-overlay.show {
    display: flex;
}

.call-popup {
    background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 28px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.3);
    overflow: hidden;
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
    background: linear-gradient(135deg, #FF6B6B 0%, #E85555 100%);
    color: white;
    font-size: 13px;
    font-weight: 600;
    border-radius: 20px;
}

.rep-badge {
    background: linear-gradient(135deg, #FF6B6B 0%, #E85555 100%) !important;
}

.call-popup-close {
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 8px;
    border-radius: 10px;
}

.call-popup-close:hover {
    background: var(--bg-secondary);
}

.call-popup-body {
    padding: 40px 24px;
    text-align: center;
    background: linear-gradient(180deg, rgba(255, 107, 107, 0.05) 0%, transparent 100%);
}

.call-popup-number-label {
    font-size: 16px;
    color: var(--text-muted);
    margin-bottom: 12px;
}

.call-popup-number {
    font-size: 6rem;
    font-weight: 800;
    line-height: 1;
    background: linear-gradient(135deg, #FF6B6B 0%, #E85555 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 20px;
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
    color: #FF6B6B;
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

.call-popup-btn-skip {
    background: rgba(255, 165, 2, 0.15);
    color: #CC8400;
}

.call-popup-btn-enter {
    background: linear-gradient(135deg, var(--success) 0%, #00C095 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(0, 217, 165, 0.3);
}
</style>

<script>
let currentRepWaitingId = null;

// إظهار مودال إضافة مندوب
function showAddRepModal() {
    document.getElementById('addRepModal').classList.add('show');
}

function closeAddRepModal() {
    document.getElementById('addRepModal').classList.remove('show');
    document.getElementById('repCompanyId').value = '';
    document.getElementById('repVisitorName').value = '';
    document.getElementById('repNotes').value = '';
    document.getElementById('nextTurnPreview').style.display = 'none';
    document.getElementById('btnConfirmAddRep').disabled = true;
}

function onCompanyChange() {
    const select = document.getElementById('repCompanyId');
    const btn = document.getElementById('btnConfirmAddRep');
    const preview = document.getElementById('nextTurnPreview');
    
    if (select.value) {
        const letter = select.options[select.selectedIndex].dataset.letter;
        document.getElementById('previewTurnNumber').innerHTML = '<span dir="ltr">' + letter + '?</span>';
        preview.style.display = 'flex';
        btn.disabled = false;
    } else {
        preview.style.display = 'none';
        btn.disabled = true;
    }
}

function confirmAddRep() {
    const companyId = document.getElementById('repCompanyId').value;
    const visitorName = document.getElementById('repVisitorName').value;
    const notes = document.getElementById('repNotes').value;
    
    if (!companyId) {
        notify.error('يرجى اختيار الشركة');
        return;
    }
    
    const formData = new FormData();
    formData.append('company_id', companyId);
    formData.append('visitor_name', visitorName);
    formData.append('notes', notes);
    
    fetch('<?= url('rep-waiting/add') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            notify.success(data.message + ' - الدور: ' + data.turn);
            closeAddRepModal();
            location.reload();
        } else {
            notify.error(data.error);
        }
    });
}

// استدعاء التالي
function callNextRep() {
    fetch('<?= url('rep-waiting/call-next') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            currentRepWaitingId = data.id;
            showRepCallPopup(data.turn, data.company_name, data.id);
            playRepCallAudio(data.turn);
        } else if (data.empty) {
            notify.info('لا يوجد مناديب في قائمة الانتظار');
        } else {
            notify.error(data.error);
        }
    });
}

// إظهار شاشة النداء
function showRepCallPopup(turn, companyName, waitingId) {
    currentRepWaitingId = waitingId;
    document.getElementById('repPopupTurnNumber').textContent = turn;
    document.getElementById('repPopupCompanyName').textContent = companyName || 'شركة';
    document.getElementById('repCallPopupOverlay').classList.add('show');
}

function closeRepCallPopup() {
    document.getElementById('repCallPopupOverlay').classList.remove('show');
    location.reload();
}

function repeatRepCall() {
    const turn = document.getElementById('repPopupTurnNumber').textContent;
    playRepCallAudio(turn);
}

function skipRepFromPopup() {
    closeRepCallPopup();
    setTimeout(() => callNextRep(), 300);
}

function enterRepFromPopup() {
    if (currentRepWaitingId) {
        fetch('<?= url('rep-waiting/enter/') ?>' + currentRepWaitingId, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closeRepCallPopup();
            }
        });
    }
}

// النداء الصوتي للمناديب (حرف + رقم)
const audioBasePath = '<?= asset('audio/numbers/') ?>';

function playRepCallAudio(turn) {
    // turn = "A1", "B2", etc.
    const letter = turn.charAt(0).toLowerCase(); // تحويل لحرف صغير
    const number = parseInt(turn.substring(1));
    
    let audioQueue = [];
    
    // "رقم"
    audioQueue.push(audioBasePath + 'raqm.mp3');
    
    // ملف الحرف (a.mp3, b.mp3, ...)
    audioQueue.push(audioBasePath + letter + '.mp3');
    
    // الرقم
    if (number <= 20) {
        audioQueue.push(audioBasePath + number + '.mp3');
    } else if (number < 100) {
        const tens = Math.floor(number / 10) * 10;
        const ones = number % 10;
        if (ones === 0) {
            audioQueue.push(audioBasePath + tens + '.mp3');
        } else {
            audioQueue.push(audioBasePath + ones + '.mp3');
            audioQueue.push(audioBasePath + 'wa.mp3');
            audioQueue.push(audioBasePath + tens + '.mp3');
        }
    }
    
    // "تفضل بالدخول"
    audioQueue.push(audioBasePath + 'tafaddal.mp3');
    
    playAudioQueue(audioQueue);
}

function playAudioQueue(queue) {
    if (queue.length === 0) return;
    
    const audio = new Audio(queue.shift());
    audio.onended = () => setTimeout(() => playAudioQueue(queue), 100);
    audio.onerror = () => playAudioQueue(queue);
    audio.play().catch(() => playAudioQueue(queue));
}

// إعادة النداء
function recallCurrentRep() {
    fetch('<?= url('rep-waiting/recall') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            playRepCallAudio(data.turn);
            notify.success('تم إعادة النداء');
        } else {
            notify.error(data.error);
        }
    });
}

// إيقاف/استئناف
function toggleRepPause() {
    fetch('<?= url('rep-waiting/toggle-pause') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
    });
}

// دخول المندوب
function enterRep() {
    const item = document.querySelector('.waiting-item.status-called');
    if (item) enterRepById(item.dataset.id);
}

function enterRepById(id) {
    fetch('<?= url('rep-waiting/enter/') ?>' + id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
    });
}

// إنهاء الزيارة
function completeRepVisit(id) {
    fetch('<?= url('rep-waiting/complete/') ?>' + id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
    });
}

// إلغاء الدور
function cancelRepTurn(id) {
    MedFlowConfirm({
        title: 'إلغاء الدور',
        message: 'هل تريد إلغاء هذا الدور؟',
        type: 'danger',
        confirmText: 'إلغاء الدور',
        cancelText: 'تراجع'
    }).then(confirmed => {
        if (!confirmed) return;
        
        fetch('<?= url('rep-waiting/cancel/') ?>' + id, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
        });
    });
}

function skipCurrentRep() {
    callNextRep();
}

function callSpecificRep(id) {
    // يمكن تطوير هذه الميزة لاحقاً
    notify.info('سيتم تفعيل هذه الميزة قريباً');
}
</script>

<?php
$content = ob_get_clean();
require VIEWS_PATH . 'layouts/main.php';
?>
