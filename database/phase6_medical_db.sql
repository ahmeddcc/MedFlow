-- =====================================================
-- MedFlow - قاعدة بيانات المرحلة السادسة
-- نظام الوصفات والتحاليل
-- =====================================================

USE medflow_db;

-- =====================================================
-- جدول الأدوية
-- =====================================================
CREATE TABLE IF NOT EXISTS medications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL COMMENT 'اسم الدواء',
    name_en VARCHAR(150) DEFAULT NULL COMMENT 'الاسم بالإنجليزية',
    category VARCHAR(50) DEFAULT NULL COMMENT 'التصنيف',
    default_dosage VARCHAR(100) DEFAULT NULL COMMENT 'الجرعة الافتراضية',
    default_frequency VARCHAR(100) DEFAULT NULL COMMENT 'التكرار الافتراضي',
    default_duration VARCHAR(50) DEFAULT NULL COMMENT 'المدة الافتراضية',
    notes TEXT DEFAULT NULL COMMENT 'ملاحظات',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='قائمة الأدوية';

-- =====================================================
-- جدول الوصفات
-- =====================================================
CREATE TABLE IF NOT EXISTS prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prescription_number VARCHAR(20) UNIQUE NOT NULL COMMENT 'رقم الوصفة',
    patient_id INT NOT NULL COMMENT 'معرف المريض',
    diagnosis TEXT DEFAULT NULL COMMENT 'التشخيص',
    notes TEXT DEFAULT NULL COMMENT 'ملاحظات للصيدلي',
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='الوصفات الطبية';

-- =====================================================
-- جدول بنود الوصفة
-- =====================================================
CREATE TABLE IF NOT EXISTS prescription_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prescription_id INT NOT NULL COMMENT 'معرف الوصفة',
    medication_id INT DEFAULT NULL COMMENT 'معرف الدواء',
    medication_name VARCHAR(150) NOT NULL COMMENT 'اسم الدواء',
    dosage VARCHAR(100) DEFAULT NULL COMMENT 'الجرعة',
    frequency VARCHAR(100) DEFAULT NULL COMMENT 'التكرار',
    duration VARCHAR(50) DEFAULT NULL COMMENT 'المدة',
    instructions TEXT DEFAULT NULL COMMENT 'تعليمات',
    FOREIGN KEY (prescription_id) REFERENCES prescriptions(id) ON DELETE CASCADE,
    FOREIGN KEY (medication_id) REFERENCES medications(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='بنود الوصفة';

-- =====================================================
-- جدول أنواع التحاليل
-- =====================================================
CREATE TABLE IF NOT EXISTS lab_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL COMMENT 'اسم التحليل',
    name_en VARCHAR(150) DEFAULT NULL COMMENT 'الاسم بالإنجليزية',
    category VARCHAR(50) DEFAULT NULL COMMENT 'التصنيف',
    normal_range VARCHAR(100) DEFAULT NULL COMMENT 'المعدل الطبيعي',
    unit VARCHAR(30) DEFAULT NULL COMMENT 'الوحدة',
    price DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'السعر',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='أنواع التحاليل';

-- =====================================================
-- جدول طلبات التحاليل
-- =====================================================
CREATE TABLE IF NOT EXISTS lab_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) UNIQUE NOT NULL COMMENT 'رقم الطلب',
    patient_id INT NOT NULL COMMENT 'معرف المريض',
    lab_test_id INT NOT NULL COMMENT 'معرف التحليل',
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending' COMMENT 'الحالة',
    result TEXT DEFAULT NULL COMMENT 'النتيجة',
    result_value VARCHAR(100) DEFAULT NULL COMMENT 'القيمة',
    result_status ENUM('normal', 'high', 'low', 'abnormal') DEFAULT NULL COMMENT 'حالة النتيجة',
    result_date DATETIME DEFAULT NULL COMMENT 'تاريخ النتيجة',
    notes TEXT DEFAULT NULL COMMENT 'ملاحظات',
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE RESTRICT,
    FOREIGN KEY (lab_test_id) REFERENCES lab_tests(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='طلبات التحاليل';

-- =====================================================
-- عرض الوصفات مع بيانات المرضى
-- =====================================================
CREATE OR REPLACE VIEW v_prescriptions AS
SELECT 
    p.*,
    pt.full_name AS patient_name,
    pt.electronic_number,
    (SELECT COUNT(*) FROM prescription_items WHERE prescription_id = p.id) AS items_count
FROM prescriptions p
JOIN patients pt ON p.patient_id = pt.id
ORDER BY p.created_at DESC;

-- =====================================================
-- عرض طلبات التحاليل
-- =====================================================
CREATE OR REPLACE VIEW v_lab_orders AS
SELECT 
    o.*,
    p.full_name AS patient_name,
    p.electronic_number,
    t.name AS test_name,
    t.normal_range,
    t.unit,
    CASE 
        WHEN o.status = 'pending' THEN 'معلق'
        WHEN o.status = 'completed' THEN 'مكتمل'
        WHEN o.status = 'cancelled' THEN 'ملغي'
    END AS status_text
FROM lab_orders o
JOIN patients p ON o.patient_id = p.id
JOIN lab_tests t ON o.lab_test_id = t.id
ORDER BY o.created_at DESC;

-- =====================================================
-- بيانات افتراضية للأدوية
-- =====================================================
INSERT INTO medications (name, name_en, category, default_dosage, default_frequency, default_duration) VALUES
('باراسيتامول', 'Paracetamol', 'مسكنات', '500 ملغ', '3 مرات يومياً', 'عند الحاجة'),
('أموكسيسيللين', 'Amoxicillin', 'مضاد حيوي', '500 ملغ', '3 مرات يومياً', '7 أيام'),
('إيبوبروفين', 'Ibuprofen', 'مضاد التهاب', '400 ملغ', 'مرتين يومياً', '5 أيام'),
('أوميبرازول', 'Omeprazole', 'معدة', '20 ملغ', 'مرة يومياً', '14 يوم'),
('سيتريزين', 'Cetirizine', 'حساسية', '10 ملغ', 'مرة يومياً', '7 أيام');

-- =====================================================
-- بيانات افتراضية للتحاليل
-- =====================================================
INSERT INTO lab_tests (name, name_en, category, normal_range, unit, price) VALUES
('صورة دم كاملة', 'CBC', 'دم', '-', '-', 100.00),
('سكر صائم', 'FBS', 'سكر', '70-100', 'mg/dl', 50.00),
('وظائف الكبد', 'Liver Function', 'كبد', '-', '-', 150.00),
('وظائف الكلى', 'Kidney Function', 'كلى', '-', '-', 150.00),
('بول كامل', 'Urine Analysis', 'بول', '-', '-', 40.00),
('هرمون الغدة الدرقية', 'TSH', 'هرمونات', '0.4-4.0', 'mIU/L', 120.00);

-- =====================================================
-- فهارس
-- =====================================================
CREATE INDEX IF NOT EXISTS idx_prescription_patient ON prescriptions(patient_id);
CREATE INDEX IF NOT EXISTS idx_prescription_date ON prescriptions(created_at);
CREATE INDEX IF NOT EXISTS idx_lab_order_patient ON lab_orders(patient_id);
CREATE INDEX IF NOT EXISTS idx_lab_order_status ON lab_orders(status);
