<?php
$pageTitle = 'إدارة الصلاحيات - ' . $user['full_name'];
ob_start();
?>

<div class="page-header">
    <h1>إدارة الصلاحيات: <?= $user['full_name'] ?></h1>
    <div class="actions">
        <a href="<?= url('users') ?>" class="btn btn-secondary">عودة للمستخدمين</a>
    </div>
</div>

<form action="<?= url('users/permissions/update/' . $user['id']) ?>" method="POST" class="permissions-form">
    <?= csrf_field() ?>
    
    <div class="permissions-grid">
        <?php foreach ($groupedPermissions as $group => $permissions): ?>
        <div class="permission-group-card">
            <div class="group-header">
                <h3><?= ucfirst($group) ?></h3>
                <label class="select-all-label">
                    <input type="checkbox" class="select-all-group" data-group="<?= $group ?>">
                    تحديد الكل
                </label>
            </div>
            <div class="group-body">
                <?php foreach ($permissions as $perm): ?>
                <label class="permission-item">
                    <input type="checkbox" name="permissions[]" value="<?= $perm['id'] ?>" 
                        class="perm-checkbox group-<?= $group ?>"
                        <?= in_array($perm['id'], $currentPermissions) ? 'checked' : '' ?>>
                    <span class="perm-info">
                        <span class="perm-name"><?= $perm['permission_name'] ?></span>
                        <span class="perm-desc"><?= $perm['description'] ?></span>
                    </span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="form-actions sticky-footer">
        <button type="submit" class="btn btn-primary btn-lg">حفظ التغييرات</button>
    </div>
</form>

<style>
.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--spacing-lg);
    margin-bottom: 80px;
}

.permission-group-card {
    background: var(--bg-card);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-light);
    overflow: hidden;
}

.group-header {
    background: var(--bg-secondary);
    padding: var(--spacing-md) var(--spacing-lg);
    border-bottom: 1px solid var(--border-light);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.group-header h3 {
    margin: 0;
    font-size: var(--font-size-md);
    font-weight: 700;
}

.group-body {
    padding: var(--spacing-md);
}

.permission-item {
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-md);
    padding: var(--spacing-sm);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: background 0.2s;
}

.permission-item:hover {
    background: var(--bg-secondary);
}

.perm-info {
    display: flex;
    flex-direction: column;
}

.perm-name {
    font-weight: 600;
    color: var(--text-primary);
}

.perm-desc {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
}

.sticky-footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: var(--bg-card);
    padding: var(--spacing-md);
    box-shadow: 0 -4px 10px rgba(0,0,0,0.1);
    display: flex;
    justify-content: center;
    z-index: 100;
}
</style>

<script>
document.querySelectorAll('.select-all-group').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const group = this.dataset.group;
        const inputs = document.querySelectorAll('.group-' + group);
        inputs.forEach(input => input.checked = this.checked);
    });
});
</script>

<?php
$content = ob_get_clean();
require VIEWS_PATH . 'layouts/main.php';
?>
