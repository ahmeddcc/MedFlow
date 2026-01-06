<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شاشة الانتظار - MedFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4ECDC4;
            --primary-dark: #3DBDB5;
            --bg: #1a1a2e;
            --text: #ffffff;
            --text-muted: rgba(255,255,255,0.6);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .display-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem 3rem;
            background: rgba(255,255,255,0.05);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logo-icon svg {
            width: 28px;
            height: 28px;
            color: white;
        }
        
        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .clock {
            font-size: 2rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }
        
        .display-main {
            flex: 1;
            display: flex;
            padding: 3rem;
            gap: 3rem;
        }
        
        .current-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .current-label {
            font-size: 2rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }
        
        .current-number {
            font-size: 15rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, var(--primary) 0%, #7EDDD6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 100px rgba(78, 205, 196, 0.5);
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        
        .current-message {
            font-size: 1.5rem;
            color: var(--text-muted);
            margin-top: 2rem;
        }
        
        .waiting-section {
            width: 350px;
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 2rem;
            display: flex;
            flex-direction: column;
        }
        
        .waiting-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-align: center;
            color: var(--text-muted);
        }
        
        .waiting-numbers {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            overflow-y: auto;
        }
        
        .waiting-number-item {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            font-size: 1.5rem;
            font-weight: 700;
            transition: all 0.3s ease;
        }
        
        .waiting-number-item.called {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            animation: glow 1.5s ease-in-out infinite alternate;
        }
        
        @keyframes glow {
            from { box-shadow: 0 0 20px rgba(78, 205, 196, 0.4); }
            to { box-shadow: 0 0 40px rgba(78, 205, 196, 0.8); }
        }
        
        .pause-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
        }
        
        .pause-message {
            text-align: center;
        }
        
        .pause-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255, 165, 2, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
        }
        
        .pause-icon svg {
            width: 50px;
            height: 50px;
            color: #FFA502;
        }
        
        .pause-text {
            font-size: 2rem;
            font-weight: 700;
            color: #FFA502;
        }
        
        .no-patients {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
        }
        
        .no-patients svg {
            width: 80px;
            height: 80px;
            opacity: 0.3;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 1024px) {
            .display-main {
                flex-direction: column;
            }
            
            .waiting-section {
                width: 100%;
                max-height: 200px;
            }
            
            .current-number {
                font-size: 8rem;
            }
        }
    </style>
</head>
<body>
    <header class="display-header">
        <div class="logo">
            <div class="logo-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                </svg>
            </div>
            <span class="logo-text"><?= getSetting('clinic_name', 'MedFlow') ?></span>
        </div>
        <div class="clock" id="clock">00:00:00</div>
    </header>
    
    <main class="display-main">
        <?php if ($settings['is_paused'] === '1'): ?>
        <div class="pause-overlay">
            <div class="pause-message">
                <div class="pause-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="6" y="4" width="4" height="16"></rect>
                        <rect x="14" y="4" width="4" height="16"></rect>
                    </svg>
                </div>
                <div class="pause-text">تم إيقاف النداء مؤقتاً</div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="current-section">
            <?php if ($currentCall): ?>
            <div class="current-label">الدور الحالي</div>
            <div class="current-number" id="currentNumber"><?= $currentCall['turn_number'] ?></div>
            <div class="current-message">يرجى التوجه لغرفة الكشف</div>
            <?php else: ?>
            <div class="no-patients">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <p style="font-size: 1.5rem">في انتظار المرضى</p>
            </div>
            <?php endif; ?>
        </div>
        
        <aside class="waiting-section">
            <h2 class="waiting-title">قائمة الانتظار</h2>
            <div class="waiting-numbers">
                <?php foreach ($waitingList as $item): ?>
                <div class="waiting-number-item <?= $item['status'] === 'called' ? 'called' : '' ?>">
                    <?= $item['turn_number'] ?>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($waitingList)): ?>
                <div style="text-align:center; color: var(--text-muted); padding: 2rem;">
                    لا يوجد مرضى في الانتظار
                </div>
                <?php endif; ?>
            </div>
        </aside>
    </main>
    
    <audio id="callAudio" preload="auto"></audio>
    
    <script>
        // الساعة
        function updateClock() {
            const now = new Date();
            const time = now.toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            document.getElementById('clock').textContent = time;
        }
        updateClock();
        setInterval(updateClock, 1000);
        
        // التحديث التلقائي
        let lastTurn = <?= $currentCall['turn_number'] ?? 0 ?>;
        
        function checkForUpdates() {
            fetch('<?= url('waiting-list/status') ?>', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.current_turn !== lastTurn && data.current_turn > 0) {
                    lastTurn = data.current_turn;
                    // تشغيل النداء الصوتي أولاً
                    playCallAudio(data.current_turn);
                    // تأخير إعادة التحميل ليكتمل الصوت
                    setTimeout(() => location.reload(), 4000);
                }
            });
        }
        
        // التحقق كل 3 ثواني
        setInterval(checkForUpdates, 3000);
        
        // =====================================================
        // نظام النداء الصوتي بالملفات العربية
        // =====================================================
        const audioBasePath = '<?= asset('audio/numbers/') ?>';
        let audioQueue = [];
        let isPlaying = false;
        
        function playCallAudio(number) {
            audioQueue = [];
            
            // raqm.mp3 = "رقم"
            audioQueue.push(audioBasePath + 'raqm.mp3');
            
            // تحويل الرقم لملفات صوتية
            const numberFiles = getNumberAudioFiles(number);
            audioQueue = audioQueue.concat(numberFiles);
            
            // tafaddal.mp3 = "تفضل بالدخول"
            audioQueue.push(audioBasePath + 'tafaddal.mp3');
            
            // تشغيل قائمة الصوت
            playNextInQueue();
        }
        
        function getNumberAudioFiles(number) {
            const files = [];
            
            if (number <= 20) {
                files.push(audioBasePath + number + '.mp3');
            } else if (number < 100) {
                const tens = Math.floor(number / 10) * 10;
                const ones = number % 10;
                
                if (ones === 0) {
                    files.push(audioBasePath + tens + '.mp3');
                } else {
                    files.push(audioBasePath + ones + '.mp3');
                    files.push(audioBasePath + 'wa.mp3');
                    files.push(audioBasePath + tens + '.mp3');
                }
            } else if (number === 100) {
                files.push(audioBasePath + '100.mp3');
            } else {
                files.push(audioBasePath + (number % 100 || 100) + '.mp3');
            }
            
            return files;
        }
        
        function playNextInQueue() {
            if (audioQueue.length === 0) {
                isPlaying = false;
                return;
            }
            
            isPlaying = true;
            const audio = new Audio(audioQueue.shift());
            
            audio.onended = () => {
                setTimeout(playNextInQueue, 100);
            };
            
            audio.onerror = () => {
                console.warn('خطأ في تحميل الملف الصوتي');
                playNextInQueue();
            };
            
            audio.play().catch(err => {
                console.warn('خطأ في التشغيل:', err);
                playNextInQueue();
            });
        }
    </script>
</body>
</html>
