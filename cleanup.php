<?php
/**
 * ملف تنظيف - احذف هذا الملف بعد التشغيل
 * http://localhost/MedFlow/cleanup.php
 */

$files = [
    'diagnose.php',
    'test_save.php',
    'full_check.php',
    'debug_router.php',
    'settings_direct.php',
    'test_settings_controller.php',
];

echo "<h1>🧹 تنظيف ملفات الاختبار</h1>";
echo "<ul>";

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        if (unlink($path)) {
            echo "<li style='color:green'>✅ تم حذف: $file</li>";
        } else {
            echo "<li style='color:red'>❌ فشل حذف: $file</li>";
        }
    } else {
        echo "<li style='color:gray'>⚪ غير موجود: $file</li>";
    }
}

echo "</ul>";
echo "<p><strong>الآن احذف هذا الملف يدوياً:</strong> cleanup.php</p>";
echo "<p><a href='settings'>← العودة للإعدادات</a></p>";
?>
