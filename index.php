<?php
/**
 * =====================================================
 * MedFlow - نظام إدارة العيادات
 * نقطة الدخول الرئيسية
 * =====================================================
 */

// تعريف ثابت للتحقق من الوصول المباشر
define('MEDFLOW', true);

// تحميل الإعدادات
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/Database.php';
require_once __DIR__ . '/app/helpers/functions.php';

// Force Debug Mode (Override config.php)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// تفعيل نظام Sentinel لالتقاط الأخطاء
require_once __DIR__ . '/app/core/Controller.php';
require_once __DIR__ . '/app/core/ErrorHandler.php';
// ErrorHandler::register(); // Disabled to see raw errors

// تحميل المتحكمات
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/DashboardController.php';
require_once __DIR__ . '/app/controllers/PatientController.php';

// بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// التحقق من قفل النظام (الترخيص)
require_once __DIR__ . '/app/services/LicenseService.php';
LicenseService::checkSystemLock();

// الحصول على المسار
$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$segments = $url ? explode('/', $url) : [];

// التوجيه
$controller = $segments[0] ?? '';
$action = $segments[1] ?? '';
$param = $segments[2] ?? null;

// معالجة المسارات
switch ($controller) {
    case '':
    case 'login':
    case 'auth':  // للتوافق مع redirects القديمة
        $auth = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action !== 'logout') {
            $auth->login();
        } else {
            $auth->showLogin();
        }
        break;
        
    case 'logout':
        $auth = new AuthController();
        $auth->logout();
        break;
        
    case 'dashboard':
        $dashboard = new DashboardController();
        $dashboard->index();
        break;
        
    case 'patients':
        $patients = new PatientController();
        
        if ($action === 'search') {
            $patients->search();
        } elseif ($action === 'create') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $patients->store();
            } else {
                $patients->create();
            }
        } elseif (is_numeric($action)) {
            $id = (int) $action;
            
            if ($param === 'edit') {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $patients->update($id);
                } else {
                    $patients->edit($id);
                }
            } elseif ($param === 'delete') {
                $patients->delete($id);
            } else {
                $patients->show($id);
            }
        } else {
            $patients->index();
        }
        break;
        
    case 'attachments':
        if ($action === 'delete' && is_numeric($param)) {
            $patients = new PatientController();
            $patients->deleteAttachment((int) $param);
        }
        break;
    
    case 'waiting-list':
        require_once __DIR__ . '/app/controllers/WaitingListController.php';
        $waiting = new WaitingListController();
        
        switch ($action) {
            case '':
                $waiting->index();
                break;
            case 'display':
                $waiting->display();
                break;
            case 'add':
                $waiting->add();
                break;
            case 'call-next':
                $waiting->callNext();
                break;
            case 'recall':
                $waiting->recall();
                break;
            case 'enter':
                if (is_numeric($param)) {
                    $waiting->enter((int) $param);
                }
                break;
            case 'complete':
                if (is_numeric($param)) {
                    $waiting->complete((int) $param);
                }
                break;
            case 'cancel':
                if (is_numeric($param)) {
                    $waiting->cancel((int) $param);
                }
                break;
            case 'toggle-pause':
                $waiting->togglePause();
                break;
            case 'reset':
                $waiting->reset();
                break;
            case 'status':
                $waiting->status();
                break;
            case 'search-patient':
                $waiting->searchPatient();
                break;
            case 'reserved-numbers':
                $waiting->reservedNumbers();
                break;
            case 'reserve-number':
                $waiting->reserveNumber();
                break;
            case 'cancel-reservation':
                $waiting->cancelReservation();
                break;
            case 'check-reserved':
                $waiting->checkReserved();
                break;
            default:
                http_response_code(404);
                require VIEWS_PATH . 'errors/404.php';
        }
        break;
        
    case 'rep-waiting':
        require_once __DIR__ . '/app/controllers/RepWaitingController.php';
        $repWaiting = new RepWaitingController();
        
        switch ($action) {
            case '':
                $repWaiting->index();
                break;
            case 'display':
                $repWaiting->display();
                break;
            case 'add':
                $repWaiting->add();
                break;
            case 'call-next':
                $repWaiting->callNext();
                break;
            case 'recall':
                $repWaiting->recall();
                break;
            case 'enter':
                if (is_numeric($param)) {
                    $repWaiting->enter((int) $param);
                }
                break;
            case 'complete':
                if (is_numeric($param)) {
                    $repWaiting->complete((int) $param);
                }
                break;
            case 'cancel':
                if (is_numeric($param)) {
                    $repWaiting->cancel((int) $param);
                }
                break;
            case 'toggle-pause':
                $repWaiting->togglePause();
                break;
            case 'status':
                $repWaiting->status();
                break;
            case 'search-rep':
                $repWaiting->searchRep();
                break;
            default:
                http_response_code(404);
                require VIEWS_PATH . 'errors/404.php';
        }
        break;
        
    case 'companies':
        require_once __DIR__ . '/app/controllers/CompanyController.php';
        $companyCtrl = new CompanyController();
        
        switch ($action) {
            case '':
            case 'index':
                $companyCtrl->index();
                break;
            case 'store':
                $companyCtrl->store();
                break;
            case 'update':
                if (is_numeric($param)) $companyCtrl->update((int)$param);
                break;
            case 'delete':
                if (is_numeric($param)) $companyCtrl->delete((int)$param);
                break;
            case 'show':
                if (is_numeric($param)) $companyCtrl->show((int)$param);
                break;
            default:
                http_response_code(404);
                require VIEWS_PATH . 'errors/404.php';
        }
        break;
        
    case 'representatives':
        require_once __DIR__ . '/app/controllers/RepresentativeController.php';
        $repCtrl = new RepresentativeController();
        
        switch ($action) {
            case '':
            case 'index':
                $repCtrl->index();
                break;
            case 'store':
                $repCtrl->store();
                break;
            case 'update':
                if (is_numeric($param)) $repCtrl->update((int)$param);
                break;
            case 'delete':
                if (is_numeric($param)) $repCtrl->delete((int)$param);
                break;
            case 'show':
                if (is_numeric($param)) $repCtrl->show((int)$param);
                break;
            default:
                http_response_code(404);
                require VIEWS_PATH . 'errors/404.php';
        }
        break;
        
    case 'medications':
        require_once __DIR__ . '/app/controllers/MedicationController.php';
        $medCtrl = new MedicationController();
        
        switch ($action) {
            case '':
            case 'index':
                $medCtrl->index();
                break;
            case 'store':
                $medCtrl->store();
                break;
            case 'update':
                if (is_numeric($param)) $medCtrl->update((int)$param);
                break;
            case 'delete':
                if (is_numeric($param)) $medCtrl->delete((int)$param);
                break;
            case 'show':
                if (is_numeric($param)) $medCtrl->show((int)$param);
                break;
            case 'search':
                $medCtrl->search();
                break;
            default:
                http_response_code(404);
                require VIEWS_PATH . 'errors/404.php';
        }
        break;

    case 'lab-tests':
        require_once __DIR__ . '/app/controllers/LabTestController.php';
        $labCtrl = new LabTestController();
        
        switch ($action) {
            case '':
            case 'index':
                $labCtrl->index();
                break;
            case 'store':
                $labCtrl->store();
                break;
            case 'update':
                if (is_numeric($param)) $labCtrl->update((int)$param);
                break;
            case 'delete':
                if (is_numeric($param)) $labCtrl->delete((int)$param);
                break;
            case 'show':
                if (is_numeric($param)) $labCtrl->show((int)$param);
                break;
            default:
                http_response_code(404);
                require VIEWS_PATH . 'errors/404.php';
        }
        break;
        
    case 'invoices':
        require_once __DIR__ . '/app/controllers/InvoiceController.php';
        $invoice = new InvoiceController();
        
        switch ($action) {
            case '':
                $invoice->index();
                break;
            case 'create':
                $invoice->create();
                break;
            case 'show':
                $invoice->show();
                break;
            case 'add-payment':
                $invoice->addPayment();
                break;
            case 'cancel':
                $invoice->cancel();
                break;
            case 'search-patient':
                $invoice->searchPatient();
                break;
            case 'services':
                $invoice->services();
                break;
            default:
                http_response_code(404);
                require VIEWS_PATH . 'errors/404.php';
        }
        break;
        
    case 'prescriptions':
        require_once __DIR__ . '/app/controllers/PrescriptionController.php';
        $prescription = new PrescriptionController();
        
        switch ($action) {
            case '':
                $prescription->index();
                break;
            case 'create':
                $prescription->create();
                break;
            case 'show':
                $prescription->show();
                break;
            case 'search-patient':
                $prescription->searchPatient();
                break;
            case 'medications':
                $prescription->medications();
                break;
            case 'lab':
                $prescription->labOrders();
                break;
            case 'lab-order':
                $prescription->createLabOrder();
                break;
            case 'lab-result':
                $prescription->updateLabResult();
                break;
            case 'lab-tests':
                $prescription->labTests();
                break;
            default:
                http_response_code(404);
                require VIEWS_PATH . 'errors/404.php';
        }
        break;
        
    case 'print':
        require_once __DIR__ . '/app/controllers/PrintController.php';
        $print = new PrintController();
        
        switch ($action) {
            case 'receipt':
                $print->receipt();
                break;
            case 'invoice':
                $print->invoice();
                break;
            case 'prescription':
                $print->prescription();
                break;
            case 'lab-result':
                $print->labResult();
                break;
            default:
                http_response_code(404);
                require VIEWS_PATH . 'errors/404.php';
        }
        break;
        
    case 'reports':
        require_once __DIR__ . '/app/controllers/ReportController.php';
        $report = new ReportController();
        
        switch ($action) {
            case '':
            case 'index':
                $report->index();
                break;
            case 'daily':
                $report->dailyReport();
                break;
            case 'export-csv':
                $report->exportCsv();
                break;
            case 'print':
                $report->printView();
                break;
            case 'send-telegram':
                $report->sendDailyToTelegram();
                break;
            default:
                http_response_code(404);
                require VIEWS_PATH . 'errors/404.php';
        }
        break;
        
    case 'logs':
        require_once __DIR__ . '/app/controllers/ActivityLogController.php';
        $logCtrl = new ActivityLogController();
        $logCtrl->index();
        break;

    case 'print-queue':
        require_once __DIR__ . '/app/controllers/PrintQueueController.php';
        $queueCtrl = new PrintQueueController();
        if ($action === 'retry') $queueCtrl->retry();
        elseif ($action === 'delete') $queueCtrl->delete();
        else $queueCtrl->index();
        break;

    case 'backups':
        require_once __DIR__ . '/app/controllers/BackupController.php';
        $backupCtrl = new BackupController();
        if ($action === 'create') $backupCtrl->create();
        elseif ($action === 'download') $backupCtrl->download();
        else $backupCtrl->index();
        break;

    case 'users':
        require_once __DIR__ . '/app/controllers/UserController.php';
        $userCtrl = new UserController();
        
        if ($action === '' || $action === 'index') {
            $userCtrl->index();
        } elseif ($action === 'create') {
            $userCtrl->create();
        } elseif ($action === 'permissions') {
            AuthController::checkSession();
            AuthController::requireRole('admin');
            require VIEWS_PATH . 'users/permissions.php';
        } elseif (is_numeric($action)) {
            $id = (int) $action;
            if ($param === 'update') {
                $userCtrl->update($id);
            } elseif ($param === 'toggle') {
                $userCtrl->toggleStatus($id);
            } elseif ($param === 'delete') {
                $userCtrl->delete($id);
            }
        } else {
            http_response_code(404);
            require VIEWS_PATH . 'errors/404.php';
        }
        break;
        
    case 'settings':
        require_once __DIR__ . '/app/controllers/SettingsController.php';
        $settingsCtrl = new SettingsController();
        
        switch ($action) {
            case '':
            case 'index':
                $settingsCtrl->index();
                break;
            case 'save':
                $settingsCtrl->save();
                break;
            case 'testBot':
                $settingsCtrl->testBot();
                break;
            case 'deletePrinter':
                $settingsCtrl->deletePrinter($param);
                break;
            default:
                http_response_code(404);
                require VIEWS_PATH . 'errors/404.php';
        }
        break;
    
    case 'backup':
        require_once __DIR__ . '/app/controllers/BackupController.php';
        $backupCtrl = new BackupController();
        
        switch ($action) {
            case '':
            case 'index':
                $backupCtrl->index();
                break;
            case 'create':
                $backupCtrl->create();
                break;
            case 'download':
                $backupCtrl->download($param);
                break;
            case 'restore':
                $backupCtrl->restore($param);
                break;
            case 'delete':
                $backupCtrl->delete($param);
                break;
            case 'saveSettings':
                $backupCtrl->saveSettings();
                break;
            case 'saveCloudSettings':
                $backupCtrl->saveCloudSettings();
                break;
            case 'uploadCloud':
                $backupCtrl->uploadCloud($param);
                break;
            case 'testCloud':
                $backupCtrl->testCloud();
                break;
            default:
                $backupCtrl->index();
        }
        break;
        
    case 'about':
        AuthController::checkSession();
        require VIEWS_PATH . 'about/index.php';
        break;
        
    case 'profile':
        AuthController::checkSession();
        require VIEWS_PATH . 'profile/index.php';
        break;
        
    case 'doctor':
        require_once __DIR__ . '/app/controllers/DoctorController.php';
        $docCtrl = new DoctorController();
        
        if ($action === 'saveNotes') {
            $docCtrl->saveNotes();
        } elseif ($action === 'summonAssistant') {
            $docCtrl->summonAssistant();
        } elseif ($action === 'transferPatient') {
            $docCtrl->transferPatient();
        } else {
            $docCtrl->index(); // Default: Workbench
        }
        break;
        
    case 'api':
        // نقطة API للإصدارات القادمة
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok', 'version' => APP_VERSION]);
        break;
        
    case 'license':
        require_once __DIR__ . '/app/controllers/LicenseController.php';
        $licenseCtrl = new LicenseController();
        
        if ($action === 'lock') {
            $licenseCtrl->lock();
        } elseif ($action === 'activate') {
            $licenseCtrl->activate();
        } else {
            redirect('license/lock'); // توجيه افتراضي
        }
        break;
        
    case 'tv':
        require_once __DIR__ . '/app/controllers/TVController.php';
        $tvCtrl = new TVController();
        
        if ($action === 'status') {
            $tvCtrl->status();
        } else {
            $tvCtrl->index();
        }
        break;
        
    default:
        // صفحة 404
        http_response_code(404);
        require VIEWS_PATH . 'errors/404.php';
        break;
}
