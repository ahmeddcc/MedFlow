<!DOCTYPE html>
<html lang="<?= currentLanguage() ?>" dir="<?= currentLanguage() === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MedFlow - نظام إدارة العيادات">
    <title><?= $pageTitle ?? __('dashboard') ?> - <?= __('app_name') ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/layout.css') ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= asset('images/favicon.svg') ?>">
</head>
<body>
    <div class="app-container">
        <!-- الشريط الجانبي -->
        <aside class="sidebar" id="sidebar">
            <button class="sidebar-toggle" onclick="toggleSidebar()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                    </svg>
                </div>
                <div class="sidebar-brand">
                    <span class="sidebar-brand-name">MedFlow</span>
                    <span class="sidebar-brand-subtitle">
                        <?php 
                        $subtitle = getSetting('clinic_name');
                        if (empty($subtitle)) {
                            $subtitle = getSetting('doctor_name');
                        }
                        if (empty($subtitle)) {
                            $subtitle = 'نظام إدارة العيادات المتطور';
                        }
                        echo htmlspecialchars($subtitle); 
                        ?>
                    </span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <span class="nav-section-title">الرئيسية</span>
                    <ul>
                        <li class="nav-item">
                            <a href="<?= url('dashboard') ?>" class="nav-link <?= ($controller ?? '') === 'dashboard' ? 'active' : '' ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="14" width="7" height="7"></rect>
                                    <rect x="3" y="14" width="7" height="7"></rect>
                                </svg>
                                <span class="nav-link-text"><?= __('dashboard') ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('doctor') ?>" class="nav-link <?= ($controller ?? '') === 'doctor' ? 'active' : '' ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7m0-18H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7m0-18v18"/>
                                </svg>
                                <span class="nav-link-text">مكتب الطبيب</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="nav-section">
                    <span class="nav-section-title">إدارة المرضى</span>
                    <ul>
                        <li class="nav-item">
                            <a href="<?= url('patients') ?>" class="nav-link <?= ($controller ?? '') === 'patients' ? 'active' : '' ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <span class="nav-link-text"><?= __('patients') ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('patients/create') ?>" class="nav-link">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="8.5" cy="7" r="4"></circle>
                                    <line x1="20" y1="8" x2="20" y2="14"></line>
                                    <line x1="23" y1="11" x2="17" y2="11"></line>
                                </svg>
                                <span class="nav-link-text"><?= __('new_patient') ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="nav-section">
                    <span class="nav-section-title">قائمة الانتظار</span>
                    <ul>
                        <li class="nav-item">
                            <a href="<?= url('waiting-list') ?>" class="nav-link">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                <span class="nav-link-text"><?= __('waiting_list') ?></span>
                                <?php
                                $waitingCount = Database::count('waiting_list', 
                                    "DATE(created_at) = CURDATE() AND status IN ('waiting', 'called')");
                                if ($waitingCount > 0):
                                ?>
                                <span class="badge badge-primary"><?= $waitingCount ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="nav-section">
                    <span class="nav-section-title">المالية</span>
                    <ul>
                        <li class="nav-item">
                            <a href="<?= url('invoices') ?>" class="nav-link">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="1" x2="12" y2="23"></line>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                                <span class="nav-link-text">الفواتير</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="nav-section">
                    <span class="nav-section-title">الطبي</span>
                    <ul>
                        <li class="nav-item">
                            <a href="<?= url('prescriptions') ?>" class="nav-link">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                </svg>
                                <span class="nav-link-text">الروشتة</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('prescriptions/lab') ?>" class="nav-link">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18l-2-3h-2l-2 3-2-3H8l-2 3z"></path>
                                </svg>
                                <span class="nav-link-text">التحاليل</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="nav-section">
                    <span class="nav-section-title">المناديب</span>
                    <ul>
                        <li class="nav-item">
                            <a href="<?= url('rep-waiting') ?>" class="nav-link">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                </svg>
                                <span class="nav-link-text">قائمة المناديب</span>
                                <?php
                                $repWaitingCount = Database::count('rep_waiting_list', 
                                    "DATE(created_at) = CURDATE() AND status IN ('waiting', 'called')");
                                if ($repWaitingCount > 0):
                                ?>
                                <span class="badge" style="background:#FF6B6B"><?= $repWaitingCount ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="nav-section">
                    <span class="nav-section-title">التقارير</span>
                    <ul>
                        <li class="nav-item">
                            <a href="<?= url('reports') ?>" class="nav-link">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="20" x2="18" y2="10"></line>
                                    <line x1="12" y1="20" x2="12" y2="4"></line>
                                    <line x1="6" y1="20" x2="6" y2="14"></line>
                                </svg>
                                <span class="nav-link-text">التقارير</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="nav-section">
                    <span class="nav-section-title">شاشات العرض</span>
                    <ul>
                        <li class="nav-item">
                            <a href="<?= url('tv') ?>" target="_blank" class="nav-link">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                    <line x1="8" y1="21" x2="16" y2="21"></line>
                                    <line x1="12" y1="17" x2="12" y2="21"></line>
                                </svg>
                                <span class="nav-link-text">شاشة الانتظار (TV)</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="nav-section">
                    <span class="nav-section-title">الإعدادات</span>
                    <ul>
                        <li class="nav-item">
                            <a href="<?= url('settings') ?>" class="nav-link">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                </svg>
                                <span class="nav-link-text"><?= __('settings') ?></span>
                            </a>
                        <?php if (currentUser()['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a href="<?= url('users') ?>" class="nav-link">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <span class="nav-link-text">المستخدمين</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('backup') ?>" class="nav-link">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                <span class="nav-link-text">النسخ الاحتياطي</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a href="<?= url('about') ?>" class="nav-link">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                </svg>
                                <span class="nav-link-text">حول النظام</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <div class="sidebar-footer">
                <div class="sidebar-user" onclick="location.href='<?= url('profile') ?>'">
                    <div class="sidebar-user-avatar">
                        <?= mb_substr(currentUser()['full_name'], 0, 1, 'UTF-8') ?>
                    </div>
                    <div class="sidebar-user-info">
                        <div class="sidebar-user-name"><?= currentUser()['full_name'] ?></div>
                        <div class="sidebar-user-role">
                            <?= currentUser()['role'] === 'doctor' ? 'طبيب' : (currentUser()['role'] === 'admin' ? 'مدير' : 'مساعد') ?>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- الغطاء للموبايل -->
        <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
        
        <!-- المحتوى الرئيسي -->
        <main class="main-content">
            <header class="main-header">
                <div class="header-right">
                    <button class="btn btn-ghost btn-icon mobile-menu-btn" onclick="toggleSidebar()" style="display: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                    <h1 class="page-title"><?= $pageTitle ?? __('dashboard') ?></h1>
                </div>
                
                <div class="header-left">
                    <div class="header-actions">
                        <button class="header-btn" title="<?= __('notifications') ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <span class="notification-dot"></span>
                        </button>
                        
                        <a href="<?= url('logout') ?>" class="header-btn" title="<?= __('logout') ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                        </a>
                    </div>
                </div>
            </header>
            
            <div class="content-area">
                <!-- رسائل الفلاش -->
                <?= showFlashMessages() ?>
                
                <!-- المحتوى -->
                <?= $content ?? '' ?>
            </div>
        </main>
    </div>
    
    <!-- Scripts -->
    <script src="<?= asset('js/app.js') ?>"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            localStorage.setItem('sidebar_collapsed', document.getElementById('sidebar').classList.contains('collapsed'));
        }
        
        // استعادة حالة الشريط الجانبي
        if (localStorage.getItem('sidebar_collapsed') === 'true') {
            document.getElementById('sidebar').classList.add('collapsed');
        }
        
        // للموبايل
        if (window.innerWidth <= 1024) {
            document.getElementById('sidebar').classList.remove('collapsed');
        }
    </script>
</body>
</html>
