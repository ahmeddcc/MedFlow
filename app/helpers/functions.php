<?php
/**
 * =====================================================
 * MedFlow - الدوال المساعدة
 * =====================================================
 */

/**
 * إعادة التوجيه
 */
function redirect(string $path): void
{
    header('Location: ' . BASE_URL . ltrim($path, '/'));
    exit;
}

/**
 * الحصول على URL كامل
 */
function url(string $path = ''): string
{
    return BASE_URL . ltrim($path, '/');
}

/**
 * الحصول على URL للأصول
 */
function asset(string $path): string
{
    return ASSETS_URL . ltrim($path, '/');
}

/**
 * تنظيف المدخلات
 */
function clean(mixed $data): mixed
{
    if (is_array($data)) {
        return array_map('clean', $data);
    }
    return htmlspecialchars(trim((string) $data), ENT_QUOTES, 'UTF-8');
}

/**
 * إنشاء رمز CSRF
 */
function generateCsrfToken(): string
{
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * التحقق من رمز CSRF
 */
function verifyCsrfToken(string $token): bool
{
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * حقل CSRF مخفي
 */
function csrfField(): string
{
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . generateCsrfToken() . '">';
}

/**
 * التحقق من تسجيل الدخول
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * الحصول على المستخدم الحالي
 */
function currentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }
    return $_SESSION['user'] ?? null;
}

/**
 * التحقق من الدور
 */
function hasRole(string ...$roles): bool
{
    $user = currentUser();
    if (!$user) {
        return false;
    }
    return in_array($user['role'], $roles);
}

/**
 * التحقق من أن المستخدم طبيب
 */
function isDoctor(): bool
{
    return hasRole('doctor', 'admin');
}

/**
 * التحقق من أن المستخدم مساعد
 */
function isAssistant(): bool
{
    return hasRole('assistant');
}

/**
 * الحصول على اللغة الحالية
 */
function currentLanguage(): string
{
    return $_SESSION['language'] ?? DEFAULT_LANGUAGE;
}

/**
 * ترجمة نص
 */
function __(string $key, array $replace = []): string
{
    static $translations = null;
    
    if ($translations === null) {
        $langFile = APP_PATH . 'lang/' . currentLanguage() . '.php';
        $translations = file_exists($langFile) ? require $langFile : [];
    }
    
    $text = $translations[$key] ?? $key;
    
    foreach ($replace as $search => $value) {
        $text = str_replace(':' . $search, $value, $text);
    }
    
    return $text;
}

/**
 * رسائل الفلاش
 */
function flash(string $type, string $message = null): mixed
{
    if ($message !== null) {
        $_SESSION['flash'][$type] = $message;
        return null;
    }
    
    $msg = $_SESSION['flash'][$type] ?? null;
    unset($_SESSION['flash'][$type]);
    return $msg;
}

/**
 * تعيين رسالة فلاش (اسم مستعار)
 */
function setFlash(string $type, string $message): void
{
    flash($type, $message);
}

/**
 * عرض رسائل الفلاش
 */
function showFlashMessages(): string
{
    $html = '';
    $types = ['success', 'error', 'warning', 'info'];
    
    foreach ($types as $type) {
        $message = flash($type);
        if ($message) {
            $icon = match($type) {
                'success' => 'check-circle',
                'error' => 'x-circle',
                'warning' => 'alert-triangle',
                'info' => 'info',
                default => 'bell'
            };
            
            $html .= <<<HTML
            <div class="alert alert-{$type}" role="alert">
                <i data-feather="{$icon}"></i>
                <span>{$message}</span>
                <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                    <i data-feather="x"></i>
                </button>
            </div>
            HTML;
        }
    }
    
    return $html;
}

/**
 * تنسيق التاريخ
 */
function formatDate(?string $date, string $format = 'Y-m-d'): string
{
    if (empty($date)) {
        return '';
    }
    return date($format, strtotime($date));
}

/**
 * تنسيق التاريخ بالعربي
 */
function formatDateArabic(?string $date): string
{
    if (empty($date)) {
        return '';
    }
    
    $timestamp = strtotime($date);
    $months = [
        1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
        5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
        9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
    ];
    
    $day = date('j', $timestamp);
    $month = $months[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp);
    
    return "{$day} {$month} {$year}";
}

/**
 * حساب العمر
 */
function calculateAge(?string $birthDate): ?int
{
    if (empty($birthDate)) {
        return null;
    }
    
    $birth = new DateTime($birthDate);
    $now = new DateTime();
    return $birth->diff($now)->y;
}

/**
 * تنسيق الرقم
 */
function formatNumber(float $number, int $decimals = 2): string
{
    return number_format($number, $decimals, '.', ',');
}

/**
 * تنسيق المبلغ المالي
 */
function formatMoney(float $amount): string
{
    return formatNumber($amount) . ' ج.م';
}

/**
 * توليد معرف فريد
 */
function generateUniqueId(string $prefix = ''): string
{
    return $prefix . uniqid() . bin2hex(random_bytes(4));
}

/**
 * الحصول على IP المستخدم
 */
function getUserIp(): string
{
    $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    
    return 'unknown';
}

/**
 * تسجيل في السجل
 */
function logAction(string $action, ?string $table = null, ?int $recordId = null, ?array $oldValues = null, ?array $newValues = null): void
{
    try {
        Database::insert('activity_logs', [
            'user_id' => $_SESSION['user_id'] ?? null,
            'action' => $action,
            'table_name' => $table,
            'record_id' => $recordId,
            'details' => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : ($oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null),
            'ip_address' => getUserIp()
        ]);
    } catch (Exception $e) {
        // تجاهل أخطاء التسجيل
    }
}

/**
 * تحديد نوع الملف
 */
function getFileType(string $extension): string
{
    $types = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'],
        'text' => ['txt', 'csv', 'json', 'xml']
    ];
    
    foreach ($types as $type => $extensions) {
        if (in_array(strtolower($extension), $extensions)) {
            return $type;
        }
    }
    
    return 'other';
}

/**
 * تحويل الأرقام العربية إلى إنجليزية
 */
function arabicToEnglishNumbers(string $string): string
{
    $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($arabic, $english, $string);
}

/**
 * تحويل الأرقام الإنجليزية إلى عربية
 */
function englishToArabicNumbers(string $string): string
{
    $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($english, $arabic, $string);
}

/**
 * JSON response
 */
function jsonResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * التحقق من طلب AJAX
 */
function isAjax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * الحصول على إعداد من قاعدة البيانات
 */
function getSetting(string $key, mixed $default = null): mixed
{
    static $settings = null;
    
    if ($settings === null) {
        $results = Database::fetchAll("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    
    return $settings[$key] ?? $default;
}

/**
 * تحديث إعداد
 */
function setSetting(string $key, mixed $value): bool
{
    $exists = Database::fetchOne("SELECT id FROM settings WHERE setting_key = ?", [$key]);
    
    if ($exists) {
        Database::update('settings', ['setting_value' => $value], 'setting_key = ?', [$key]);
    } else {
        Database::insert('settings', ['setting_key' => $key, 'setting_value' => $value]);
    }
    
    return true;
}
