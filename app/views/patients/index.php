<?php
$pageTitle = __('patients');
ob_start();
?>

<div class="patients-page">
    <!-- الهيدر -->
    <div class="patients-header">
        <div class="patients-search">
            <input type="text" 
                   class="form-control" 
                   id="patientSearch"
                   placeholder="<?= __('search_patients') ?>"
                   autocomplete="off">
            <span class="search-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </span>
        </div>
        
        <a href="<?= url('patients/create') ?>" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <?= __('new_patient') ?>
        </a>
    </div>
    
    <!-- نتائج البحث السريع -->
    <div class="search-results hidden" id="searchResults">
        <div class="search-results-content"></div>
    </div>
    
    <!-- قائمة المرضى -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><?= __('patient_list') ?></h2>
            <span class="badge badge-primary"><?= $total ?> مريض</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($patients)): ?>
            <div class="text-center p-3" style="color: var(--text-muted);">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="48" height="48" style="opacity: 0.5; margin-bottom: 1rem;">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <p>لا يوجد مرضى</p>
            </div>
            <?php else: ?>
            <div class="table-container">
                <table class="table patients-table">
                    <thead>
                        <tr>
                            <th><?= __('patient') ?></th>
                            <th><?= __('electronic_number') ?></th>
                            <th><?= __('paper_file_number') ?></th>
                            <th><?= __('phone') ?></th>
                            <th><?= __('gender') ?></th>
                            <th><?= __('age') ?></th>
                            <th><?= __('actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $patient): ?>
                        <tr>
                            <td>
                                <div class="patient-info">
                                    <div class="patient-avatar">
                                        <?= mb_substr($patient['full_name'], 0, 1, 'UTF-8') ?>
                                    </div>
                                    <div>
                                        <div class="patient-name"><?= $patient['full_name'] ?></div>
                                        <div class="patient-number"><?= $patient['barcode'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-primary"><?= $patient['electronic_number'] ?></span>
                            </td>
                            <td><?= $patient['paper_file_number'] ?: '-' ?></td>
                            <td dir="ltr"><?= $patient['phone'] ?: '-' ?></td>
                            <td>
                                <?php if ($patient['gender']): ?>
                                <span class="badge <?= $patient['gender'] === 'male' ? 'badge-info' : 'badge-warning' ?>">
                                    <?= $patient['gender'] === 'male' ? __('male') : __('female') ?>
                                </span>
                                <?php else: ?>
                                -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $age = calculateAge($patient['date_of_birth']);
                                echo $age !== null ? $age . ' ' . __('years') : '-';
                                ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?= url('patients/' . $patient['id']) ?>" class="btn btn-ghost btn-sm" title="<?= __('patient_details') ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                    <a href="<?= url('patients/' . $patient['id'] . '/edit') ?>" class="btn btn-ghost btn-sm" title="<?= __('edit') ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </a>
                                    <?php if (hasRole('admin') || can('patients.delete')): ?>
                                    <button class="btn btn-ghost btn-sm text-danger delete-patient-btn" data-id="<?= $patient['id'] ?>" title="حذف">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- التنقل بين الصفحات -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination-wrapper">
                <nav class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="pagination-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
                       class="pagination-btn <?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="pagination-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </a>
                    <?php endif; ?>
                </nav>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.patients-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
    flex-wrap: wrap;
}

.patients-search {
    position: relative;
    flex: 1;
    max-width: 400px;
    min-width: 250px;
}

.patients-search .form-control {
    padding-right: 3rem;
    height: 48px;
    border-radius: var(--radius-lg);
}

.patients-search .search-icon {
    position: absolute;
    top: 50%;
    right: 1rem;
    transform: translateY(-50%);
    color: var(--text-muted);
    pointer-events: none;
}

/* نتائج البحث */
.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--bg-card);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-lg);
    z-index: 100;
    max-height: 400px;
    overflow-y: auto;
    margin-top: var(--spacing-sm);
}

.search-result-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    text-decoration: none;
    color: inherit;
    transition: background var(--transition-fast);
    border-bottom: 1px solid var(--border-light);
}

.search-result-item:last-child {
    border-bottom: none;
}

.search-result-item:hover {
    background: var(--bg-secondary);
}

.search-result-item .patient-info {
    flex: 1;
}

.search-highlight {
    background: rgba(78, 205, 196, 0.3);
    padding: 0 2px;
    border-radius: 2px;
}

/* التنقل بين الصفحات */
.pagination-wrapper {
    display: flex;
    justify-content: center;
    padding: var(--spacing-lg);
    border-top: 1px solid var(--border-light);
}

.pagination {
    display: flex;
    gap: var(--spacing-xs);
}

.pagination-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 var(--spacing-sm);
    background: var(--bg-secondary);
    border-radius: var(--radius-sm);
    color: var(--text-secondary);
    text-decoration: none;
    font-weight: 500;
    transition: all var(--transition-fast);
}

.pagination-btn:hover {
    background: var(--primary);
    color: white;
}

.pagination-btn.active {
    background: var(--primary);
    color: white;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('patientSearch');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout;
    
    // جعل حقل البحث نسبياً للنتائج
    searchInput.parentElement.style.position = 'relative';
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            searchResults.classList.add('hidden');
            return;
        }
        
        searchTimeout = setTimeout(() => {
            fetch('<?= url('patients/search') ?>?q=' + encodeURIComponent(query), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.patients && data.patients.length > 0) {
                    let html = '';
                    data.patients.forEach(patient => {
                        const name = highlightText(patient.full_name, query);
                        html += `
                            <a href="<?= url('patients/') ?>${patient.id}" class="search-result-item">
                                <div class="patient-avatar">${patient.full_name.charAt(0)}</div>
                                <div class="patient-info">
                                    <div class="patient-name">${name}</div>
                                    <div class="patient-number">
                                        ${patient.electronic_number} | ${patient.phone || '-'}
                                    </div>
                                </div>
                                <span class="badge badge-${patient.gender === 'male' ? 'info' : 'warning'}">
                                    ${patient.gender === 'male' ? 'ذكر' : 'أنثى'}
                                </span>
                            </a>
                        `;
                    });
                    searchResults.querySelector('.search-results-content').innerHTML = html;
                    searchResults.classList.remove('hidden');
                } else {
                    searchResults.querySelector('.search-results-content').innerHTML = `
                        <div style="padding: var(--spacing-lg); text-align: center; color: var(--text-muted);">
                            لا توجد نتائج
                        </div>
                    `;
                    searchResults.classList.remove('hidden');
                }
            });
        }, 300);
    });
    
    // إخفاء النتائج عند النقر خارجها
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.add('hidden');
        }
    });
    
    function highlightText(text, query) {
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<span class="search-highlight">$1</span>');
    }
    
    // حذف مريض
    document.querySelectorAll('.delete-patient-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            if (confirm('هل أنت متأكد من حذف هذا المريض؟ لا يمكن التراجع عن هذا الإجراء.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= url("patients/delete/") ?>' + id;
                
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '<?= CSRF_TOKEN_NAME ?>';
                csrf.value = '<?= generateCsrfToken() ?>';
                
                form.appendChild(csrf);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>

<?php
$content = ob_get_clean();
require VIEWS_PATH . 'layouts/main.php';
?>
