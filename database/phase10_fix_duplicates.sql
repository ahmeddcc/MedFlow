-- =====================================================
-- MedFlow - Fix Settings Duplicates
-- إصلاح تكرار الإعدادات وإضافة قيد التفرد
-- =====================================================

USE medflow_db;

-- 1. حذف الصفوف المكررة (الاحتفاظ بأحدث صف لكل مفتاح)
DELETE t1 FROM settings t1
INNER JOIN settings t2 
WHERE 
    t1.id < t2.id AND 
    t1.setting_key = t2.setting_key;

-- 2. التأكد من أن المفتاح فريد (Unique Key)
-- إذا كان الفهرس موجوداً، فإن هذا الأمر لن يضر (لأنه IF NOT EXISTS غير مدعومة مباشرة في ADD UNIQUE في بعض النسخ القديمة، لكننا سنحاول)
-- الطريقة الآمنة: إسقاط الفهرس إذا وُجد ثم إعادة إنشائه، أو استخدام ALTER IGNORE
-- سنستخدم محاولة إضافة القيد، وإذا فشل لوجود تكرار (مستبعد بعد الخطوة 1) سيتضح الخطأ.

ALTER TABLE settings ADD UNIQUE INDEX idx_unique_setting_key (setting_key);

-- 3. تحديث القيم الفارغة بقيم افتراضية (اختياري، لتجنب مشاكل العرض)
UPDATE settings SET setting_value = '' WHERE setting_value IS NULL;
