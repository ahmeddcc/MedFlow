-- =====================================================
-- MedFlow - Phase 10: Advanced Telegram Integration
-- تيليجرام المتقدم (البوت المزدوج + التفاعل)
-- =====================================================

USE medflow_db;

-- 1. ربط حسابات تيليجرام بالمستخدمين (لأجل البوت التفاعلي)
-- نضيف الحقل فقط إذا لم يكن موجوداً
SET @dbname = DATABASE();
SET @tablename = "users";
SET @columnname = "telegram_user_id";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE users ADD COLUMN telegram_user_id VARCHAR(50) DEFAULT NULL COMMENT 'معرف تيليجرام للمستخدم'"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- فهرس للبحث السريع عن المستخدم باستخدام معرف تيليجرام
CREATE INDEX IF NOT EXISTS idx_users_telegram_id ON users(telegram_user_id);


-- 2. إعدادات "بوت الدعم الفني" (Support Bot) - منفصل عن بوت العمليات
-- هذا البوت مخصص للمطور/الدعم الفني لاستقبال الأخطاء فقط
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
    ('telegram_support_bot_token', '', 'text', 'Token بوت الدعم الفني (للأخطاء)'),
    ('telegram_support_chat_id', '', 'text', 'Chat ID لفريق الدعم الفني'),
    ('telegram_support_enabled', '1', 'boolean', 'تفعيل بوت الدعم الفني')
ON DUPLICATE KEY UPDATE setting_key = setting_key;


-- 3. جدول الأوامر التفاعلية (Interactive Commands State)
-- يستخدم لتخزين "حالة" المستخدم عند التفاعل مع البوت (مثل: المستخدم ضغط "حجز" وينتظر إدخال الاسم)
CREATE TABLE IF NOT EXISTS telegram_commands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    telegram_user_id VARCHAR(50) NOT NULL COMMENT 'من قام بالأمر',
    command VARCHAR(50) NOT NULL COMMENT 'الأمر الحالي (add_patient, book_ticket...)',
    step VARCHAR(50) DEFAULT 'start' COMMENT 'الخطوة الحالية (start, waiting_name, waiting_phone...)',
    temp_data TEXT DEFAULT NULL COMMENT 'بيانات مؤقتة (JSON) أثناء المحادثة',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_cmd_user (telegram_user_id),
    INDEX idx_cmd_updated (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='حالة الأوامر التفاعلية للبوت';


-- 4. جدول سجل الأخطاء التفصيلي (Enhanced Error Logs)
-- مخصص لتقارير الأخطاء التي ترسل لبوت الدعم
CREATE TABLE IF NOT EXISTS telegram_error_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    error_type VARCHAR(50) NOT NULL COMMENT 'نوع الخطأ (Exception, PHP Error, Fatal...)',
    error_message TEXT NOT NULL COMMENT 'رسالة الخطأ',
    file_path VARCHAR(255) DEFAULT NULL COMMENT 'الملف المسبب',
    line_number INT DEFAULT NULL COMMENT 'رقم السطر',
    stack_trace LONGTEXT DEFAULT NULL COMMENT 'تتبع الخطأ كاملاً',
    sent_status TINYINT(1) DEFAULT 0 COMMENT 'هل تم إرساله لتيليجرام؟',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_err_status (sent_status),
    INDEX idx_err_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='سجل أخطاء النظام التفصيلي';
