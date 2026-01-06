<?php
$pageTitle = 'إدارة المستخدمين';
ob_start();
?>

<div class="users-page">
    <!-- شريط التحكم -->
    <div class="controls-bar">
        <h1 class="page-title">المستخدمين</h1>
        <button class="btn btn-primary" id="btnNewUser">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            مستخدم جديد
        </button>
    </div>
    
    <!-- جدول المستخدمين -->
    <div class="card">
        <div class="card-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>المستخدم</th>
                        <th>الاسم</th>
                        <th>الصلاحية</th>
                        <th>الحالة</th>
                        <th>آخر دخول</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><code><?= $user['username'] ?></code></td>
                        <td><?= $user['full_name'] ?></td>
                        <td>
                            <span class="role-badge role-<?= $user['role'] ?>">
                                <?= $user['role'] === 'admin' ? 'مدير' : ($user['role'] === 'doctor' ? 'طبيب' : 'مساعد') ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?= $user['is_active'] ? 'active' : 'inactive' ?>">
                                <?= $user['is_active'] ? 'نشط' : 'معطل' ?>
                            </span>
                        </td>
                        <td><?= $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : '-' ?></td>
                        <td>
                            <button class="btn btn-ghost btn-sm btn-edit" data-user='<?= htmlspecialchars(json_encode($user)) ?>' title="تعديل">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </button>
                            <?php if ($user['id'] !== currentUser()['id']): ?>
                            <button class="btn btn-ghost btn-sm btn-toggle" data-id="<?= $user['id'] ?>" title="تبديل الحالة">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path>
                                    <line x1="12" y1="2" x2="12" y2="12"></line>
                                </svg>
                            </button>
                            <a href="<?= url('users/permissions?user_id=' . $user['id']) ?>" class="btn btn-ghost btn-sm" title="الصلاحيات">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                </svg>
                            </a>
                            </a>
                            <?php if ($user['id'] !== currentUser()['id']): ?>
                            <button class="btn btn-ghost btn-sm text-danger delete-user-btn" data-id="<?= $user['id'] ?>" title="حذف">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </button>
                            <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- مودال مستخدم جديد -->
<div class="modal-overlay" id="newUserModal">
    <div class="modal">
        <form id="newUserForm">
            <div class="modal-header">
                <h3>مستخدم جديد</h3>
                <button type="button" class="modal-close" id="closeNewModal">×</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">اسم المستخدم <span class="required">*</span></label>
                    <input type="text" class="form-control" id="newUsername" placeholder="username" autocomplete="username">
                </div>
                <div class="form-group">
                    <label class="form-label">الاسم الكامل <span class="required">*</span></label>
                    <input type="text" class="form-control" id="newFullName" autocomplete="name">
                </div>
                <div class="form-group">
                    <label class="form-label">كلمة المرور <span class="required">*</span></label>
                    <input type="password" class="form-control" id="newPassword" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label class="form-label">الصلاحية</label>
                    <select class="form-control" id="newRole">
                        <option value="assistant">مساعد</option>
                        <option value="doctor">طبيب</option>
                        <option value="admin">مدير</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelNewModal">إلغاء</button>
                <button type="submit" class="btn btn-primary">إنشاء</button>
            </div>
        </form>
    </div>
</div>

<!-- مودال تعديل -->
<div class="modal-overlay" id="editUserModal">
    <div class="modal">
        <form id="editUserForm">
            <div class="modal-header">
                <h3>تعديل المستخدم</h3>
                <button type="button" class="modal-close" id="closeEditModal">×</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editUserId">
                <div class="form-group">
                    <label class="form-label">اسم المستخدم</label>
                    <input type="text" class="form-control" id="editUsername" disabled autocomplete="username">
                </div>
                <div class="form-group">
                    <label class="form-label">الاسم الكامل</label>
                    <input type="text" class="form-control" id="editFullName" autocomplete="name">
                </div>
                <div class="form-group">
                    <label class="form-label">الصلاحية</label>
                    <select class="form-control" id="editRole">
                        <option value="assistant">مساعد</option>
                        <option value="doctor">طبيب</option>
                        <option value="admin">مدير</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">كلمة مرور جديدة (اختياري)</label>
                    <input type="password" class="form-control" id="editPassword" placeholder="اتركه فارغاً للإبقاء على الحالية" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" id="editIsActive">
                        <span>نشط</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelEditModal">إلغاء</button>
                <button type="submit" class="btn btn-primary">حفظ</button>
            </div>
        </form>
    </div>
</div>

<style>
.users-page {
    max-width: 1000px;
    margin: 0 auto;
}

.controls-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-xl);
}

.page-title {
    font-size: var(--font-size-xl);
    font-weight: 700;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: var(--spacing-md);
    text-align: right;
    border-bottom: 1px solid var(--border-light);
}

.data-table th {
    font-weight: 600;
    color: var(--text-muted);
    font-size: var(--font-size-sm);
}

.role-badge {
    padding: 4px 10px;
    border-radius: var(--radius-full);
    font-size: var(--font-size-sm);
}

.role-admin { background: rgba(255, 107, 107, 0.15); color: #E85555; }
.role-doctor { background: rgba(78, 205, 196, 0.15); color: var(--primary); }
.role-assistant { background: rgba(255, 165, 2, 0.15); color: #CC8400; }

.status-badge {
    padding: 4px 10px;
    border-radius: var(--radius-full);
    font-size: var(--font-size-sm);
}

.status-badge.active { background: rgba(0, 217, 165, 0.15); color: var(--success); }
.status-badge.inactive { background: var(--bg-secondary); color: var(--text-muted); }

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10000;
}

.modal-overlay.show { display: flex; }

.modal {
    background: var(--bg-card);
    border-radius: var(--radius-xl);
    width: 100%;
    max-width: 450px;
    box-shadow: var(--shadow-lg);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--border-light);
}

.modal-header h3 { margin: 0; }

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-muted);
}

.modal-body { padding: var(--spacing-lg); }

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: var(--spacing-sm);
    padding: var(--spacing-lg);
    border-top: 1px solid var(--border-light);
    background: var(--bg-secondary);
}

.form-check {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.form-check input { width: 18px; height: 18px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // فتح مودال مستخدم جديد
    document.getElementById('btnNewUser').addEventListener('click', function() {
        document.getElementById('newUsername').value = '';
        document.getElementById('newFullName').value = '';
        document.getElementById('newPassword').value = '';
        document.getElementById('newRole').value = 'assistant';
        document.getElementById('newUserModal').classList.add('show');
    });
    
    // إغلاق مودال جديد
    document.getElementById('closeNewModal').addEventListener('click', closeNewModal);
    document.getElementById('cancelNewModal').addEventListener('click', closeNewModal);
    
    function closeNewModal() {
        document.getElementById('newUserModal').classList.remove('show');
    }
    
    // حفظ مستخدم جديد
    document.getElementById('newUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('username', document.getElementById('newUsername').value);
        formData.append('full_name', document.getElementById('newFullName').value);
        formData.append('password', document.getElementById('newPassword').value);
        formData.append('role', document.getElementById('newRole').value);
        
        fetch('<?= url('users/create') ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                notify.success(data.message);
                closeNewModal();
                location.reload();
            } else {
                notify.error(data.error);
            }
        });
    });
    
    // حذف مستخدم
    document.querySelectorAll('.delete-user-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('هل أنت متأكد من حذف هذا المستخدم؟')) {
                const id = this.dataset.id;
                fetch('<?= url('users/delete/') ?>' + id, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.error || 'حدث خطأ');
                    }
                });
            }
        });
    });
    
    // فتح مودال تعديل
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            const user = JSON.parse(this.dataset.user);
            document.getElementById('editUserId').value = user.id;
            document.getElementById('editUsername').value = user.username;
            document.getElementById('editFullName').value = user.full_name;
            document.getElementById('editRole').value = user.role;
            document.getElementById('editIsActive').checked = user.is_active == 1;
            document.getElementById('editPassword').value = '';
            document.getElementById('editUserModal').classList.add('show');
        });
    });
    
    // إغلاق مودال تعديل
    document.getElementById('closeEditModal').addEventListener('click', closeEditModal);
    document.getElementById('cancelEditModal').addEventListener('click', closeEditModal);
    
    function closeEditModal() {
        document.getElementById('editUserModal').classList.remove('show');
    }
    
    // حفظ التعديل
    document.getElementById('editUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const id = document.getElementById('editUserId').value;
        const formData = new FormData();
        formData.append('full_name', document.getElementById('editFullName').value);
        formData.append('role', document.getElementById('editRole').value);
        formData.append('is_active', document.getElementById('editIsActive').checked ? 1 : 0);
        formData.append('new_password', document.getElementById('editPassword').value);
        
        fetch('<?= url('users') ?>/' + id + '/update', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                notify.success(data.message);
                closeEditModal();
                location.reload();
            } else {
                notify.error(data.error);
            }
        });
    });
    
    // تبديل الحالة
    document.querySelectorAll('.btn-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            fetch('<?= url('users') ?>/' + id + '/toggle', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        });
    });
});
</script>

<?php
$content = ob_get_clean();
require VIEWS_PATH . 'layouts/main.php';
?>
