<?php

class LicenseService {
    private const SECRET_SALT = 'MedFlow_Secret_Salt_2024_@#$';

    /**
     * الحصول على معرف الجهاز الفريد
     * يعتمد على اسم السيرفر ومسار التنصيب لربط النسخة بالجهاز
     */
    public static function getMachineID(): string {
        $serverName = php_uname('n');
        $installPath = __DIR__; // مسار الملف الحالي
        return strtoupper(md5($serverName . $installPath . self::SECRET_SALT));
    }

    /**
     * التحقق من مفتاح الترخيص
     */
    public static function validateLicense(string $key): bool {
        $machineID = self::getMachineID();
        $validKey = self::generateKeyForMachine($machineID);
        return $key === $validKey;
    }

    /**
     * توليد مفتاح صحيح لمعرف جهاز معين (للاستخدام الداخلي أو التفعيل)
     */
    public static function generateKeyForMachine(string $machineID): string {
        return strtoupper(hash('sha256', $machineID . self::SECRET_SALT));
    }

    /**
     * الحصول على حالة الترخيص الحالية
     */
    public static function getStatus(): string {
        // إذا كان هناك مفتاح محفوظ
        $savedKey = getSetting('license_key', '');
        
        if (empty($savedKey)) {
            // التحقق من فترة التجربة
            $installDate = getSetting('install_date', '');
            if (empty($installDate)) {
                // أول تشغيل - حفظ تاريخ اليوم
                setSetting('install_date', date('Y-m-d'));
                return 'trial';
            }
            
            // حساب الأيام المنقضية (فترة تجريبية 14 يوم مثلاً)
            $start = new DateTime($installDate);
            $now = new DateTime();
            $diff = $now->diff($start)->days;
            
            if ($diff > 14) {
                return 'expired';
            }
            return 'trial';
        }

        if (self::validateLicense($savedKey)) {
            return 'active';
        }

        return 'invalid';
    }

    /**
     * التحقق من قفل النظام
     * يتم استدعاؤها في بداية كل طلب
     */
    public static function checkSystemLock(): void {
        // استثناء صفحات تسجيل الدخول، صفحة القفل، والأسيتس
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        // السماح بالوصول لصفحات القفل، تسجيل الدخول، والملفات الثابتة
        if (strpos($uri, 'license/lock') !== false || 
            strpos($uri, 'auth/login') !== false || 
            strpos($uri, 'assets/') !== false ||
            strpos($uri, 'logout') !== false) {
            return;
        }

        // المسؤولين فقط يمكنهم الوصول لصفحة القفل لتفعيل النظام
        // المستخدمين العاديين قد يواصلون العمل إذا كان "تجريبي منتهي" ولكننا سنفرض القفل الكامل هنا للأمان
        
        $status = self::getStatus();
        
        if ($status === 'invalid' || $status === 'expired') {
            // توجيه لصفحة القفل
            if (strpos($uri, 'license/lock') === false) {
                header('Location: ' . url('license/lock'));
                exit;
            }
        }
    }
}
