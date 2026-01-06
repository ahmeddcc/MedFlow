<?php
/**
 * Global Error Handler
 * يلتقط الأخطاء ويرسلها لتيليجرام (Sentinel)
 */
class ErrorHandler
{
    public static function register()
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleError($level, $message, $file, $line)
    {
        if (error_reporting() & $level) {
            self::logToTelegram("PHP Error ($level)", $message, $file, $line);
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    public static function handleException($exception)
    {
        $type = get_class($exception);
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();

        self::logToTelegram("Exception ($type)", $message, $file, $line);
        
        // عرض صفحة خطأ ودية للمستخدم (إلا إذا كنا في وضع التطوير)
        http_response_code(500);
        if (defined('DEBUG') && DEBUG) {
            echo "<h1>System Error</h1>";
            echo "<pre>$message</pre>";
        } else {
            require_once APP_PATH . 'views/errors/500.php';
        }
    }

    public static function handleShutdown()
    {
        $error = error_get_last();
        if ($error && ($error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR))) {
            self::logToTelegram("Fatal Error", $error['message'], $error['file'], $error['line']);
            
            // محاولة عرض رسالة نظيفة
            if (!headers_sent()) {
                http_response_code(500);
                require_once APP_PATH . 'views/errors/500.php';
            }
        }
    }

    private static function logToTelegram($type, $message, $file, $line)
    {
        // نستخدم الخدمة فقط إذا كانت متوفرة
        try {
            if (class_exists('TelegramService')) {
                $telegram = new TelegramService();
                $telegram->logSystemError($type, $message, $file, $line);
            }
        } catch (Throwable $t) {
            // فشل الصيد لا يجب أن يوقف النظام
            error_log("Failed to send error to Telegram: " . $t->getMessage());
        }
    }
}
