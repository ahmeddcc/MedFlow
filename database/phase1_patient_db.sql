-- =====================================================
-- MedFlow - نظام إدارة العيادات
-- المرحلة الأولى: إدارة المرضى
-- تاريخ الإنشاء: 2026-01-04
-- =====================================================

-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS medflow_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE medflow_db;

-- =====================================================
-- جدول المستخدمين
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('doctor', 'assistant', 'admin') NOT NULL DEFAULT 'assistant',
    language ENUM('ar', 'en') NOT NULL DEFAULT 'ar',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- جدول المرضى
-- =====================================================
CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paper_file_number VARCHAR(20) NULL COMMENT 'رقم الملف الورقي',
    electronic_number VARCHAR(20) NOT NULL UNIQUE COMMENT 'الرقم الإلكتروني (تلقائي)',
    barcode VARCHAR(50) NOT NULL UNIQUE COMMENT 'الباركود (تلقائي)',
    full_name VARCHAR(100) NOT NULL COMMENT 'الاسم الكامل',
    phone VARCHAR(20) NULL COMMENT 'رقم الهاتف',
    secondary_phone VARCHAR(20) NULL COMMENT 'هاتف بديل',
    date_of_birth DATE NULL COMMENT 'تاريخ الميلاد',
    gender ENUM('male', 'female') NULL COMMENT 'الجنس',
    address TEXT NULL COMMENT 'العنوان',
    medical_history TEXT NULL COMMENT 'التاريخ الطبي',
    notes TEXT NULL COMMENT 'ملاحظات',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_electronic_number (electronic_number),
    INDEX idx_barcode (barcode),
    INDEX idx_full_name (full_name),
    INDEX idx_phone (phone),
    INDEX idx_paper_file_number (paper_file_number),
    INDEX idx_is_active (is_active),
    
    CONSTRAINT fk_patients_created_by 
        FOREIGN KEY (created_by) REFERENCES users(id) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- جدول مرفقات المرضى
-- =====================================================
CREATE TABLE IF NOT EXISTS patient_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL COMMENT 'اسم الملف الأصلي',
    file_path VARCHAR(500) NOT NULL COMMENT 'مسار الملف على السيرفر',
    file_type VARCHAR(50) NULL COMMENT 'نوع الملف',
    file_size INT NULL COMMENT 'حجم الملف بالبايت',
    description VARCHAR(255) NULL COMMENT 'وصف المرفق',
    uploaded_by INT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_patient_id (patient_id),
    INDEX idx_file_type (file_type),
    
    CONSTRAINT fk_attachments_patient 
        FOREIGN KEY (patient_id) REFERENCES patients(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_attachments_uploaded_by 
        FOREIGN KEY (uploaded_by) REFERENCES users(id) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- جدول إعدادات النظام
-- =====================================================
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    setting_type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- جدول سجل النظام (Audit Log)
-- =====================================================
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(50) NOT NULL COMMENT 'نوع الإجراء',
    table_name VARCHAR(50) NULL COMMENT 'الجدول المتأثر',
    record_id INT NULL COMMENT 'معرف السجل المتأثر',
    old_values JSON NULL COMMENT 'القيم القديمة',
    new_values JSON NULL COMMENT 'القيم الجديدة',
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_table_name (table_name),
    INDEX idx_created_at (created_at),
    
    CONSTRAINT fk_audit_user 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- إدراج البيانات الافتراضية
-- =====================================================

-- المستخدم الافتراضي (كلمة المرور: admin123)
INSERT INTO users (username, password, full_name, role, language) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدير النظام', 'admin', 'ar'),
('doctor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'الطبيب', 'doctor', 'ar'),
('assistant', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'المساعد', 'assistant', 'ar');

-- الإعدادات الافتراضية
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('clinic_name', 'عيادة MedFlow', 'text', 'اسم العيادة'),
('clinic_phone', '', 'text', 'رقم هاتف العيادة'),
('clinic_address', '', 'text', 'عنوان العيادة'),
('electronic_number_prefix', 'MF', 'text', 'بادئة الرقم الإلكتروني'),
('electronic_number_start', '1000', 'number', 'بداية الترقيم الإلكتروني'),
('default_language', 'ar', 'text', 'اللغة الافتراضية');

-- بيانات تجريبية للمرضى
INSERT INTO patients (paper_file_number, electronic_number, barcode, full_name, phone, date_of_birth, gender, address, created_by) VALUES
('001', 'MF1001', 'MF-A1001', 'أحمد محمد علي', '01001234567', '1985-03-15', 'male', 'القاهرة - مصر الجديدة', 1),
('002', 'MF1002', 'MF-M1002', 'محمد أحمد حسن', '01112345678', '1990-07-22', 'male', 'الجيزة - الدقي', 1),
('003', 'MF1003', 'MF-F1003', 'فاطمة علي محمد', '01223456789', '1988-11-10', 'female', 'الإسكندرية - سموحة', 1),
('004', 'MF1004', 'MF-S1004', 'سارة أحمد إبراهيم', '01098765432', '1995-01-25', 'female', 'القاهرة - المعادي', 1),
('005', 'MF1005', 'MF-K1005', 'خالد محمود عبد الله', '01234567890', '1978-09-05', 'male', 'الجيزة - 6 أكتوبر', 1);

-- =====================================================
-- نهاية ملف المرحلة الأولى
-- =====================================================
