<?php
/**
 * متحكم Webhook لتيليجرام
 * لاستقبال الأوامر التفاعلية
 */
class TelegramWebhookController
{
    private $telegram;
    
    public function __construct()
    {
        require_once APP_PATH . 'services/TelegramService.php';
        $this->telegram = new TelegramService();
    }
    
    public function handle(): void
    {
        $input = file_get_contents('php://input');
        $update = json_decode($input, true);
        
        if (!$update) {
            return;
        }
        
        // تسجيل للتبع
        // logAction('telegram_webhook', null, null, null, ['input' => $input]);
        
        if (isset($update['callback_query'])) {
            $this->handleCallback($update['callback_query']);
        }
    }
    
    private function handleCallback(array $callback): void
    {
        $data = $callback['data'];
        $chatId = $callback['message']['chat']['id'];
        
        // يمكن تنفيذ أوامر بناءً على البيانات
        // مثال: next_turn
        if ($data === 'next_turn') {
            // يمكن استدعاء WaitingListController هنا
            // لكن بما أننا في طلب منفصل، يجب الحذر من الصلاح السيشن
            // حالياً مجرد مثال
        }
    }
}
