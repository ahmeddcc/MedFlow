<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MedFlow - نظام إدارة العيادات">
    <title><?= __('login_title') ?> - <?= __('app_name') ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/auth.css') ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= asset('images/favicon.svg') ?>">
</head>
<body>
    <div class="auth-container">
        <!-- الزخارف -->
        <div class="auth-decoration auth-decoration-1"></div>
        <div class="auth-decoration auth-decoration-2"></div>
        <div class="auth-decoration auth-decoration-3"></div>
        
        <!-- بطاقة تسجيل الدخول -->
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                    </svg>
                </div>
                <h1 class="auth-title"><?= getSetting('clinic_name', __('app_name')) ?></h1>
                <p class="auth-subtitle"><?= __('login_subtitle') ?></p>
            </div>
            
            <div class="auth-body">
                <?php $error = flash('error'); ?>
                <?php if ($error): ?>
                <div class="auth-error">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span><?= $error ?></span>
                </div>
                <?php endif; ?>
                
                <form class="auth-form" method="POST" action="<?= url('login') ?>">
                    <?= csrfField() ?>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <?= __('username') ?>
                        </label>
                        <div class="input-wrapper">
                            <input type="text" 
                                   name="username" 
                                   class="form-control" 
                                   placeholder="<?= __('username') ?>"
                                   autocomplete="username"
                                   required
                                   autofocus>
                            <span class="input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <?= __('password') ?>
                        </label>
                        <div class="input-wrapper">
                            <input type="password" 
                                   name="password" 
                                   id="password"
                                   class="form-control" 
                                   placeholder="<?= __('password') ?>"
                                   autocomplete="current-password"
                                   required>
                            <span class="input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </span>
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="auth-remember">
                        <label class="checkbox-wrapper">
                            <input type="checkbox" name="remember" value="1">
                            <span><?= __('remember_me') ?></span>
                        </label>
                    </div>
                    
                    <button type="submit" class="auth-submit">
                        <span><?= __('login') ?></span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                            <polyline points="10 17 15 12 10 7"></polyline>
                            <line x1="15" y1="12" x2="3" y2="12"></line>
                        </svg>
                    </button>
                </form>
            </div>
            
            <div class="auth-footer">
                <p>&copy; <?= date('Y') ?> <?= __('app_name') ?>. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                `;
            }
        }
    </script>
</body>
</html>
