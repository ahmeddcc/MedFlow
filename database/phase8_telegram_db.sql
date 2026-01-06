-- =====================================================
-- MedFlow - Phase 8: Reports & Telegram
-- إعدادات التقارير و Telegram
-- =====================================================

-- إضافة إعدادات Telegram
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
    ('telegram_bot_token', '', 'text', 'Telegram Bot Token'),
    ('telegram_chat_id', '', 'text', 'Telegram Chat ID'),
    ('telegram_enabled', '0', 'boolean', 'تفعيل إشعارات Telegram'),
    ('telegram_notify_new_patient', '0', 'boolean', 'إشعار بالمريض الجديد'),
    ('telegram_notify_new_turn', '0', 'boolean', 'إشعار بالدور الجديد'),
    ('telegram_daily_report', '0', 'boolean', 'التقرير اليومي'),
    ('telegram_daily_report_time', '20:00', 'text', 'وقت التقرير اليومي')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- جدول سجل إشعارات Telegram
CREATE TABLE IF NOT EXISTS telegram_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_type VARCHAR(50) NOT NULL COMMENT 'نوع الرسالة',
    message_text TEXT COMMENT 'نص الرسالة',
    status ENUM('success', 'failed') DEFAULT 'success',
    error_message VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='سجل إشعارات Telegram';

-- فهرس للبحث
CREATE INDEX idx_telegram_logs_date ON telegram_logs(created_at);
CREATE INDEX idx_telegram_logs_type ON telegram_logs(message_type);
