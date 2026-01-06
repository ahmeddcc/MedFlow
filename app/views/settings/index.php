<?php
$pageTitle = "مركز التحكم والإعدادات";
ob_start();
?>

<div class="settings-container">
    <div class="settings-header">
        <h1>⚙️ مركز التحكم وإعدادات النظام</h1>
    </div>

    <!-- Flash Messages -->
    <?= showFlashMessages() ?>

    <!-- Tabs Navigation -->
    <div class="settings-tabs">
        <button type="button" class="tab-btn active" onclick="openTab(event, 'general')">عام</button>
        <button type="button" class="tab-btn" onclick="openTab(event, 'toggles')">المميزات</button>
        <button type="button" class="tab-btn" onclick="openTab(event, 'telegram')">تيليجرام</button>
        <button type="button" class="tab-btn" onclick="openTab(event, 'printers')">🖨️ الطابعات</button>
        <button type="button" class="tab-btn" onclick="openTab(event, 'backups')">💾 النسخ الاحتياطي</button>
        <button type="button" class="tab-btn" onclick="openTab(event, 'license')">🛡️ الترخيص</button>
    </div>

    <!-- 1. General Settings -->
    <div id="general" class="tab-content active" style="display: block;">
        <form action="<?= url('settings/save') ?>" method="POST" enctype="multipart/form-data" class="settings-form">
            <input type="hidden" name="form_type" value="general">
            
            <div class="form-section">
                <h3>بيانات العيادة</h3>
                <div class="form-group">
                    <label>اسم العيادة</label>
                    <input type="text" name="clinic_name" value="<?= htmlspecialchars($settings['clinic_name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>هاتف العيادة</label>
                    <input type="text" name="clinic_phone" value="<?= htmlspecialchars($settings['clinic_phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>العنوان</label>
                    <input type="text" name="clinic_address" value="<?= htmlspecialchars($settings['clinic_address'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>اسم الطبيب</label>
                    <input type="text" name="doctor_name" value="<?= htmlspecialchars($settings['doctor_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>شعار العيادة (صورة)</label>
                    <input type="file" name="clinic_logo" accept="image/*">
                </div>
            </div>
            <button type="submit" class="btn-save">حفظ التغييرات</button>
        </form>
    </div>

    <!-- 2. Feature Toggles -->
    <div id="toggles" class="tab-content">
        <form action="<?= url('settings/save') ?>" method="POST" class="settings-form">
            <input type="hidden" name="form_type" value="toggles">
            
            <div class="toggles-grid">
                <div class="toggle-card">
                    <h4>💰 المحاسبة والديون</h4>
                    <label class="switch">
                        <input type="checkbox" name="enable_debts" <?= (isset($settings['enable_debts']) && $settings['enable_debts'] == '1') ? 'checked' : '' ?>>
                        <span class="slider round"></span>
                    </label>
                </div>

                <div class="toggle-card">
                    <h4>🧪 تسعير التحاليل</h4>
                    <label class="switch">
                        <input type="checkbox" name="enable_lab_pricing" <?= (isset($settings['enable_lab_pricing']) && $settings['enable_lab_pricing'] == '1') ? 'checked' : '' ?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                
                <div class="toggle-card">
                    <h4>☢️ تسعير الأشعة</h4>
                    <label class="switch">
                        <input type="checkbox" name="enable_rad_pricing" <?= (isset($settings['enable_rad_pricing']) && $settings['enable_rad_pricing'] == '1') ? 'checked' : '' ?>>
                        <span class="slider round"></span>
                    </label>
                </div>

                <div class="toggle-card">
                    <h4>🖨️ طباعة الفواتير</h4>
                    <label class="switch">
                        <input type="checkbox" name="enable_patient_printing" <?= (isset($settings['enable_patient_printing']) && $settings['enable_patient_printing'] == '1') ? 'checked' : '' ?>>
                        <span class="slider round"></span>
                    </label>
                </div>

                <div class="toggle-card">
                    <h4>📅 جدولة المندوبين</h4>
                    <label class="switch">
                        <input type="checkbox" name="enable_smart_scheduling" <?= (isset($settings['enable_smart_scheduling']) && $settings['enable_smart_scheduling'] == '1') ? 'checked' : '' ?>>
                        <span class="slider round"></span>
                    </label>
                </div>
                
                 <div class="toggle-card">
                    <h4>📺 وضع شاشة الانتظار</h4>
                    <label class="switch">
                        <input type="checkbox" name="enable_idle_branding" <?= (isset($settings['enable_idle_branding']) && $settings['enable_idle_branding'] == '1') ? 'checked' : '' ?>>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
            <br>
            <button type="submit" class="btn-save">حفظ المميزات</button>
        </form>
    </div>

    <!-- 3. Telegram -->
    <div id="telegram" class="tab-content">
        <form action="<?= url('settings/save') ?>" method="POST" class="settings-form">
            <input type="hidden" name="form_type" value="telegram">
            
            <div class="form-section">
                <h3>🤖 بوت العمليات</h3>
                <div class="form-group">
                    <label>Bot Token</label>
                    <input type="text" name="telegram_bot_token" value="<?= htmlspecialchars($settings['telegram_bot_token'] ?? '') ?>" class="code-input">
                </div>
                <div class="form-group">
                    <label>Chat ID</label>
                    <input type="text" name="telegram_chat_id" value="<?= htmlspecialchars($settings['telegram_chat_id'] ?? '') ?>" class="code-input">
                </div>
                <div class="form-group">
                    <label class="switch">
                        <input type="checkbox" name="telegram_enabled" <?= (isset($settings['telegram_enabled']) && $settings['telegram_enabled'] == '1') ? 'checked' : '' ?>>
                        <span class="slider round"></span>
                    </label>
                    <span>تفعيل</span>
                </div>
                <button type="button" class="btn btn-secondary" onclick="testBot('ops')">📡 اختبار</button>
            </div>

            <hr>

            <div class="form-section">
                <h3>🛠️ بوت الدعم (Sentinel)</h3>
                <div class="form-group">
                    <label>Sentinel Token</label>
                    <input type="text" name="telegram_support_bot_token" value="<?= htmlspecialchars($settings['telegram_support_bot_token'] ?? '') ?>" class="code-input">
                </div>
                <div class="form-group">
                    <label>Developer Chat ID</label>
                    <input type="text" name="telegram_support_chat_id" value="<?= htmlspecialchars($settings['telegram_support_chat_id'] ?? '') ?>" class="code-input">
                </div>
                 <div class="form-group">
                    <label class="switch">
                        <input type="checkbox" name="telegram_support_enabled" <?= (isset($settings['telegram_support_enabled']) && $settings['telegram_support_enabled'] == '1') ? 'checked' : '' ?>>
                        <span class="slider round"></span>
                    </label>
                    <span>تفعيل</span>
                </div>
                <button type="button" class="btn btn-secondary" onclick="testBot('support')">🛡️ اختبار</button>
            </div>
            <br>
            <button type="submit" class="btn-save">حفظ الإعدادات</button>
        </form>
    </div>

    <!-- 4. Printers -->
    <div id="printers" class="tab-content">
        <form action="<?= url('settings/save') ?>" method="POST" class="settings-form">
            <input type="hidden" name="form_type" value="printers">
            
            <div class="form-section">
                <h3>➕ إضافة طابعة</h3>
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" name="printer_name" class="form-control" placeholder="اسم الطابعة (Windows Name)">
                    </div>
                    <div class="col-md-3">
                        <select name="printer_type" class="form-control">
                            <option value="thermal">حراري (80mm)</option>
                            <option value="a4">ليزر (A4)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="printer_location" class="form-control">
                            <option value="reception">الاستقبال</option>
                            <option value="doctor_room">الطبيب</option>
                        </select>
                    </div>
                </div>
            </div>
            <br>
            
            <div class="printers-list">
                 <h4>الطابعات المعرفة:</h4>
                 <table class="data-table" style="width:100%">
                    <thead><tr><th>الاسم</th><th>النوع</th><th>المكان</th><th>إجراء</th></tr></thead>
                    <tbody>
                        <?php if(!empty($settings['printers'])): foreach($settings['printers'] as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= $p['type'] == 'thermal' ? 'حراري' : ($p['type'] == 'a4' ? 'ليزر A4' : $p['type']) ?></td>
                            <td><?= $p['location'] == 'reception' ? 'الاستقبال' : ($p['location'] == 'doctor_room' ? 'الطبيب' : $p['location']) ?></td>
                            <td>
                                <a href="<?= url('settings/deletePrinter/' . $p['id']) ?>" 
                                   onclick="return confirm('هل أنت متأكد من حذف هذه الطابعة؟')" 
                                   class="btn-danger-sm">🗑️ حذف</a>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="4" style="text-align:center; color:#888;">لا توجد طابعات معرفة</td></tr>
                        <?php endif; ?>
                    </tbody>
                 </table>
            </div>
            
            <hr>
            
            <div class="form-section">
                <h3>🔀 توجيه المستندات</h3>
                <table class="data-table" style="width:100%">
                    <thead><tr><th>المستند</th><th>الطابعة</th><th>الحجم</th><th>تلقائي</th></tr></thead>
                    <tbody>
                        <?php 
                        $types = [
                            'receipt' => 'إيصالات الكشف',
                            'invoice' => 'فواتير',
                            'prescription' => 'روشتات',
                            'lab_result' => 'نتائج التحاليل'
                        ];
                        foreach($types as $key=>$label): 
                            $curr = $settings['routing_map'][$key] ?? [];
                        ?>
                        <tr>
                            <td><?= $label ?></td>
                            <td>
                                <select name="routing[<?= $key ?>][printer_id]" class="form-control">
                                    <option value="0">-- اختر --</option>
                                    <?php if(!empty($settings['printers'])): foreach($settings['printers'] as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= ($curr['printer_id']??0)==$p['id']?'selected':'' ?>><?= $p['name'] ?></option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </td>
                            <td>
                                <select name="routing[<?= $key ?>][format]" class="form-control">
                                    <option value="a4" <?= ($curr['template_format']??'')=='a4'?'selected':'' ?>>A4</option>
                                    <option value="a5" <?= ($curr['template_format']??'')=='a5'?'selected':'' ?>>A5</option>
                                    <option value="thermal_80mm" <?= ($curr['template_format']??'')=='thermal_80mm'?'selected':'' ?>>حراري</option>
                                </select>
                            </td>
                            <td><input type="checkbox" name="routing[<?= $key ?>][auto]" value="1" <?= ($curr['auto_print']??0)?'checked':'' ?>></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <br>
            <button type="submit" class="btn-save">حفظ الطابعات</button>
        </form>
    </div>

</div>

<style>
/* Emergency CSS Fixes */
.settings-container { max-width: 900px; margin: 2rem auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); direction: rtl; text-align: right; }
.settings-header { background: #007bff; color: white; padding: 15px; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px; }
.settings-tabs { display: flex; border-bottom: 2px solid #eee; margin-bottom: 20px; }
.tab-btn { padding: 10px 20px; border: none; background: none; cursor: pointer; font-size: 16px; border-bottom: 3px solid transparent; }
.tab-btn.active { border-bottom-color: #007bff; color: #007bff; font-weight: bold; }
.tab-content { display: none; }
.tab-content.active { display: block; }
.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
.form-control, input[type="text"], input[type="number"], select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
.btn-save { background: #28a745; color: white; border: none; padding: 10px 30px; border-radius: 4px; font-size: 16px; cursor: pointer; width: 100%; }
.btn-save:hover { background: #218838; }
.btn-secondary { background: #6c757d; color: white; border: none; padding: 5px 15px; border-radius: 4px; cursor: pointer; margin-top: 5px; }
.code-input { font-family: monospace; direction: ltr; text-align: left; }

/* Switch Toggle */
.switch { position: relative; display: inline-block; width: 50px; height: 24px; vertical-align: middle; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
.slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
input:checked + .slider { background-color: #007bff; }
input:checked + .slider:before { transform: translateX(26px); }

.toggles-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; }
.toggle-card { border: 1px solid #eee; padding: 15px; border-radius: 8px; background: #f9f9f9; }
.toggle-card h4 { margin-top: 0; color: #333; }

.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
.data-table th, .data-table td { border: 1px solid #ddd; padding: 8px; text-align: center; }
.data-table th { background-color: #f2f2f2; }

.btn-danger-sm { background: #dc3545; color: white; padding: 4px 10px; border-radius: 4px; text-decoration: none; font-size: 13px; }
.btn-danger-sm:hover { background: #c82333; }
</style>


    <!-- 6. Backups -->
    <div id="backups" class="tab-content">
        <div class="form-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>نسخ قواعد البيانات</h3>
                <form action="<?= url('backups/create') ?>" method="POST" style="display:inline;"> 
                    <button type="submit" class="btn btn-primary">➕ إنشاء نسخة جديدة</button>
                </form>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>اسم الملف</th>
                        <th>الحجم</th>
                        <th>التاريخ</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($settings['backups'])): foreach($settings['backups'] as $file): ?>
                    <tr>
                        <td dir="ltr"><?= $file['name'] ?></td>
                        <td dir="ltr"><?= round($file['size'] / 1024, 2) ?> KB</td>
                        <td dir="ltr"><?= date('Y-m-d H:i:s', $file['time']) ?></td>
                        <td>
                            <a href="<?= url('backups/download?file=' . urlencode($file['name'])) ?>" class="btn btn-sm btn-info">⬇️ تحميل</a>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">لا توجد نسخ احتياطية</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5. License Settings -->
    <div id="license" class="tab-content">
        <?php 
        require_once __DIR__ . '/../../services/LicenseService.php';
        $machineID = LicenseService::getMachineID();
        $status = LicenseService::getStatus();
        $statusText = $status === 'active' ? 'نشط ✅' : ($status === 'trial' ? 'تجريبي ⏳' : 'غير صالح ❌');
        $statusClass = $status === 'active' ? 'text-success' : ($status === 'trial' ? 'text-warning' : 'text-danger');
        ?>
        <div class="license-info-card">
            <h3>حالة النظام: <span class="<?= $statusClass ?>"><?= $statusText ?></span></h3>
            
            <div class="machine-id-container">
                <label>معرف الجهاز (Machine ID):</label>
                <div class="code-box"><?= $machineID ?></div>
                <small class="text-muted">هذا المعرف فريد لهذا الجهاز ومسار التنصيب. أي تغيير في السيرفر أو المسار قد يبطل الترخيص.</small>
            </div>
        </div>

        <form action="<?= url('settings/saveLicenseSettings') ?>" method="POST" class="settings-form mt-4">
            <div class="form-group">
                <label>مفتاح الترخيص (License Key)</label>
                <input type="text" name="license_key" class="form-control mono-font" 
                       value="<?= htmlspecialchars(getSetting('license_key', '')) ?>" 
                       placeholder="XXXX-XXXX-XXXX-XXXX" style="letter-spacing: 1px;">
            </div>
            <button type="submit" class="btn-save width-auto">تفعيل / تحديث الترخيص</button>
        </form>
    </div>

    <script>
    function openTab(evt, tabName) {
    // console.log("Switching to tab: " + tabName);
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
        tabcontent[i].classList.remove("active");
    }
    tablinks = document.getElementsByClassName("tab-btn");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].classList.remove("active");
    }
    document.getElementById(tabName).style.display = "block";
    document.getElementById(tabName).classList.add("active");
    evt.currentTarget.classList.add("active");
}

// Auto Test Bot
function testBot(type) {
    const btn = event.target;
    btn.innerText = '⏳ جاري الاتصال...';
    btn.disabled = true;
    
    const formData = new FormData();
    formData.append('type', type);
    
    fetch('<?= url('settings/testBot') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.ok) {
            alert('✅ تم الاتصال بنجاح!');
        } else {
            alert('❌ فشل الاتصال: ' + (data.description || 'تأكد من التوكن'));
        }
    })
    .catch(err => alert('خطأ في الشبكة'))
    .finally(() => {
        btn.innerText = type === 'ops' ? '📡 اختبار' : '🛡️ اختبار';
        btn.disabled = false;
    });
}

// Hash Navigation
document.addEventListener("DOMContentLoaded", function() {
    const hash = window.location.hash.substring(1);
    if (hash && document.getElementById(hash)) {
        document.querySelector(`.tab-btn[onclick*="'${hash}'"]`).click();
    }
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
