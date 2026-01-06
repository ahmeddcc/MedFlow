-- =====================================================
-- MedFlow - قاعدة بيانات المرحلة الثالثة
-- إدارة المناديب والشركات
-- =====================================================

USE medflow_db;

-- =====================================================
-- جدول الشركات
-- =====================================================
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT 'اسم الشركة',
    letter CHAR(1) NOT NULL UNIQUE COMMENT 'الحرف المخصص (A-Z)',
    phone VARCHAR(20) DEFAULT NULL COMMENT 'هاتف الشركة',
    email VARCHAR(100) DEFAULT NULL COMMENT 'البريد الإلكتروني',
    address TEXT DEFAULT NULL COMMENT 'العنوان',
    notes TEXT DEFAULT NULL COMMENT 'ملاحظات',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'الحالة',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='شركات الأدوية';

-- =====================================================
-- جدول المناديب
-- =====================================================
CREATE TABLE IF NOT EXISTS representatives (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL COMMENT 'معرف الشركة',
    full_name VARCHAR(100) NOT NULL COMMENT 'الاسم الكامل',
    phone VARCHAR(20) DEFAULT NULL COMMENT 'الهاتف',
    email VARCHAR(100) DEFAULT NULL COMMENT 'البريد الإلكتروني',
    notes TEXT DEFAULT NULL COMMENT 'ملاحظات',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'الحالة',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='مناديب شركات الأدوية';

-- =====================================================
-- جدول قائمة انتظار المناديب
-- =====================================================
CREATE TABLE IF NOT EXISTS rep_waiting_list (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rep_id INT DEFAULT NULL COMMENT 'معرف المندوب (اختياري)',
    company_id INT DEFAULT NULL COMMENT 'معرف الشركة',
    turn_letter CHAR(1) NOT NULL COMMENT 'حرف الشركة',
    turn_number INT NOT NULL COMMENT 'الرقم التسلسلي',
    full_turn VARCHAR(10) GENERATED ALWAYS AS (CONCAT(turn_letter, turn_number)) STORED COMMENT 'الرقم الكامل (A1, B2...)',
    visitor_name VARCHAR(100) DEFAULT NULL COMMENT 'اسم الزائر (إذا لم يكن مندوب مسجل)',
    status ENUM('waiting', 'called', 'entered', 'completed', 'cancelled') DEFAULT 'waiting' COMMENT 'الحالة',
    notes TEXT DEFAULT NULL COMMENT 'ملاحظات',
    call_count INT DEFAULT 0 COMMENT 'عدد مرات النداء',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    called_at TIMESTAMP NULL DEFAULT NULL COMMENT 'وقت النداء',
    entered_at TIMESTAMP NULL DEFAULT NULL COMMENT 'وقت الدخول',
    completed_at TIMESTAMP NULL DEFAULT NULL COMMENT 'وقت الانتهاء',
    created_by INT DEFAULT NULL COMMENT 'أنشأه',
    FOREIGN KEY (rep_id) REFERENCES representatives(id) ON DELETE SET NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='قائمة انتظار المناديب';

-- =====================================================
-- جدول إعدادات انتظار المناديب
-- =====================================================
CREATE TABLE IF NOT EXISTS rep_waiting_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='إعدادات قائمة انتظار المناديب';

-- الإعدادات الافتراضية
INSERT INTO rep_waiting_settings (setting_key, setting_value) VALUES
('is_paused', '0'),
('current_turn', ''),
('last_reset_date', CURDATE())
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- =====================================================
-- جدول سجل نداء المناديب
-- =====================================================
CREATE TABLE IF NOT EXISTS rep_call_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    waiting_id INT NOT NULL,
    full_turn VARCHAR(10) NOT NULL,
    called_by INT DEFAULT NULL,
    called_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (waiting_id) REFERENCES rep_waiting_list(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='سجل نداءات المناديب';

-- =====================================================
-- عرض قائمة انتظار المناديب مع البيانات
-- =====================================================
CREATE OR REPLACE VIEW v_rep_waiting_list AS
SELECT 
    w.id,
    w.rep_id,
    w.company_id,
    w.turn_letter,
    w.turn_number,
    w.full_turn,
    COALESCE(r.full_name, w.visitor_name, 'زائر') AS visitor_name,
    c.name AS company_name,
    c.letter AS company_letter,
    w.status,
    CASE w.status
        WHEN 'waiting' THEN 'في الانتظار'
        WHEN 'called' THEN 'تم النداء'
        WHEN 'entered' THEN 'في الغرفة'
        WHEN 'completed' THEN 'منتهي'
        WHEN 'cancelled' THEN 'ملغي'
    END AS status_text,
    w.notes,
    w.call_count,
    w.created_at,
    w.called_at,
    w.entered_at,
    w.completed_at
FROM rep_waiting_list w
LEFT JOIN representatives r ON w.rep_id = r.id
LEFT JOIN companies c ON w.company_id = c.id;

-- =====================================================
-- بيانات تجريبية - شركات
-- =====================================================
INSERT INTO companies (name, letter, phone) VALUES
('شركة فايزر', 'A', '01000000001'),
('شركة نوفارتس', 'B', '01000000002'),
('شركة سانوفي', 'C', '01000000003'),
('شركة باير', 'D', '01000000004'),
('شركة أسترازينكا', 'E', '01000000005')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- =====================================================
-- فهارس لتحسين الأداء
-- =====================================================
CREATE INDEX IF NOT EXISTS idx_rep_waiting_date ON rep_waiting_list(created_at);
CREATE INDEX IF NOT EXISTS idx_rep_waiting_status ON rep_waiting_list(status);
CREATE INDEX IF NOT EXISTS idx_rep_waiting_letter ON rep_waiting_list(turn_letter);
