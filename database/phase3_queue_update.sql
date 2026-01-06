-- إضافة عمود الأولوية لجدول قائمة الانتظار
ALTER TABLE waiting_list ADD COLUMN IF NOT EXISTS priority_level ENUM('normal', 'urgent', 'vip') DEFAULT 'normal' AFTER visit_type;

-- إنشاء جدول الأرقام المحجوزة
CREATE TABLE IF NOT EXISTS reserved_numbers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    reserved_number INT NOT NULL,
    notes TEXT,
    is_active TINYINT DEFAULT 1,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    UNIQUE KEY unique_reservation (patient_id, is_active),
    UNIQUE KEY unique_number (reserved_number, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إنشاء View للأرقام المحجوزة
CREATE OR REPLACE VIEW v_reserved_numbers AS
SELECT r.*, p.full_name as patient_name, p.phone
FROM reserved_numbers r
JOIN patients p ON r.patient_id = p.id
WHERE r.is_active = 1;

-- تحديث View قائمة الانتظار لتشمل الأولوية
CREATE OR REPLACE VIEW v_waiting_list AS
SELECT w.*, p.full_name as patient_name, p.electronic_number, p.phone
FROM waiting_list w
JOIN patients p ON w.patient_id = p.id;
