<?php
/**
 * متحكم المستخدمين
 * User Controller
 */
class UserController
{
    /**
     * قائمة المستخدمين
     */
    public function index(): void
    {
        AuthController::checkSession();
        AuthController::requireRole('admin');
        
        $users = Database::fetchAll(
            "SELECT id, username, full_name, role, is_active, last_login, created_at 
             FROM users ORDER BY created_at DESC"
        );
        
        require VIEWS_PATH . 'users/index.php';
    }
    
    /**
     * إنشاء مستخدم جديد
     */
    public function create(): void
    {
        AuthController::checkSession();
        AuthController::requireRole('admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'طريقة غير مسموحة'], 405);
            return;
        }
        
        $username = clean($_POST['username'] ?? '');
        $fullName = clean($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = clean($_POST['role'] ?? 'assistant');
        
        // التحقق
        if (empty($username) || empty($fullName) || empty($password)) {
            jsonResponse(['error' => 'جميع الحقول مطلوبة']);
            return;
        }
        
        // التحقق من وجود المستخدم
        $exists = Database::fetchOne(
            "SELECT id FROM users WHERE username = ?",
            [$username]
        );
        
        if ($exists) {
            jsonResponse(['error' => 'اسم المستخدم موجود مسبقاً']);
            return;
        }
        
        // إنشاء المستخدم
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        Database::query(
            "INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)",
            [$username, $hashedPassword, $fullName, $role]
        );
        
        jsonResponse([
            'success' => true,
            'message' => 'تم إنشاء المستخدم بنجاح'
        ]);
    }
    
    /**
     * تحديث مستخدم
     */
    public function update(int $id): void
    {
        AuthController::checkSession();
        AuthController::requireRole('admin');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'طريقة غير مسموحة'], 405);
            return;
        }
        
        $fullName = clean($_POST['full_name'] ?? '');
        $role = clean($_POST['role'] ?? 'assistant');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $newPassword = $_POST['new_password'] ?? '';
        
        // تحديث البيانات
        Database::query(
            "UPDATE users SET full_name = ?, role = ?, is_active = ? WHERE id = ?",
            [$fullName, $role, $isActive, $id]
        );
        
        // تحديث كلمة المرور إذا تم توفيرها
        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            Database::query(
                "UPDATE users SET password = ? WHERE id = ?",
                [$hashedPassword, $id]
            );
        }
        
        jsonResponse([
            'success' => true,
            'message' => 'تم تحديث المستخدم بنجاح'
        ]);
    }
    
    /**
     * حذف مستخدم
     */
    public function delete(int $id): void
    {
        AuthController::checkSession();
        AuthController::requireRole('admin');
        
        // منع حذف المستخدم الحالي
        if ($id === currentUser()['id']) {
            jsonResponse(['error' => 'لا يمكنك حذف حسابك']);
            return;
        }
        
        Database::query("DELETE FROM users WHERE id = ?", [$id]);
        
        jsonResponse([
            'success' => true,
            'message' => 'تم حذف المستخدم'
        ]);
    }
    
    /**
     * تبديل حالة المستخدم
     */
    public function toggleStatus(int $id): void
    {
        AuthController::checkSession();
        AuthController::requireRole('admin');
        
        Database::query(
            "UPDATE users SET is_active = NOT is_active WHERE id = ?",
            [$id]
        );
        
        jsonResponse(['success' => true]);
    }
}
