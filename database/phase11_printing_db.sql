-- =====================================================
-- MedFlow - Phase 11: Smart Printing System
-- نظام الطباعة الذكي
-- =====================================================

USE medflow_db;

-- 1. جدول الطابعات (تعريف الطابعات المتاحة في العيادة)
CREATE TABLE IF NOT EXISTS printers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT 'اسم الطابعة التعريفي',
    type ENUM('thermal', 'a4', 'label') NOT NULL DEFAULT 'thermal' COMMENT 'نوع الطابعة',
    connection_type ENUM('network', 'usb', 'windows_share') DEFAULT 'windows_share',
    address VARCHAR(255) NULL COMMENT 'IP or Share Name',
    location ENUM('reception', 'doctor_room', 'lab', 'pharmacy') NOT NULL DEFAULT 'reception',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='تعريف الطابعات';

-- 2. إعدادات التوجيه (أي قسم يستخدم أي طابعة)
-- يمكننا استخدام جدول settings، لكن يفضل جدول منفصل للمرونة
CREATE TABLE IF NOT EXISTS print_routing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_type ENUM('invoice', 'prescription', 'referral', 'lab_result', 'receipt') NOT NULL UNIQUE,
    printer_id INT NOT NULL,
    template_format ENUM('thermal_80mm', 'a4', 'a5') DEFAULT 'a4',
    auto_print BOOLEAN DEFAULT FALSE COMMENT 'هل يطبع تلقائياً بدون معاينة؟',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_routing_printer FOREIGN KEY (printer_id) REFERENCES printers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='توجيه الطباعة';

-- 3. جدول مهام الطباعة (Queue) - للمستقبل (Cloud Print)
CREATE TABLE IF NOT EXISTS print_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    printer_id INT NOT NULL,
    document_type VARCHAR(50) NOT NULL,
    content LONGTEXT NOT NULL COMMENT 'HTML content or File Path',
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    attempts INT DEFAULT 0,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_print_status (status),
    CONSTRAINT fk_job_printer FOREIGN KEY (printer_id) REFERENCES printers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- بيانات أولية افتراضية
INSERT INTO printers (name, type, location) VALUES 
('Reception Thermal', 'thermal', 'reception'),
('Doctor Laser', 'a4', 'doctor_room');

INSERT INTO print_routing (document_type, printer_id, template_format) VALUES
('invoice', 1, 'thermal_80mm'), -- الفواتير للطابعة الحرارية
('prescription', 2, 'a5');     -- الروشتات لطابعة الليزر (A5)
