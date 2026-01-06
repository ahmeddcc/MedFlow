<?php
/**
 * متحكم الصلاحيات
 * Permission Controller
 */
class PermissionController
{
    /**
     * عرض صفحة إدارة الصلاحيات لمستخدم معين
     */
    public function edit(int $userId): void
    {
        AuthController::checkSession();
        AuthController::requireRole('admin');
        
        // جلب بيانات المستخدم
        $user = Database::fetchOne("SELECT id, full_name, role FROM users WHERE id = ?", [$userId]);
        if (!$user) {
            flash('error', 'المستخدم غير موجود');
            redirect('users');
        }
        
        // جلب جميع الصلاحيات المتاحة
        $allPermissions = Database::fetchAll("SELECT * FROM permissions ORDER BY permission_group, id");
        
        // جلب صلاحيات المستخدم الحالية
        $userPermissions = Database::fetchAll(
            "SELECT permission_id FROM user_permissions WHERE user_id = ?", 
            [$userId]
        );
        $currentPermissions = array_column($userPermissions, 'permission_id');
        
        // تجميع الصلاحيات حسب المجموعة
        $groupedPermissions = [];
        foreach ($allPermissions as $perm) {
            $groupedPermissions[$perm['permission_group']][] = $perm;
        }
        
        require VIEWS_PATH . 'users/permissions.php';
    }
    
    /**
     * حفظ الصلاحيات
     */
    public function update(int $userId): void
    {
        AuthController::checkSession();
        AuthController::requireRole('admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('users');
        }
        
        $permissions = $_POST['permissions'] ?? [];
        
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            
            // حذف الصلاحيات القديمة
            Database::query("DELETE FROM user_permissions WHERE user_id = ?", [$userId]);
            
            // إضافة الصلاحيات الجديدة
            if (!empty($permissions)) {
                $sql = "INSERT INTO user_permissions (user_id, permission_id) VALUES (?, ?)";
                foreach ($permissions as $permId) {
                    Database::insert('user_permissions', [
                        'user_id' => $userId, 
                        'permission_id' => $permId
                    ]);
                }
            }
            
            $db->commit();
            
            // تحديث السيشن إذا كان المستخدم الحالي هو نفسه الذي يتم تعديله
            if ($userId == $_SESSION['user_id']) {
                AuthController::loadUserPermissions($userId);
            }
            
            flash('success', 'تم تحديث صلاحيات المستخدم بنجاح');
            
        } catch (Exception $e) {
            $db->rollBack();
            flash('error', 'حدث خطأ أثناء تحديث الصلاحيات');
        }
        
        redirect('users');
    }
}
