-- =====================================================
-- MedFlow - قاعدة بيانات المرحلة الخامسة
-- نظام المدفوعات والفواتير
-- =====================================================

USE medflow_db;

-- =====================================================
-- جدول الخدمات
-- =====================================================
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT 'اسم الخدمة',
    name_en VARCHAR(100) DEFAULT NULL COMMENT 'اسم الخدمة بالإنجليزية',
    category ENUM('consultation', 'procedure', 'lab', 'other') DEFAULT 'consultation' COMMENT 'التصنيف',
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT 'السعر',
    description TEXT DEFAULT NULL COMMENT 'الوصف',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'نشط',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='خدمات العيادة';

-- =====================================================
-- جدول الفواتير
-- =====================================================
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(20) UNIQUE NOT NULL COMMENT 'رقم الفاتورة',
    patient_id INT NOT NULL COMMENT 'معرف المريض',
    subtotal DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT 'الإجمالي الفرعي',
    discount DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'الخصم',
    discount_type ENUM('fixed', 'percent') DEFAULT 'fixed' COMMENT 'نوع الخصم',
    total DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT 'الإجمالي',
    paid DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'المدفوع',
    remaining DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'المتبقي',
    status ENUM('pending', 'partial', 'paid', 'cancelled') DEFAULT 'pending' COMMENT 'الحالة',
    notes TEXT DEFAULT NULL COMMENT 'ملاحظات',
    created_by INT DEFAULT NULL COMMENT 'أنشأها',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='الفواتير';

-- =====================================================
-- جدول بنود الفاتورة
-- =====================================================
CREATE TABLE IF NOT EXISTS invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL COMMENT 'معرف الفاتورة',
    service_id INT DEFAULT NULL COMMENT 'معرف الخدمة',
    description VARCHAR(255) NOT NULL COMMENT 'الوصف',
    quantity INT DEFAULT 1 COMMENT 'الكمية',
    unit_price DECIMAL(10, 2) NOT NULL COMMENT 'سعر الوحدة',
    total DECIMAL(10, 2) NOT NULL COMMENT 'الإجمالي',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='بنود الفاتورة';

-- =====================================================
-- جدول المدفوعات
-- =====================================================
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL COMMENT 'معرف الفاتورة',
    amount DECIMAL(10, 2) NOT NULL COMMENT 'المبلغ',
    payment_method ENUM('cash', 'card', 'transfer', 'other') DEFAULT 'cash' COMMENT 'طريقة الدفع',
    payment_date DATE NOT NULL COMMENT 'تاريخ الدفع',
    reference VARCHAR(100) DEFAULT NULL COMMENT 'رقم المرجع',
    notes TEXT DEFAULT NULL COMMENT 'ملاحظات',
    created_by INT DEFAULT NULL COMMENT 'أنشأها',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='المدفوعات';

-- =====================================================
-- عرض الفواتير مع بيانات المرضى
-- =====================================================
CREATE OR REPLACE VIEW v_invoices AS
SELECT 
    i.*,
    p.full_name AS patient_name,
    p.electronic_number,
    p.phone AS patient_phone,
    CASE 
        WHEN i.status = 'pending' THEN 'معلق'
        WHEN i.status = 'partial' THEN 'مسدد جزئياً'
        WHEN i.status = 'paid' THEN 'مسدد'
        WHEN i.status = 'cancelled' THEN 'ملغي'
    END AS status_text
FROM invoices i
JOIN patients p ON i.patient_id = p.id;

-- =====================================================
-- بيانات افتراضية
-- =====================================================
INSERT INTO services (name, name_en, category, price) VALUES
('كشف أول', 'First Visit', 'consultation', 100.00),
('كشف متابعة', 'Follow-up', 'consultation', 50.00),
('استشارة', 'Consultation', 'consultation', 150.00),
('تحليل', 'Lab Test', 'lab', 80.00);

-- =====================================================
-- فهارس
-- =====================================================
CREATE INDEX IF NOT EXISTS idx_invoice_patient ON invoices(patient_id);
CREATE INDEX IF NOT EXISTS idx_invoice_date ON invoices(created_at);
CREATE INDEX IF NOT EXISTS idx_invoice_status ON invoices(status);
CREATE INDEX IF NOT EXISTS idx_payment_invoice ON payments(invoice_id);
CREATE INDEX IF NOT EXISTS idx_payment_date ON payments(payment_date);
