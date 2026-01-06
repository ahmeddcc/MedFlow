<?php
/**
 * خدمة الطباعة الذكية
 * Smart Printing Service
 * مسؤول عن تحديد الطابعة المناسبة وتنسيق القالب
 */
class PrintService
{
    /**
     * الحصول على إعدادات الطباعة لمستند معين
     * @param string $docType نوع المستند (invoice, prescription, etc)
     * @return array إعدادات الطابعة والقالب
     */
    public function getPrintConfig(string $docType): array
    {
        // القيم الافتراضية
        $defaultConfig = [
            'printer_name' => '',
            'template_format' => 'a4', // a4, a5, thermal_80mm
            'auto_print' => false,
            'copies' => 1
        ];

        try {
            // جلب قواعد التوجيه من قاعدة البيانات
            $sql = "SELECT r.*, p.name as printer_name, p.type as printer_type 
                    FROM print_routing r
                    JOIN printers p ON r.printer_id = p.id
                    WHERE r.document_type = ? AND p.is_active = 1";
            
            $config = Database::fetchOne($sql, [$docType]);
            
            if ($config) {
                return array_merge($defaultConfig, [
                    'printer_name' => $config['printer_name'],
                    'template_format' => $config['template_format'],
                    'auto_print' => (bool)$config['auto_print']
                ]);
            }
        } catch (Exception $e) {
            // في حالة وجود خطأ نعود للافتراضي
            error_log("Print Routing Error: " . $e->getMessage());
        }

        return $defaultConfig;
    }

    /**
     * الحصول على جميع الطابعات
     */
    public function getPrinters(): array
    {
        return Database::fetchAll("SELECT * FROM printers ORDER BY name ASC");
    }

    /**
     * الحصول على قواعد التوجيه
     */
    public function getRoutingRules(): array
    {
        return Database::fetchAll(
            "SELECT r.*, p.name as printer_name 
             FROM print_routing r 
             JOIN printers p ON r.printer_id = p.id"
        );
    }
}
