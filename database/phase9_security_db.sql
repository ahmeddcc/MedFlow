-- =====================================================
-- MedFlow - Phase 9: Security & Licensing
-- الأمان والترخيص
-- =====================================================

-- إعدادات الترخيص والأمان
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
    ('license_key', '', 'text', 'مفتاح الترخيص'),
    ('license_status', 'trial', 'text', 'حالة الترخيص'),
    ('license_expiry', '', 'text', 'تاريخ انتهاء الترخيص'),
    ('app_version', '1.0.0', 'text', 'إصدار التطبيق'),
    ('developer_name', 'MedFlow Team', 'text', 'اسم المطور'),
    ('developer_email', 'support@medflow.com', 'text', 'بريد المطور'),
    ('developer_phone', '', 'text', 'هاتف المطور'),
    ('enable_audit_log', '1', 'boolean', 'تفعيل سجل النشاط'),
    ('session_timeout', '60', 'number', 'مهلة الجلسة (دقائق)'),
    ('max_login_attempts', '5', 'number', 'الحد الأقصى لمحاولات الدخول')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- جدول محاولات تسجيل الدخول الفاشلة
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(100) NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_ip (ip_address),
    INDEX idx_attempted_at (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='محاولات تسجيل الدخول الفاشلة';

-- تنظيف المحاولات القديمة (أكثر من 24 ساعة)
-- يمكن تنفيذ هذا الأمر دورياً
-- DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 24 HOUR);
