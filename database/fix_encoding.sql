-- =====================================================
-- MedFlow - إصلاح الترميز وتحديث البيانات
-- =====================================================

USE medflow_db;

-- تحويل الجداول للترميز الصحيح
ALTER DATABASE medflow_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE patients CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE patient_attachments CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE settings CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE audit_logs CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- حذف البيانات القديمة
DELETE FROM patients;
DELETE FROM users;
DELETE FROM settings;

-- إعادة إدراج المستخدمين
INSERT INTO users (id, username, password, full_name, role, language) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدير النظام', 'admin', 'ar'),
(2, 'doctor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'الطبيب', 'doctor', 'ar'),
(3, 'assistant', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'المساعد', 'assistant', 'ar');

-- إعادة إدراج الإعدادات
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('clinic_name', 'عيادة MedFlow', 'text', 'اسم العيادة'),
('clinic_phone', '', 'text', 'رقم هاتف العيادة'),
('clinic_address', '', 'text', 'عنوان العيادة'),
('electronic_number_prefix', 'MF', 'text', 'بادئة الرقم الإلكتروني'),
('electronic_number_start', '1000', 'number', 'بداية الترقيم الإلكتروني'),
('default_language', 'ar', 'text', 'اللغة الافتراضية');

-- إعادة إدراج المرضى التجريبيين
INSERT INTO patients (id, paper_file_number, electronic_number, barcode, full_name, phone, date_of_birth, gender, address, created_by) VALUES
(1, '001', 'MF1001', 'MF-A1001', 'أحمد محمد علي', '01001234567', '1985-03-15', 'male', 'القاهرة - مصر الجديدة', 1),
(2, '002', 'MF1002', 'MF-M1002', 'محمد أحمد حسن', '01112345678', '1990-07-22', 'male', 'الجيزة - الدقي', 1),
(3, '003', 'MF1003', 'MF-F1003', 'فاطمة علي محمد', '01223456789', '1988-11-10', 'female', 'الإسكندرية - سموحة', 1),
(4, '004', 'MF1004', 'MF-S1004', 'سارة أحمد إبراهيم', '01098765432', '1995-01-25', 'female', 'القاهرة - المعادي', 1),
(5, '005', 'MF1005', 'MF-K1005', 'خالد محمود عبد الله', '01234567890', '1978-09-05', 'male', 'الجيزة - 6 أكتوبر', 1);
