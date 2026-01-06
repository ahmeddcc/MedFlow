<?php
/**
 * خدمة Telegram المتقدمة (Dual Bot Architecture)
 * - Bot 1: Operations (للإشعارات والتقارير اليومية)
 * - Bot 2: Support (لسجلات الأخطاء والدعم الفني)
 */
class TelegramService
{
    private string $opsToken;
    private string $opsChatId;
    
    private string $supportToken;
    private string $supportChatId;
    
    private string $apiUrl = 'https://api.telegram.org/bot';
    
    public function __construct()
    {
        // إعدادات بوت العمليات
        $this->opsToken = getSetting('telegram_bot_token', '');
        $this->opsChatId = getSetting('telegram_chat_id', '');
        
        // إعدادات بوت الدعم الفني
        $this->supportToken = getSetting('telegram_support_bot_token', '');
        $this->supportChatId = getSetting('telegram_support_chat_id', '');
    }
    
    // =================================================================
    // 1. وظائف بوت العمليات (Operations Bot)
    // =================================================================
    
    public function sendOperationMessage(string $message, bool $parseHtml = false): array
    {
        if (getSetting('telegram_enabled', '0') !== '1') return ['ok' => false];
        return $this->sendRequest($this->opsToken, $this->opsChatId, $message, $parseHtml);
    }

    public function notifyNewTurn(string $patientName, int $turnNumber): void
    {
        $msg = "🔔 *دور جديد*\n\n" .
               "👤 المريض: {$patientName}\n" .
               "🔢 الرقم: *{$turnNumber}*\n" .
               "🕒 الوقت: " . date('H:i');
        $this->sendOperationMessage($msg);
    }

    public function notifyAssistant(string $text): void
    {
        // يمكن تخصيص ChatID مختلف للمساعد مستقبلاً
        $msg = "⚠️ *نداء للمساعد*\n\n{$text}";
        $this->sendOperationMessage($msg);
    }

    public function sendDailySummary(array $stats): void
    {
        $date = date('Y-m-d');
        $msg = "📊 *التقرير اليومي - {$date}*\n\n" .
               "👥 *الزيارات:* {$stats['visits']}\n" .
               "💰 *الإيراد:* " . formatMoney($stats['revenue']) . "\n" .
               "📉 *المصروفات:* " . formatMoney($stats['expenses']) . "\n" .
               "💵 *الصافي:* " . formatMoney($stats['net_income']) . "\n\n" .
               "🕒 *آخر تحديث:* " . date('H:i');
               
        $this->sendOperationMessage($msg);
    }
    
    // =================================================================
    // 2. وظائف بوت الدعم الفني (Support Bot)
    // =================================================================
    
    public function sendSupportMessage(string $message, bool $parseHtml = false): array
    {
        if (getSetting('telegram_support_enabled', '0') !== '1') return ['ok' => false];
        return $this->sendRequest($this->supportToken, $this->supportChatId, $message, $parseHtml);
    }

    public function logSystemError(string $errorType, string $errorMessage, string $file, int $line): void
    {
        // 1. تسجيل الخطأ في قاعدة البيانات
        try {
            Database::query(
                "INSERT INTO telegram_error_logs (error_type, error_message, file_path, line_number) VALUES (?, ?, ?, ?)",
                [$errorType, $errorMessage, $file, $line]
            );
        } catch (Exception $e) { /* تجاهل أخطاء قاعدة البيانات لتجنب الحلقة المفرغة */ }

        // 2. إرسال إشعار للمطور
        $msg = "🚨 *خطأ في النظام (System Error)*\n\n" .
               "🛑 *النوع:* {$errorType}\n" .
               "📂 *الملف:* `" . basename($file) . "`\n" .
               "🔢 *السطر:* {$line}\n\n" .
               "📝 *الرسالة:*\n`{$errorMessage}`";
               
        $this->sendSupportMessage($msg);
    }

    // =================================================================
    // 3. المحرك الأساسي (Core Engine)
    // =================================================================
    
    private function sendRequest(string $token, string $chatId, string $message, bool $parseHtml): array
    {
        if (empty($token) || empty($chatId)) return ['ok' => false, 'error' => 'Missing Config'];
        
        $url = $this->apiUrl . $token . '/sendMessage';
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => $parseHtml ? 'HTML' : 'Markdown',
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false // للتطوير المحلي فقط
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true) ?? ['ok' => false];
    }
}
