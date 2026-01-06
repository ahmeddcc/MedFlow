<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شاشة الانتظار - MedFlow</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root {
            --bg-color: #0f172a;
            --sidebar-width: 350px;
            --accent-color: #06b6d4;
            --text-color: #f8fafc;
            --glass-bg: rgba(30, 41, 59, 0.7);
            --glass-border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        * { box-sizing: border-box; }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Cairo', sans-serif;
            margin: 0;
            height: 100vh;
            overflow: hidden;
            display: flex;
        }

        /* --- Left Side: Media Canvas (70-75%) --- */
        .media-section {
            flex: 1;
            position: relative;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .media-content {
            width: 100%;
            height: 100%;
            object-fit: contain; /* or cover based on preference */
            display: none;
        }
        
        .media-content.active { display: block; }
        
        .clinic-logo-overlay {
            position: absolute;
            top: 30px;
            right: 30px;
            background: rgba(0,0,0,0.5);
            padding: 10px 20px;
            border-radius: 50px;
            backdrop-filter: blur(5px);
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .clinic-logo-overlay img { height: 40px; }
        .clinic-logo-overlay span { font-weight: bold; font-size: 1.2rem; }

        .news-ticker {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background: var(--accent-color);
            color: #000;
            padding: 10px 0;
            font-weight: bold;
            font-size: 1.2rem;
            white-space: nowrap;
            overflow: hidden;
            z-index: 20;
        }
        .ticker-content {
            display: inline-block;
            padding-left: 100%;
            animation: ticker 30s linear infinite;
        }
        @keyframes ticker {
            0% { transform: translate3d(0, 0, 0); }
            100% { transform: translate3d(100%, 0, 0); } /* RTL ticker moves right */
        }

        /* --- Right Side: Queue Sidebar (25-30%) --- */
        .queue-sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            border-left: 1px solid #334155;
            display: flex;
            flex-direction: column;
            padding: 20px;
            z-index: 50;
            box-shadow: -10px 0 30px rgba(0,0,0,0.5);
        }

        .clock-widget {
            text-align: center;
            margin-bottom: 30px;
            background: rgba(255,255,255,0.05);
            padding: 15px;
            border-radius: 15px;
            border: var(--glass-border);
        }
        .time-display { font-size: 2.5rem; font-weight: 900; line-height: 1; letter-spacing: 2px; }
        .date-display { color: #94a3b8; font-size: 0.9rem; margin-top: 5px; }

        .current-turn-card {
            background: linear-gradient(135deg, var(--accent-color), #0891b2);
            border-radius: 20px;
            padding: 30px 10px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(6, 182, 212, 0.3);
            margin-bottom: 40px;
            transform-origin: center;
            transition: transform 0.3s;
        }
        .current-label { font-size: 1.2rem; color: rgba(255,255,255,0.8); margin-bottom: 10px; }
        .current-number { font-size: 8rem; font-weight: 900; line-height: 1; text-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        .current-status { 
            display: inline-block; 
            background: rgba(0,0,0,0.2); 
            padding: 5px 20px; 
            border-radius: 50px; 
            margin-top: 15px;
            font-weight: bold;
        }

        .next-list-container h3 {
            color: #94a3b8;
            font-size: 1.1rem;
            border-bottom: 1px solid #334155;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .next-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255,255,255,0.03);
            margin-bottom: 15px;
            padding: 15px 20px;
            border-radius: 12px;
            border: 1px solid transparent;
        }
        .next-item.active { border-color: rgba(255,255,255,0.1); }
        .next-label { color: #64748b; font-size: 0.9rem; }
        .next-val { font-size: 2rem; font-weight: bold; color: #e2e8f0; }

        /* --- Fullscreen Popup Overlay (For New Number) --- */
        #popupOverlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.85); /* Darken bg heavily */
            backdrop-filter: blur(10px);
            z-index: 999;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .popup-content {
            text-align: center;
            animation: zoomIn 0.5s;
        }
        .popup-number {
            font-size: 20rem;
            font-weight: 900;
            color: var(--accent-color);
            text-shadow: 0 0 50px var(--accent-color);
            line-height: 1;
        }
        .popup-text {
            font-size: 4rem;
            color: white;
            margin-top: 20px;
        }

        /* --- Utility --- */
        .hidden { display: none !important; }
        .fs-btn { position: fixed; bottom: 20px; left: 20px; opacity: 0; transition: opacity 0.3s; z-index: 1000; }
        body:hover .fs-btn { opacity: 1; }

    </style>
</head>
<body>

    <!-- Left: Media -->
    <div class="media-section">
        <div class="clinic-logo-overlay">
            <span><?= getSetting('clinic_name', 'MedFlow Clinic') ?></span>
        </div>

        <div id="mediaContainer" style="width:100%; height:100%; position:relative;">
            <!-- Media items injected via JS -->
            <div class="media-content active" style="display:flex; justify-content:center; align-items:center; height:100%; background:#111;">
                <h1 style="color:#333;">MedFlow Intelligent TV</h1>
            </div>
        </div>

        <div class="news-ticker">
            <div class="ticker-content">
                نتمنى لكم دوام الصحة والعافية • يرجى الالتزام بالهدوء • مواعيد العمل من 9 صباحاً حتى 9 مساءً
            </div>
        </div>
    </div>

    <!-- Right: Queue -->
    <div class="queue-sidebar">
        <div class="clock-widget">
            <div class="time-display" id="clock">00:00</div>
            <div class="date-display" id="date">--/--/----</div>
        </div>

        <div class="current-turn-card" id="currentCard">
            <div class="current-label">الدور الحالي</div>
            <div class="current-number" id="currentNumber">-</div>
            <div class="current-status" id="currentStatus">انتظار</div>
        </div>

        <div class="next-list-container">
            <h3>القادمون</h3>
            <div id="nextList">
                <!-- Items -->
            </div>
        </div>
    </div>

    <!-- Popup Overlay -->
    <div id="popupOverlay">
        <div class="popup-content">
            <div class="popup-number" id="popupNum">000</div>
            <div class="popup-text">تفضل بالدخول</div>
        </div>
    </div>
    
    <button class="fs-btn btn btn-light" onclick="toggleFullScreen()">🖥️ ملئ الشاشة</button>

    <script>
        // --- Configuration ---
        const mediaFiles = <?= json_encode($mediaFiles ?? []) ?>;
        const mediaPath = '<?= asset('assets/tv_media/') ?>';
        
        // --- Logic ---
        function updateTime() {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit', hour12: true}).replace('AM','').replace('PM','');
            document.getElementById('date').innerText = now.toLocaleDateString('ar-EG');
        }
        setInterval(updateTime, 1000);
        updateTime();

        // --- Media Player ---
        let currentMediaIndex = -1;
        const container = document.getElementById('mediaContainer');

        function playNextMedia() {
            if (mediaFiles.length === 0) return;
            
            currentMediaIndex = (currentMediaIndex + 1) % mediaFiles.length;
            const file = mediaFiles[currentMediaIndex];
            const ext = file.split('.').pop().toLowerCase();
            const isVideo = ['mp4', 'webm'].includes(ext);

            // Clear previous
            container.innerHTML = '';

            let el;
            if (isVideo) {
                el = document.createElement('video');
                el.src = mediaPath + file;
                el.className = 'media-content active';
                el.muted = true; // Auto-play usually requires mute
                el.autoplay = true;
                el.style.width = '100%';
                el.style.height = '100%';
                
                el.onended = () => { playNextMedia(); };
                el.onerror = () => { setTimeout(playNextMedia, 1000); };
            } else {
                el = document.createElement('img');
                el.src = mediaPath + file;
                el.className = 'media-content active animate__animated animate__fadeIn';
                
                // Show image for 10 seconds
                setTimeout(playNextMedia, 10000);
            }
            container.appendChild(el);
        }

        if(mediaFiles.length > 0) playNextMedia();


        // --- Queue System ---
        let lastNumber = null;
        const chime = new Audio('<?= url('assets/audio/numbers/tafaddal.mp3') ?>');

        function fetchStatus() {
            fetch('<?= url('tv/status') ?>')
                .then(r => r.json())
                .then(data => {
                    updateQueueUI(data);
                })
                .catch(e => console.error(e));
        }

        function updateQueueUI(data) {
            const numEl = document.getElementById('currentNumber');
            const statusEl = document.getElementById('currentStatus');
            const listEl = document.getElementById('nextList');
            
            // Detect Change
            if (data.current_number !== lastNumber && data.current_number !== '-') {
                // Show Popup
                showPopup(data.current_number);
                
                // Play Sound
                if (lastNumber !== null) playSound();
            }
            lastNumber = data.current_number;

            // Update Sidebar
            numEl.innerText = data.current_number;
            const statusMap = { 'called': 'تفضل بالدخول', 'entered': 'بالداخل', 'waiting': 'انتظار' };
            statusEl.innerText = statusMap[data.current_status] || data.current_status;
            
            // Color logic
            const card = document.getElementById('currentCard');
            if (data.current_status === 'entered') {
                card.style.background = 'linear-gradient(135deg, #ef4444, #b91c1c)'; // Red
            } else {
                card.style.background = 'linear-gradient(135deg, var(--accent-color), #0891b2)'; // Cyan
            }

            // Next List
            listEl.innerHTML = '';
            if (data.next_numbers && data.next_numbers.length > 0) {
                data.next_numbers.forEach((n, index) => {
                    const div = document.createElement('div');
                    div.className = 'next-item';
                    div.innerHTML = `
                        <span class="next-label">التالي ${index + 1}</span>
                        <span class="next-val">${n}</span>
                    `;
                    listEl.appendChild(div);
                });
            } else {
                listEl.innerHTML = '<div style="text-align:center; color:#666; padding:20px;">لا يوجد انتظار</div>';
            }
        }

        function showPopup(number) {
            const overlay = document.getElementById('popupOverlay');
            const popupNum = document.getElementById('popupNum');
            
            popupNum.innerText = number;
            overlay.style.display = 'flex';
            
            // Hide after 5 seconds
            setTimeout(() => {
                overlay.style.display = 'none';
            }, 6000);
        }

        function playSound() {
            chime.currentTime = 0;
            chime.play().catch(e => console.log('Interact to enable audio'));
        }

        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }

        setInterval(fetchStatus, 3000);
        fetchStatus();
        
        // Initial interaction
        document.body.addEventListener('click', () => {
            playSound();
            chime.pause();
        }, {once:true});

    </script>
</body>
</html>
