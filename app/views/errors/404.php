<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - الصفحة غير موجودة</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4ECDC4;
            --text: #2C3E50;
            --text-muted: #7F8C8D;
            --bg: #FAFBFC;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .error-container {
            text-align: center;
            max-width: 500px;
        }
        
        .error-code {
            font-size: 8rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, #3DBDB5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 1rem;
        }
        
        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 0.5rem;
        }
        
        .error-message {
            color: var(--text-muted);
            margin-bottom: 2rem;
        }
        
        .error-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, #3DBDB5 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .error-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(78, 205, 196, 0.4);
        }
        
        .error-btn svg {
            width: 20px;
            height: 20px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">404</div>
        <h1 class="error-title">الصفحة غير موجودة</h1>
        <p class="error-message">عذراً، الصفحة التي تبحث عنها غير موجودة أو تم نقلها.</p>
        <a href="<?= defined('BASE_URL') ? BASE_URL : '/MedFlow/' ?>" class="error-btn">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            العودة للرئيسية
        </a>
    </div>
</body>
</html>
