-- =====================================================
-- MedFlow - نظام الصلاحيات المفصل
-- Detailed Permissions System
-- =====================================================

-- جدول الصلاحيات المتاحة
CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    permission_key VARCHAR(100) NOT NULL UNIQUE COMMENT 'مفتاح الصلاحية',
    permission_name VARCHAR(100) NOT NULL COMMENT 'اسم الصلاحية',
    permission_group VARCHAR(50) NOT NULL COMMENT 'مجموعة الصلاحية',
    description VARCHAR(255) NULL COMMENT 'الوصف',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_permission_key (permission_key),
    INDEX idx_permission_group (permission_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='الصلاحيات المتاحة';

-- جدول صلاحيات المستخدمين
CREATE TABLE IF NOT EXISTS user_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_user_permission (user_id, permission_id),
    
    CONSTRAINT fk_user_permissions_user FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_permissions_permission FOREIGN KEY (permission_id) 
        REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='صلاحيات المستخدمين';

-- إدراج الصلاحيات الافتراضية (IGNORE لتجنب التكرار)
INSERT IGNORE INTO permissions (permission_key, permission_name, permission_group, description) VALUES
-- المرضى
('patients.view', 'عرض المرضى', 'patients', 'عرض قائمة المرضى وبياناتهم'),
('patients.create', 'إضافة مريض', 'patients', 'إضافة مريض جديد'),
('patients.edit', 'تعديل مريض', 'patients', 'تعديل بيانات المريض'),
('patients.delete', 'حذف مريض', 'patients', 'حذف مريض من النظام'),

-- قائمة الانتظار
('waiting.view', 'عرض الانتظار', 'waiting', 'عرض قائمة الانتظار'),
('waiting.add', 'إضافة للانتظار', 'waiting', 'إضافة مريض لقائمة الانتظار'),
('waiting.call', 'نداء المريض', 'waiting', 'نداء المريض التالي'),
('waiting.cancel', 'إلغاء الانتظار', 'waiting', 'إلغاء مريض من الانتظار'),

-- المناديب
('representatives.view', 'عرض المناديب', 'representatives', 'عرض قائمة انتظار المناديب'),
('representatives.add', 'إضافة مندوب', 'representatives', 'إضافة مندوب للانتظار'),
('representatives.manage', 'إدارة المناديب', 'representatives', 'إدارة قائمة المناديب'),

-- الفواتير
('invoices.view', 'عرض الفواتير', 'invoices', 'عرض الفواتير'),
('invoices.create', 'إنشاء فاتورة', 'invoices', 'إنشاء فاتورة جديدة'),
('invoices.edit', 'تعديل فاتورة', 'invoices', 'تعديل فاتورة'),
('invoices.delete', 'حذف فاتورة', 'invoices', 'حذف فاتورة'),
('invoices.payment', 'تسجيل دفعة', 'invoices', 'تسجيل دفعة على فاتورة'),

-- الروشتة والتحاليل
('prescriptions.view', 'عرض الروشتات', 'prescriptions', 'عرض الروشتات'),
('prescriptions.create', 'إنشاء روشتة', 'prescriptions', 'إنشاء روشتة جديدة'),
('prescriptions.edit', 'تعديل روشتة', 'prescriptions', 'تعديل روشتة'),
('lab.view', 'عرض التحاليل', 'lab', 'عرض طلبات التحاليل'),
('lab.create', 'طلب تحليل', 'lab', 'إنشاء طلب تحليل'),
('lab.result', 'إضافة نتيجة', 'lab', 'إضافة نتيجة تحليل'),

-- الطباعة
('print.receipt', 'طباعة إيصال', 'print', 'طباعة إيصال'),
('print.invoice', 'طباعة فاتورة', 'print', 'طباعة فاتورة'),
('print.prescription', 'طباعة روشتة', 'print', 'طباعة روشتة'),
('print.lab', 'طباعة تحليل', 'print', 'طباعة نتيجة تحليل'),

-- التقارير
('reports.view', 'عرض التقارير', 'reports', 'عرض التقارير'),
('reports.export', 'تصدير التقارير', 'reports', 'تصدير التقارير'),

-- الإعدادات
('settings.view', 'عرض الإعدادات', 'settings', 'عرض الإعدادات'),
('settings.edit', 'تعديل الإعدادات', 'settings', 'تعديل الإعدادات'),
('settings.telegram', 'إعدادات Telegram', 'settings', 'إدارة إعدادات Telegram'),

-- المستخدمين
('users.view', 'عرض المستخدمين', 'users', 'عرض قائمة المستخدمين'),
('users.create', 'إضافة مستخدم', 'users', 'إضافة مستخدم جديد'),
('users.edit', 'تعديل مستخدم', 'users', 'تعديل بيانات مستخدم'),
('users.delete', 'حذف مستخدم', 'users', 'حذف مستخدم'),
('users.permissions', 'إدارة الصلاحيات', 'users', 'إدارة صلاحيات المستخدمين'),

-- الأرقام المحجوزة
('reserved.view', 'عرض الأرقام المحجوزة', 'reserved', 'عرض قائمة الأرقام المحجوزة'),
('reserved.create', 'حجز رقم', 'reserved', 'حجز رقم جديد'),
('reserved.delete', 'إلغاء الحجز', 'reserved', 'إلغاء حجز رقم'),

-- لوحة التحكم
('dashboard.view', 'عرض لوحة التحكم', 'dashboard', 'الوصول للوحة التحكم'),
('dashboard.stats', 'عرض الإحصائيات', 'dashboard', 'عرض إحصائيات لوحة التحكم'),

-- المرفقات
('attachments.view', 'عرض المرفقات', 'attachments', 'عرض مرفقات المريض'),
('attachments.upload', 'رفع مرفقات', 'attachments', 'رفع مرفقات للمريض'),
('attachments.delete', 'حذف مرفقات', 'attachments', 'حذف مرفقات المريض'),

-- الخدمات
('services.view', 'عرض الخدمات', 'services', 'عرض قائمة الخدمات'),
('services.create', 'إضافة خدمة', 'services', 'إضافة خدمة جديدة'),
('services.edit', 'تعديل خدمة', 'services', 'تعديل خدمة'),
('services.delete', 'حذف خدمة', 'services', 'حذف خدمة');

-- منح جميع الصلاحيات للمدير (admin - user_id = 1)
INSERT IGNORE INTO user_permissions (user_id, permission_id)
SELECT 1, id FROM permissions;
