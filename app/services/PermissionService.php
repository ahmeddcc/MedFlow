<?php
/**
 * خدمة الصلاحيات
 * Permission Service
 */
class PermissionService
{
    private static array $userPermissions = [];
    
    /**
     * تحميل صلاحيات المستخدم
     */
    public static function loadUserPermissions(int $userId): void
    {
        self::$userPermissions[$userId] = Database::fetchAll(
            "SELECT p.permission_key 
             FROM user_permissions up 
             JOIN permissions p ON up.permission_id = p.id 
             WHERE up.user_id = ?",
            [$userId]
        );
    }
    
    /**
     * التحقق من صلاحية
     */
    public static function hasPermission(string $permissionKey, ?int $userId = null): bool
    {
        $userId = $userId ?? (currentUser()['id'] ?? 0);
        
        // المدير لديه كل الصلاحيات
        if (currentUser()['role'] === 'admin') {
            return true;
        }
        
        // تحميل الصلاحيات إذا لم تكن محملة
        if (!isset(self::$userPermissions[$userId])) {
            self::loadUserPermissions($userId);
        }
        
        // البحث عن الصلاحية
        foreach (self::$userPermissions[$userId] as $perm) {
            if ($perm['permission_key'] === $permissionKey) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * التحقق من صلاحية مع توقف
     */
    public static function requirePermission(string $permissionKey): void
    {
        if (!self::hasPermission($permissionKey)) {
            if (isAjax()) {
                jsonResponse(['error' => 'ليس لديك صلاحية لهذا الإجراء'], 403);
            } else {
                setFlash('error', 'ليس لديك صلاحية لهذا الإجراء');
                redirect('dashboard');
            }
            exit;
        }
    }
    
    /**
     * جلب كل الصلاحيات
     */
    public static function getAllPermissions(): array
    {
        return Database::fetchAll(
            "SELECT * FROM permissions ORDER BY permission_group, id"
        );
    }
    
    /**
     * جلب صلاحيات مستخدم
     */
    public static function getUserPermissions(int $userId): array
    {
        return Database::fetchAll(
            "SELECT permission_id FROM user_permissions WHERE user_id = ?",
            [$userId]
        );
    }
    
    /**
     * تحديث صلاحيات مستخدم
     */
    public static function updateUserPermissions(int $userId, array $permissionIds): void
    {
        // حذف الصلاحيات القديمة
        Database::query(
            "DELETE FROM user_permissions WHERE user_id = ?",
            [$userId]
        );
        
        // إضافة الصلاحيات الجديدة
        foreach ($permissionIds as $permId) {
            Database::query(
                "INSERT INTO user_permissions (user_id, permission_id) VALUES (?, ?)",
                [$userId, $permId]
            );
        }
        
        // تحديث الكاش
        unset(self::$userPermissions[$userId]);
    }
    
    /**
     * جلب مجموعات الصلاحيات
     */
    public static function getPermissionGroups(): array
    {
        return [
            'dashboard' => 'لوحة التحكم',
            'patients' => 'المرضى',
            'attachments' => 'المرفقات',
            'waiting' => 'قائمة الانتظار',
            'reserved' => 'الأرقام المحجوزة',
            'representatives' => 'المناديب',
            'invoices' => 'الفواتير',
            'services' => 'الخدمات',
            'prescriptions' => 'الروشتة',
            'lab' => 'التحاليل',
            'print' => 'الطباعة',
            'reports' => 'التقارير',
            'settings' => 'الإعدادات',
            'users' => 'المستخدمين',
        ];
    }
}

/**
 * دالة مختصرة للتحقق من الصلاحية
 */
function can(string $permission): bool
{
    return PermissionService::hasPermission($permission);
}

/**
 * دالة مختصرة للتحقق مع إيقاف
 */
function authorize(string $permission): void
{
    PermissionService::requirePermission($permission);
}
