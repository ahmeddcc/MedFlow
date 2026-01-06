<?php
/**
 * =====================================================
 * MedFlow - متحكم المرضى
 * =====================================================
 */

class PatientController
{
    /**
     * عرض قائمة المرضى
     */
    public function index(): void
    {
        AuthController::checkSession();
        
        $search = clean($_GET['search'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;
        
        $where = "is_active = 1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (full_name LIKE ? OR electronic_number LIKE ? OR phone LIKE ? OR paper_file_number LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam, $searchParam];
        }
        
        // إجمالي السجلات
        $total = Database::count('patients', $where, $params);
        $totalPages = ceil($total / $perPage);
        
        // جلب المرضى
        $patients = Database::fetchAll(
            "SELECT * FROM patients WHERE {$where} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        
        $pageTitle = __('patients');
        require VIEWS_PATH . 'patients/index.php';
    }
    
    /**
     * البحث السريع (AJAX)
     */
    public function search(): void
    {
        AuthController::checkSession();
        
        if (!isAjax()) {
            redirect('patients');
        }
        
        $query = clean($_GET['q'] ?? '');
        
        if (strlen($query) < 2) {
            jsonResponse(['patients' => []]);
        }
        
        $searchParam = "%{$query}%";
        $patients = Database::fetchAll(
            "SELECT id, full_name, electronic_number, phone, paper_file_number, barcode, gender, date_of_birth 
             FROM patients 
             WHERE is_active = 1 AND (
                 full_name LIKE ? OR 
                 electronic_number LIKE ? OR 
                 phone LIKE ? OR 
                 paper_file_number LIKE ? OR
                 barcode LIKE ?
             )
             ORDER BY full_name 
             LIMIT 10",
            [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]
        );
        
        // إضافة العمر
        foreach ($patients as &$patient) {
            $patient['age'] = calculateAge($patient['date_of_birth']);
            $patient['gender_text'] = $patient['gender'] === 'male' ? 'ذكر' : 'أنثى';
        }
        
        jsonResponse(['patients' => $patients, 'query' => $query]);
    }
    
    /**
     * عرض نموذج إضافة مريض
     */
    public function create(): void
    {
        AuthController::checkSession();
        
        $pageTitle = __('new_patient');
        require VIEWS_PATH . 'patients/create.php';
    }
    
    /**
     * حفظ مريض جديد
     */
    public function store(): void
    {
        AuthController::checkSession();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('patients/create');
        }
        
        if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
            flash('error', 'طلب غير صالح');
            redirect('patients/create');
        }
        
        // جمع البيانات
        $data = [
            'paper_file_number' => clean($_POST['paper_file_number'] ?? ''),
            'full_name' => clean($_POST['full_name'] ?? ''),
            'phone' => clean($_POST['phone'] ?? ''),
            'secondary_phone' => clean($_POST['secondary_phone'] ?? ''),
            'date_of_birth' => clean($_POST['date_of_birth'] ?? '') ?: null,
            'gender' => clean($_POST['gender'] ?? '') ?: null,
            'address' => clean($_POST['address'] ?? ''),
            'medical_history' => clean($_POST['medical_history'] ?? ''),
            'notes' => clean($_POST['notes'] ?? ''),
            'created_by' => $_SESSION['user_id']
        ];
        
        // التحقق
        if (empty($data['full_name'])) {
            flash('error', 'يرجى إدخال اسم المريض');
            redirect('patients/create');
        }
        
        try {
            // الرقم الإلكتروني (من النموذج أو توليد تلقائي)
            $electronicNumber = clean($_POST['electronic_number'] ?? '');
            if (!empty($electronicNumber)) {
                $data['electronic_number'] = $electronicNumber;
            } else {
                $data['electronic_number'] = $this->generateElectronicNumber();
            }
            
            // توليد الباركود
            $data['barcode'] = $this->generateBarcode($data['full_name'], $data['electronic_number']);
            
            // الإدراج
            $patientId = Database::insert('patients', $data);
            
            // رفع المرفقات إن وجدت
            if (!empty($_FILES['attachments']['name'][0])) {
                $this->uploadAttachments($patientId, $_FILES['attachments']);
            }
            
            logAction('create', 'patients', $patientId, null, $data);
            
            flash('success', 'تم إضافة المريض بنجاح - الرقم الإلكتروني: ' . $data['electronic_number']);
            redirect('patients/' . $patientId);
            
        } catch (Exception $e) {
            flash('error', 'حدث خطأ أثناء إضافة المريض');
            redirect('patients/create');
        }
    }
    
    /**
     * عرض تفاصيل مريض (Timeline Profile)
     */
    public function show(int $id): void
    {
        AuthController::checkSession();
        $role = $_SESSION['user_role'] ?? 'user';
        
        // 1. الصلاحيات
        $permissions = [
            'can_edit_patient' => in_array($role, ['admin', 'doctor']),
            'can_delete_patient' => in_array($role, ['admin']),
            'can_view_financial' => in_array($role, ['admin', 'doctor', 'accountant']),
            'can_add_attachment' => in_array($role, ['admin', 'doctor', 'assistant', 'receptionist']),
            'can_delete_attachment' => in_array($role, ['admin', 'doctor']),
            'can_print_invoice' => in_array($role, ['doctor', 'accountant']),
        ];

        $patient = Database::fetchOne("SELECT * FROM patients WHERE id = ?", [$id]);
        
        if (!$patient) {
            flash('error', 'المريض غير موجود');
            redirect('patients');
        }
        
        // 2. Timeline Generator (تجميع التاريخ المرضي)
        $timeline = [];
        
        // أ. الزيارات (Waiting List Entries)
        $visits = Database::fetchAll(
            "SELECT id, created_at, 'visit' as type, status, doctor_notes 
             FROM waiting_list 
             WHERE patient_id = ? 
             ORDER BY created_at DESC", 
            [$id]
        );
        
        // ب. المرفقات
        $attachments = Database::fetchAll(
            "SELECT id, uploaded_at as created_at, 'attachment' as type, file_name, file_path, file_type 
             FROM patient_attachments 
             WHERE patient_id = ? 
             ORDER BY uploaded_at DESC", 
            [$id]
        );

        // ج. الفواتير (إذا كان مصرحاً)
        $invoices = [];
        if ($permissions['can_view_financial']) {
            $invoices = Database::fetchAll(
                "SELECT id, created_at, 'invoice' as type, total, status, invoice_number 
                 FROM invoices 
                 WHERE patient_id = ? 
                 ORDER BY created_at DESC", 
                [$id]
            );
        }

        // دمج الأحداث
        $allEvents = array_merge($visits, $attachments, $invoices);
        
        // الترتيب الزمني العكسي
        usort($allEvents, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        // التجميع حسب التاريخ (Y-m-d)
        foreach ($allEvents as $event) {
            $date = date('Y-m-d', strtotime($event['created_at']));
            $timeline[$date][] = $event;
        }
        
        $patient['age'] = calculateAge($patient['date_of_birth']);
        
        // التحقق من الزيارة النشطة (Active Visit)
        $activeVisit = Database::fetchOne(
            "SELECT * FROM waiting_list 
             WHERE patient_id = ? AND status IN ('waiting', 'called', 'entered') 
             ORDER BY created_at DESC LIMIT 1",
            [$id]
        );
        
        $pageTitle = $patient['full_name'];
        require VIEWS_PATH . 'patients/show.php';
    }
    
    /**
     * عرض نموذج التعديل
     */
    public function edit(int $id): void
    {
        AuthController::checkSession();
        
        $patient = Database::fetchOne("SELECT * FROM patients WHERE id = ?", [$id]);
        
        if (!$patient) {
            flash('error', 'المريض غير موجود');
            redirect('patients');
        }
        
        $pageTitle = __('edit_patient') . ' - ' . $patient['full_name'];
        require VIEWS_PATH . 'patients/edit.php';
    }
    
    /**
     * تحديث بيانات مريض
     */
    public function update(int $id): void
    {
        AuthController::checkSession();
        
        // تصريح التعديل: فقط الطبيب والمدير
        // المساعد (assistant) يمكنه الإضافة فقط
        $role = $_SESSION['user_role'] ?? 'user';
        if (!in_array($role, ['admin', 'doctor'])) {
            flash('error', 'ليس لديك صلاحية لتعديل بيانات المريض. الإضافة فقط للمساعدين.');
            redirect('patients/' . $id);
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('patients/' . $id . '/edit');
        }
        
        $oldData = Database::fetchOne("SELECT * FROM patients WHERE id = ?", [$id]);
        
        if (!$oldData) {
            flash('error', 'المريض غير موجود');
            redirect('patients');
        }
        
        $data = [
            'paper_file_number' => clean($_POST['paper_file_number'] ?? ''),
            'full_name' => clean($_POST['full_name'] ?? ''),
            'phone' => clean($_POST['phone'] ?? ''),
            'secondary_phone' => clean($_POST['secondary_phone'] ?? ''),
            'date_of_birth' => clean($_POST['date_of_birth'] ?? '') ?: null,
            'gender' => clean($_POST['gender'] ?? '') ?: null,
            'address' => clean($_POST['address'] ?? ''),
            'medical_history' => clean($_POST['medical_history'] ?? ''),
            'notes' => clean($_POST['notes'] ?? ''),
        ];
        
        if (empty($data['full_name'])) {
            flash('error', 'يرجى إدخال اسم المريض');
            redirect('patients/' . $id . '/edit');
        }
        
        try {
            Database::update('patients', $data, 'id = ?', [$id]);
            
            // رفع المرفقات الجديدة
            if (!empty($_FILES['attachments']['name'][0])) {
                $this->uploadAttachments($id, $_FILES['attachments']);
            }
            
            logAction('update', 'patients', $id, $oldData, $data);
            
            flash('success', 'تم تحديث بيانات المريض بنجاح');
            redirect('patients/' . $id);
            
        } catch (Exception $e) {
            flash('error', 'حدث خطأ أثناء التحديث');
            redirect('patients/' . $id . '/edit');
        }
    }
    
    /**
     * حذف مريض (soft delete)
     */
    public function delete(int $id): void
    {
        AuthController::checkSession();
        AuthController::requireRole('doctor', 'admin');
        
        $patient = Database::fetchOne("SELECT * FROM patients WHERE id = ?", [$id]);
        
        if (!$patient) {
            if (isAjax()) {
                jsonResponse(['error' => 'المريض غير موجود'], 404);
            }
            flash('error', 'المريض غير موجود');
            redirect('patients');
        }
        
        Database::update('patients', ['is_active' => 0], 'id = ?', [$id]);
        logAction('delete', 'patients', $id, $patient);
        
        if (isAjax()) {
            jsonResponse(['success' => true, 'message' => 'تم حذف المريض بنجاح']);
        }
        
        flash('success', 'تم حذف المريض بنجاح');
        redirect('patients');
    }
    
    /**
     * توليد الرقم الإلكتروني
     */
    private function generateElectronicNumber(): string
    {
        $prefix = getSetting('electronic_number_prefix', ELECTRONIC_NUMBER_PREFIX);
        
        $lastNumber = Database::fetchOne(
            "SELECT electronic_number FROM patients ORDER BY id DESC LIMIT 1"
        );
        
        if ($lastNumber) {
            $num = (int) preg_replace('/[^0-9]/', '', $lastNumber['electronic_number']);
            $nextNum = $num + 1;
        } else {
            $nextNum = (int) getSetting('electronic_number_start', ELECTRONIC_NUMBER_START);
        }
        
        return $prefix . $nextNum;
    }
    
    /**
     * توليد الباركود
     */
    private function generateBarcode(string $name, string $electronicNumber): string
    {
        // الحصول على أول حرف من الاسم
        $firstChar = mb_substr($name, 0, 1, 'UTF-8');
        
        // تحويل الحرف العربي إلى إنجليزي
        $arabicToEnglish = [
            'أ' => 'A', 'ا' => 'A', 'إ' => 'A', 'آ' => 'A',
            'ب' => 'B', 'ت' => 'T', 'ث' => 'TH',
            'ج' => 'G', 'ح' => 'H', 'خ' => 'KH',
            'د' => 'D', 'ذ' => 'TH', 'ر' => 'R', 'ز' => 'Z',
            'س' => 'S', 'ش' => 'SH', 'ص' => 'S', 'ض' => 'D',
            'ط' => 'T', 'ظ' => 'Z', 'ع' => 'A', 'غ' => 'GH',
            'ف' => 'F', 'ق' => 'Q', 'ك' => 'K', 'ل' => 'L',
            'م' => 'M', 'ن' => 'N', 'ه' => 'H', 'و' => 'W',
            'ي' => 'Y', 'ى' => 'Y', 'ة' => 'H', 'ئ' => 'Y',
            'ؤ' => 'W', 'ء' => 'A'
        ];
        
        $letter = $arabicToEnglish[$firstChar] ?? strtoupper($firstChar);
        $number = preg_replace('/[^0-9]/', '', $electronicNumber);
        
        return BARCODE_PREFIX . '-' . $letter . $number;
    }
    
    /**
     * رفع المرفقات
     */
    private function uploadAttachments(int $patientId, array $files): void
    {
        $uploadDir = UPLOADS_PATH . 'patients/' . $patientId . '/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        foreach ($files['name'] as $key => $name) {
            if (empty($name) || $files['error'][$key] !== UPLOAD_ERR_OK) {
                continue;
            }
            
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            
            if (!in_array($extension, ALLOWED_EXTENSIONS)) {
                continue;
            }
            
            if ($files['size'][$key] > MAX_UPLOAD_SIZE) {
                continue;
            }
            
            $newName = generateUniqueId() . '.' . $extension;
            $path = $uploadDir . $newName;
            
            if (move_uploaded_file($files['tmp_name'][$key], $path)) {
                Database::insert('patient_attachments', [
                    'patient_id' => $patientId,
                    'file_name' => $name,
                    'file_path' => 'patients/' . $patientId . '/' . $newName,
                    'file_type' => $extension,
                    'file_size' => $files['size'][$key],
                    'description' => '',
                    'uploaded_by' => $_SESSION['user_id']
                ]);
            }
        }
    }
    
    /**
     * حذف مريض
     */


    /**
     * حذف مرفق
     */
    public function deleteAttachment(int $id): void
    {
        AuthController::checkSession();
        
        // تصريح الحذف: فقط الطبيب والمدير
        $role = $_SESSION['user_role'] ?? 'user';
        if (!in_array($role, ['admin', 'doctor'])) {
             jsonResponse(['error' => 'ليس لديك صلاحية لحذف المرفقات'], 403);
        }
        
        $attachment = Database::fetchOne(
            "SELECT * FROM patient_attachments WHERE id = ?",
            [$id]
        );
        
        if (!$attachment) {
            jsonResponse(['error' => 'المرفق غير موجود'], 404);
        }
        
        $filePath = UPLOADS_PATH . $attachment['file_path'];
        
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        jsonResponse(['success' => true, 'message' => 'تم حذف المرفق بنجاح']);
    }

    /**
     * رفع مرفقات (AJAX)
     */
    public function upload(): void
    {
        AuthController::checkSession();
        
        $role = $_SESSION['user_role'] ?? 'user';
        if (!in_array($role, ['admin', 'doctor', 'assistant', 'receptionist'])) {
             jsonResponse(['error' => 'ليس لديك صلاحية لرفع المرفقات'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['patient_id'])) {
            jsonResponse(['error' => 'طلب غير صالح'], 400);
        }

        $patientId = (int) $_POST['patient_id'];
        
        if (!empty($_FILES['attachments']['name'][0])) {
            $this->uploadAttachments($patientId, $_FILES['attachments']);
            jsonResponse(['success' => true]);
        }
        
        jsonResponse(['error' => 'لم يتم اختيار ملفات'], 400);
    }
}
