<?php

require_once __DIR__ . '/../core/Controller.php';

class TVController extends Controller {

    /**
     * الواجهة الرئيسية للشاشة
     */
    public function index() {
        // لا نحتاج للتحقق من الجلسة في بعض الحالات، ولكن يفضل
        // جعلها متاحة فقط للمصرح لهم أو الشبكة الداخلية
        // للتبسيط الآن سنسمح بالدخول
        
        // Scan for media files
        $mediaPath = __DIR__ . '/../../assets/tv_media';
        $mediaFiles = [];
        
        if (is_dir($mediaPath)) {
            $files = scandir($mediaPath);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                // Allow images and videos
                if (preg_match('/\.(jpg|jpeg|png|gif|mp4)$/i', $file)) {
                    $mediaFiles[] = $file;
                }
            }
        }
        
        // Pass media files to view
        require VIEWS_PATH . 'tv/index.php';
    }

    /**
     * API لجلب الحالة الحالية (AJAX Polling)
     */
    public function status() {
        // 1. الدور الحالي (الذي دخل العيادة أو تم استدعاؤه)
        $current = Database::fetchOne(
            "SELECT turn_number, status, called_at 
             FROM waiting_list 
             WHERE status IN ('called', 'entered') AND date(created_at) = CURDATE()
             ORDER BY called_at DESC LIMIT 1"
        );
        
        // 2. القادمين (في الانتظار)
        $next = Database::fetchAll(
            "SELECT turn_number 
             FROM waiting_list 
             WHERE status = 'waiting' AND date(created_at) = CURDATE()
             ORDER BY turn_number ASC LIMIT 3"
        );
        
        jsonResponse([
            'current_number' => $current['turn_number'] ?? '-',
            'current_status' => $current['status'] ?? 'waiting',
            'next_numbers' => array_column($next, 'turn_number'),
            'last_update' => $current['called_at'] ?? null,
            'timestamp' => time()
        ]);
    }
}
