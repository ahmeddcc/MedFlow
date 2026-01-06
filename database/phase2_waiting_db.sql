-- =====================================================
-- MedFlow - المرحلة الثانية: قائمة انتظار المرضى
-- تاريخ الإنشاء: 2026-01-04
-- =====================================================

USE medflow_db;

-- =====================================================
-- جدول قائمة الانتظار
-- =====================================================
CREATE TABLE IF NOT EXISTS waiting_list (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL COMMENT 'معرف المريض',
    turn_number INT NOT NULL COMMENT 'رقم الدور',
    status ENUM('waiting', 'called', 'entered', 'completed', 'cancelled', 'paused') 
        NOT NULL DEFAULT 'waiting' COMMENT 'حالة الدور',
    visit_type ENUM('checkup', 'followup', 'consultation', 'emergency') 
        NOT NULL DEFAULT 'checkup' COMMENT 'نوع الزيارة',
    call_count INT NOT NULL DEFAULT 0 COMMENT 'عدد مرات النداء',
    called_at DATETIME NULL COMMENT 'وقت الاستدعاء',
    entered_at DATETIME NULL COMMENT 'وقت الدخول',
    completed_at DATETIME NULL COMMENT 'وقت الانتهاء',
    notes TEXT NULL COMMENT 'ملاحظات',
    created_by INT NULL COMMENT 'أنشأه',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_patient_id (patient_id),
    INDEX idx_turn_number (turn_number),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_visit_type (visit_type),
    
    CONSTRAINT fk_waiting_patient 
        FOREIGN KEY (patient_id) REFERENCES patients(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_waiting_created_by 
        FOREIGN KEY (created_by) REFERENCES users(id) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- جدول إعدادات قائمة الانتظار
-- =====================================================
CREATE TABLE IF NOT EXISTS waiting_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    description VARCHAR(255) NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- جدول سجل النداءات
-- =====================================================
CREATE TABLE IF NOT EXISTS call_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    waiting_id INT NOT NULL,
    turn_number INT NOT NULL,
    called_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    called_by INT NULL,
    
    INDEX idx_waiting_id (waiting_id),
    
    CONSTRAINT fk_call_waiting 
        FOREIGN KEY (waiting_id) REFERENCES waiting_list(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_call_user 
        FOREIGN KEY (called_by) REFERENCES users(id) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- إعدادات قائمة الانتظار الافتراضية
-- =====================================================
INSERT INTO waiting_settings (setting_key, setting_value, description) VALUES
('is_paused', '0', 'هل قائمة الانتظار متوقفة'),
('current_turn', '0', 'الدور الحالي'),
('daily_counter', '0', 'عداد اليوم'),
('last_reset_date', CURDATE(), 'تاريخ آخر إعادة تعيين'),
('voice_enabled', '1', 'تفعيل النداء الصوتي'),
('voice_repeat', '2', 'عدد مرات تكرار النداء'),
('display_mode', 'full', 'وضع العرض على الشاشة'),
('auto_call_interval', '0', 'فترة النداء التلقائي بالثواني (0 = معطل)')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- =====================================================
-- إنشاء View لعرض قائمة الانتظار مع بيانات المرضى
-- =====================================================
CREATE OR REPLACE VIEW v_waiting_list AS
SELECT 
    w.id,
    w.turn_number,
    w.status,
    w.visit_type,
    w.call_count,
    w.called_at,
    w.entered_at,
    w.completed_at,
    w.notes,
    w.created_at,
    p.id AS patient_id,
    p.full_name AS patient_name,
    p.electronic_number,
    p.phone AS patient_phone,
    p.barcode,
    p.gender,
    CASE w.status
        WHEN 'waiting' THEN 'في الانتظار'
        WHEN 'called' THEN 'تم الاستدعاء'
        WHEN 'entered' THEN 'في الكشف'
        WHEN 'completed' THEN 'منتهي'
        WHEN 'cancelled' THEN 'ملغي'
        WHEN 'paused' THEN 'متوقف'
    END AS status_text,
    CASE w.visit_type
        WHEN 'checkup' THEN 'كشف'
        WHEN 'followup' THEN 'متابعة'
        WHEN 'consultation' THEN 'استشارة'
        WHEN 'emergency' THEN 'طوارئ'
    END AS visit_type_text
FROM waiting_list w
JOIN patients p ON w.patient_id = p.id;
