<?php
/**
 * =====================================================
 * MedFlow - متحكم المصادقة
 * =====================================================
 */

class AuthController
{
    /**
     * عرض صفحة تسجيل الدخول
     */
    public function showLogin(): void
    {
        // إذا كان المستخدم مسجل الدخول، توجيهه للوحة التحكم
        if (isLoggedIn()) {
            redirect('dashboard');
        }
        
        require VIEWS_PATH . 'auth/login.php';
    }
    
    /**
     * معالجة تسجيل الدخول
     */
    public function login(): void
    {
        // التحقق من طريقة الطلب
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('login');
        }
        
        // التحقق من CSRF
        if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            flash('error', 'طلب غير صالح');
            redirect('login');
        }
        
        $username = clean($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        // التحقق من البيانات
        if (empty($username) || empty($password)) {
            flash('error', 'يرجى إدخال اسم المستخدم وكلمة المرور');
            redirect('login');
        }
        
        // البحث عن المستخدم
        $user = Database::fetchOne(
            "SELECT * FROM users WHERE username = ? AND is_active = 1",
            [$username]
        );
        
        if (!$user || !password_verify($password, $user['password'])) {
            flash('error', 'اسم المستخدم أو كلمة المرور غير صحيحة');
            logAction('login_failed', 'users', null, null, ['username' => $username]);
            redirect('login');
        }
        
        // تسجيل الدخول
        $this->createSession($user, $remember);
        
        // تحديث آخر تسجيل دخول
        Database::update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
        
        // تسجيل في السجل
        logAction('login_success', 'users', $user['id']);
        
        flash('success', 'مرحباً ' . $user['full_name']);
        redirect('dashboard');
    }
    
    /**
     * تسجيل الخروج
     */
    public function logout(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if ($userId) {
            logAction('logout', 'users', $userId);
        }
        
        // حذف الجلسة
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        session_destroy();
        
        // حذف كوكي التذكر
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        flash('success', 'تم تسجيل الخروج بنجاح');
        redirect('login');
    }
    
    /**
     * إنشاء جلسة للمستخدم
     */
    private function createSession(array $user, bool $remember = false): void
    {
        // تجديد معرف الجلسة لمنع Session Fixation
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'role' => $user['role'],
            'language' => $user['language']
        ];
        $_SESSION['language'] = $user['language'];
        $_SESSION['login_time'] = time();
        
        // كوكي التذكر
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
            // يمكن حفظ التوكن في قاعدة البيانات لاحقاً
        }
    }
    
    /**
     * التحقق من الجلسة
     */
    public static function checkSession(): void
    {
        if (!isLoggedIn()) {
            if (isAjax()) {
                jsonResponse(['error' => 'غير مصرح', 'redirect' => url('login')], 401);
            }
            flash('error', 'يرجى تسجيل الدخول أولاً');
            redirect('login');
        }
        
        // التحقق من انتهاء الجلسة
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > SESSION_LIFETIME) {
            session_destroy();
            flash('error', 'انتهت صلاحية الجلسة');
            redirect('login');
        }
        
        // تحديث وقت النشاط
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * التحقق من الصلاحية
     */
    public static function hasPermission(string $permission): bool
    {
        if (!isLoggedIn()) return false;
        
        // المدير له كل الصلاحيات
        if ($_SESSION['user']['role'] === 'admin') return true;
        
        // التحقق من السيشن أولاً (لتقليل استعلامات القاعدة)
        if (!isset($_SESSION['user_permissions'])) {
            self::loadUserPermissions($_SESSION['user_id']);
        }
        
        return in_array($permission, $_SESSION['user_permissions']);
    }

    /**
     * تحميل صلاحيات المستخدم
     */
    public static function loadUserPermissions(int $userId): void
    {
        $permissions = Database::fetchAll(
            "SELECT p.permission_key 
             FROM permissions p
             JOIN user_permissions up ON p.id = up.permission_id
             WHERE up.user_id = ?",
            [$userId]
        );
        
        $_SESSION['user_permissions'] = array_column($permissions, 'permission_key');
    }

    /**
     * طلب صلاحية محددة
     */
    public static function requirePermission(string $permission): void
    {
        self::checkSession();
        
        if (!self::hasPermission($permission)) {
            if (isAjax()) {
                jsonResponse(['error' => 'ليس لديك الصلاحية اللازمة: ' . $permission], 403);
            }
            flash('error', 'ليس لديك الصلاحية للقيام بهذا الإجراء');
            redirect('dashboard');
        }
    }

    /**
     * التحقق من الدور
     */
    public static function requireRole(string ...$roles): void
    {
        if (!hasRole(...$roles)) {
            if (isAjax()) {
                jsonResponse(['error' => 'غير مصرح بالوصول'], 403);
            }
            flash('error', 'غير مصرح لك بالوصول إلى هذه الصفحة');
            redirect('dashboard');
        }
    }
}
