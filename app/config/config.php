<?php
/**
 * =====================================================
 * MedFlow - نظام إدارة العيادات
 * ملف الإعدادات الرئيسي
 * =====================================================
 */

// منع الوصول المباشر
if (!defined('MEDFLOW')) {
    define('MEDFLOW', true);
}

// =====================================================
// إعدادات البيئة
// =====================================================
define('APP_NAME', 'MedFlow');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // development | production

// =====================================================
// إعدادات قاعدة البيانات
// =====================================================
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'medflow_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// =====================================================
// إعدادات المسارات
// =====================================================
define('BASE_PATH', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR);
define('APP_PATH', BASE_PATH . 'app' . DIRECTORY_SEPARATOR);
define('ASSETS_PATH', BASE_PATH . 'assets' . DIRECTORY_SEPARATOR);
define('UPLOADS_PATH', BASE_PATH . 'uploads' . DIRECTORY_SEPARATOR);
define('VIEWS_PATH', APP_PATH . 'views' . DIRECTORY_SEPARATOR);

// URL الأساسي
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL', $protocol . '://' . $host . '/MedFlow/');
define('ASSETS_URL', BASE_URL . 'assets/');

// =====================================================
// إعدادات الجلسة
// =====================================================
define('SESSION_NAME', 'medflow_session');
define('SESSION_LIFETIME', 7200); // ساعتين

// =====================================================
// إعدادات الأمان
// =====================================================
define('HASH_COST', 10);
define('CSRF_TOKEN_NAME', 'csrf_token');

// =====================================================
// إعدادات اللغة
// =====================================================
define('DEFAULT_LANGUAGE', 'ar');
define('SUPPORTED_LANGUAGES', ['ar', 'en']);

// =====================================================
// إعدادات الملفات
// =====================================================
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10 ميجا
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// =====================================================
// إعدادات الباركود
// =====================================================
define('BARCODE_PREFIX', 'MF');
define('ELECTRONIC_NUMBER_PREFIX', 'MF');
define('ELECTRONIC_NUMBER_START', 1000);

// =====================================================
// إعدادات التصحيح
// =====================================================
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// =====================================================
// إعدادات المنطقة الزمنية
// =====================================================
date_default_timezone_set('Africa/Cairo');

// =====================================================
// إعدادات الترميز
// =====================================================
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
