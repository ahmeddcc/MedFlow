-- =====================================================
-- MedFlow - Phase 9: System Settings & Feature Toggles
-- إعدادات النظام ومفاتيح التحكم والمميزات
-- =====================================================

USE medflow_db;

-- 1. جدول أنواع الأشعة (Radiology Types) - New Table
CREATE TABLE IF NOT EXISTS radiology_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL COMMENT 'اسم الفحص',
    name_en VARCHAR(150) DEFAULT NULL COMMENT 'الاسم بالإنجليزية',
    category VARCHAR(50) DEFAULT NULL COMMENT 'التصنيف (X-Ray, MRI, CT...)',
    price DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'السعر الافتراضي',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='أنواع الأشعة والفحوصات';

-- 2. إدخال بيانات افتراضية للأشعة
INSERT INTO radiology_types (name, name_en, category, price) VALUES
('أشعة سينية على الصدر', 'Chest X-Ray', 'X-Ray', 150.00),
('أشعة سينية على الأسنان', 'Dental X-Ray', 'X-Ray', 100.00),
('موجات صوتية على البطن', 'Abdominal Ultrasound', 'Ultrasound', 200.00),
('أشعة مقطعية على المخ', 'Brain CT Scan', 'CT', 800.00),
('رنين مغناطيسي على الركبة', 'Knee MRI', 'MRI', 1200.00)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- 3. مفاتيح التشغيل (Feature Toggles) في جدول الإعدادات
-- نستخدم INSERT IGNORE أو ON DUPLICATE KEY لتجنب التكرار
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
    ('enable_debts', '0', 'boolean', 'تفعيل نظام الديون والمدفوعات الجزئية'),
    ('enable_patient_printing', '0', 'boolean', 'تفعيل طباعة الفواتير للمرضى (للطبيب فقط)'),
    ('enable_lab_pricing', '0', 'boolean', 'تفعيل أسعار التحاليل في الفواتير'),
    ('enable_rad_pricing', '0', 'boolean', 'تفعيل أسعار الأشعة في الفواتير'),
    ('enable_smart_scheduling', '0', 'boolean', 'تفعيل الجدولة الذكية للمندوبين'),
    ('enable_idle_branding', '1', 'boolean', 'تفعيل شاشة الانتظار (الشعار) عند الخمول'),
    ('clinic_logo', 'assets/images/logo_default.png', 'image', 'شعار العيادة'),
    ('print_header_text', '', 'text', 'نص ترويسة الطباعة'),
    ('print_footer_text', '', 'text', 'نص تذييل الطباعة')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- 4. إضافة جداول القوائم الطبية (إذا لزم الأمر مستقبلاً)
-- (تم التأكد من وجود tables: medications, lab_tests في المرحلة 6)
