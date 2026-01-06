-- =====================================================
-- MedFlow - قاعدة بيانات المرحلة الرابعة
-- الأرقام المحجوزة للمرضى المميزين
-- =====================================================

USE medflow_db;

-- =====================================================
-- جدول الأرقام المحجوزة
-- =====================================================
CREATE TABLE IF NOT EXISTS reserved_numbers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL UNIQUE COMMENT 'معرف المريض',
    reserved_number INT NOT NULL COMMENT 'الرقم المحجوز',
    notes VARCHAR(255) DEFAULT NULL COMMENT 'ملاحظات',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'نشط',
    created_by INT DEFAULT NULL COMMENT 'أنشأه',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    UNIQUE KEY unique_reserved_number (reserved_number, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='الأرقام المحجوزة للمرضى المميزين';

-- =====================================================
-- عرض الأرقام المحجوزة مع بيانات المرضى
-- =====================================================
CREATE OR REPLACE VIEW v_reserved_numbers AS
SELECT 
    r.id,
    r.patient_id,
    r.reserved_number,
    p.full_name AS patient_name,
    p.electronic_number,
    p.phone,
    r.notes,
    r.is_active,
    r.created_at
FROM reserved_numbers r
JOIN patients p ON r.patient_id = p.id
WHERE r.is_active = 1
ORDER BY r.reserved_number;

-- =====================================================
-- فهرس لتحسين الأداء
-- =====================================================
CREATE INDEX IF NOT EXISTS idx_reserved_number ON reserved_numbers(reserved_number);
CREATE INDEX IF NOT EXISTS idx_reserved_patient ON reserved_numbers(patient_id);
