<?php
$pageTitle = 'الملف الشخصي';
ob_start();

$user = currentUser();

// تحديث الملف الشخصي
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = clean($_POST['full_name'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // تحديث الاسم
    if (!empty($fullName) && $fullName !== $user['full_name']) {
        Database::execute(
            "UPDATE users SET full_name = ? WHERE id = ?",
            [$fullName, $user['id']]
        );
        $_SESSION['user']['full_name'] = $fullName;
    }
    
    // تغيير كلمة المرور
    if (!empty($newPassword)) {
        if (empty($currentPassword)) {
            $errors[] = 'يرجى إدخال كلمة المرور الحالية';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $errors[] = 'كلمة المرور الحالية غير صحيحة';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'كلمة المرور الجديدة غير متطابقة';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            Database::execute(
                "UPDATE users SET password = ? WHERE id = ?",
                [$hashedPassword, $user['id']]
            );
        }
    }
    
    if (empty($errors)) {
        setFlash('success', 'تم تحديث الملف الشخصي بنجاح');
        redirect('profile');
    } else {
        setFlash('error', implode('<br>', $errors));
    }
    
    // إعادة جلب بيانات المستخدم
    $user = Database::fetchOne("SELECT * FROM users WHERE id = ?", [$user['id']]);
}
?>

<div class="profile-page">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">الملف الشخصي</h2>
        </div>
        <form method="post">
            <div class="card-body">
                <div class="profile-avatar">
                    <div class="avatar-large">
                        <?= mb_substr($user['full_name'], 0, 1, 'UTF-8') ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">اسم المستخدم</label>
                    <input type="text" class="form-control" value="<?= $user['username'] ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label class="form-label">الاسم الكامل</label>
                    <input type="text" class="form-control" name="full_name" value="<?= $user['full_name'] ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">الصلاحية</label>
                    <input type="text" class="form-control" value="<?= $user['role'] === 'admin' ? 'مدير' : ($user['role'] === 'doctor' ? 'طبيب' : 'مساعد') ?>" disabled>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
            </div>
        </form>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">تغيير كلمة المرور</h2>
        </div>
        <form method="post">
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">كلمة المرور الحالية</label>
                    <input type="password" class="form-control" name="current_password">
                </div>
                <div class="form-group">
                    <label class="form-label">كلمة المرور الجديدة</label>
                    <input type="password" class="form-control" name="new_password">
                </div>
                <div class="form-group">
                    <label class="form-label">تأكيد كلمة المرور</label>
                    <input type="password" class="form-control" name="confirm_password">
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-warning">تغيير كلمة المرور</button>
            </div>
        </form>
    </div>
</div>

<style>
.profile-page {
    max-width: 600px;
    margin: 0 auto;
}

.card {
    margin-bottom: var(--spacing-xl);
}

.profile-avatar {
    text-align: center;
    margin-bottom: var(--spacing-xl);
}

.avatar-large {
    width: 80px;
    height: 80px;
    border-radius: var(--radius-full);
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 700;
}

.card-footer {
    padding: var(--spacing-lg);
    border-top: 1px solid var(--border-light);
    background: var(--bg-secondary);
    display: flex;
    justify-content: flex-end;
}
</style>

<?php
$content = ob_get_clean();
require VIEWS_PATH . 'layouts/main.php';
?>
