<?php
$pageTitle = "النسخ الاحتياطي";
ob_start();
?>

<div class="backup-container">
    <div class="backup-header">
        <h1>🗄️ مركز النسخ الاحتياطي</h1>
        <p>حماية بياناتك من الضياع</p>
    </div>
    
    <?= showFlashMessages() ?>
    
    <div class="backup-actions">
        <div class="action-card create-backup">
            <div class="action-icon">💾</div>
            <h3>إنشاء نسخة احتياطية</h3>
            <p>حفظ نسخة من قاعدة البيانات الآن</p>
            <a href="<?= url('backup/create') ?>" class="btn-primary" onclick="return confirm('هل تريد إنشاء نسخة احتياطية الآن؟')">
                إنشاء نسخة جديدة
            </a>
        </div>
        
        <div class="action-card settings-backup">
            <div class="action-icon">⚙️</div>
            <h3>إعدادات النسخ التلقائي</h3>
            <form method="POST" action="<?= url('backup/saveSettings') ?>">
                <div class="form-row">
                    <label class="switch-label">
                        <input type="checkbox" name="auto_backup_enabled" <?= $autoBackupEnabled == '1' ? 'checked' : '' ?>>
                        <span>تفعيل النسخ التلقائي</span>
                    </label>
                </div>
                <div class="form-row">
                    <label>وقت النسخ اليومي:</label>
                    <input type="time" name="auto_backup_time" value="<?= htmlspecialchars($autoBackupTime) ?>">
                </div>
                <div class="form-row">
                    <label>الاحتفاظ بالنسخ لمدة:</label>
                    <select name="backup_retention_days">
                        <option value="7" <?= $backupRetentionDays == '7' ? 'selected' : '' ?>>7 أيام</option>
                        <option value="14" <?= $backupRetentionDays == '14' ? 'selected' : '' ?>>14 يوم</option>
                        <option value="30" <?= $backupRetentionDays == '30' ? 'selected' : '' ?>>30 يوم</option>
                        <option value="60" <?= $backupRetentionDays == '60' ? 'selected' : '' ?>>60 يوم</option>
                        <option value="90" <?= $backupRetentionDays == '90' ? 'selected' : '' ?>>90 يوم</option>
                    </select>
                </div>
                <button type="submit" class="btn-secondary">حفظ الإعدادات</button>
            </form>
        </div>
        
        <div class="action-card cloud-backup">
            <div class="action-icon">☁️</div>
            <h3>إعدادات الكلاود</h3>
            <form method="POST" action="<?= url('backup/saveCloudSettings') ?>">
                <?php 
                $cloudEnabled = getSetting('cloud_backup_enabled', '0');
                $cloudAutoUpload = getSetting('cloud_auto_upload', '0');
                $cloudType = getSetting('cloud_backup_type', 'ftp');
                $cloudHost = getSetting('cloud_backup_host', '');
                $cloudUser = getSetting('cloud_backup_user', '');
                $cloudPath = getSetting('cloud_backup_path', '/backups/');
                ?>
                <div class="form-row">
                    <label class="switch-label">
                        <input type="checkbox" name="cloud_backup_enabled" <?= $cloudEnabled == '1' ? 'checked' : '' ?>>
                        <span>تفعيل الرفع للكلاود</span>
                    </label>
                </div>
                <div class="form-row">
                    <label class="switch-label">
                        <input type="checkbox" name="cloud_auto_upload" <?= $cloudAutoUpload == '1' ? 'checked' : '' ?>>
                        <span>رفع تلقائي عند إنشاء نسخة</span>
                    </label>
                </div>
                <div class="form-row">
                    <label>نوع الاتصال:</label>
                    <select name="cloud_backup_type">
                        <option value="ftp" <?= $cloudType == 'ftp' ? 'selected' : '' ?>>FTP</option>
                        <option value="sftp" <?= $cloudType == 'sftp' ? 'selected' : '' ?>>SFTP</option>
                    </select>
                </div>
                <div class="form-row">
                    <label>عنوان السيرفر:</label>
                    <input type="text" name="cloud_backup_host" placeholder="ftp.example.com" value="<?= htmlspecialchars($cloudHost) ?>">
                </div>
                <div class="form-row">
                    <label>اسم المستخدم:</label>
                    <input type="text" name="cloud_backup_user" value="<?= htmlspecialchars($cloudUser) ?>">
                </div>
                <div class="form-row">
                    <label>كلمة المرور:</label>
                    <input type="password" name="cloud_backup_pass" placeholder="••••••••">
                </div>
                <div class="form-row">
                    <label>مسار الرفع:</label>
                    <input type="text" name="cloud_backup_path" placeholder="/backups/" value="<?= htmlspecialchars($cloudPath) ?>">
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn-secondary">حفظ الإعدادات</button>
                    <a href="<?= url('backup/testCloud') ?>" class="btn-test" onclick="return confirm('اختبار اتصال الكلاود؟')">🔗 اختبار الاتصال</a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="backup-list">
        <h2>📂 النسخ الاحتياطية المتوفرة</h2>
        
        <?php if (empty($backups)): ?>
        <div class="no-backups">
            <p>لا توجد نسخ احتياطية بعد</p>
            <small>قم بإنشاء أول نسخة احتياطية للحفاظ على بياناتك</small>
        </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>اسم الملف</th>
                    <th>الحجم</th>
                    <th>التاريخ</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($backups as $backup): ?>
                <tr>
                    <td class="filename"><?= htmlspecialchars($backup['filename']) ?></td>
                    <td><?= number_format($backup['size'] / 1024, 1) ?> KB</td>
                    <td><?= date('Y-m-d H:i', $backup['date']) ?></td>
                    <td class="actions">
                        <a href="<?= url('backup/download/' . urlencode($backup['filename'])) ?>" 
                           class="btn-action btn-download" title="تحميل">⬇️</a>
                        <a href="<?= url('backup/uploadCloud/' . urlencode($backup['filename'])) ?>" 
                           class="btn-action btn-cloud" title="رفع للكلاود"
                           onclick="return confirm('رفع هذه النسخة للكلاود؟')">☁️</a>
                        <a href="<?= url('backup/restore/' . urlencode($backup['filename'])) ?>" 
                           class="btn-action btn-restore" title="استعادة"
                           onclick="return confirm('⚠️ هل أنت متأكد من استعادة هذه النسخة؟\nسيتم استبدال البيانات الحالية!')">🔄</a>
                        <a href="<?= url('backup/delete/' . urlencode($backup['filename'])) ?>" 
                           class="btn-action btn-delete" title="حذف"
                           onclick="return confirm('هل تريد حذف هذه النسخة الاحتياطية؟')">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<style>
.backup-container { max-width: 1000px; margin: 0 auto; padding: 20px; }
.backup-header { text-align: center; margin-bottom: 30px; }
.backup-header h1 { color: #2c3e50; margin-bottom: 5px; }
.backup-header p { color: #7f8c8d; }

.backup-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
.action-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
.action-icon { font-size: 48px; margin-bottom: 15px; }
.action-card h3 { margin: 0 0 10px 0; color: #2c3e50; }
.action-card p { color: #7f8c8d; margin-bottom: 15px; font-size: 14px; }

.btn-primary { display: inline-block; background: linear-gradient(135deg, #27ae60, #2ecc71); color: white; padding: 12px 25px; border-radius: 8px; text-decoration: none; font-weight: bold; transition: transform 0.2s; }
.btn-primary:hover { transform: translateY(-2px); }

.btn-secondary { background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; margin-top: 10px; }
.btn-secondary:hover { background: #2980b9; }

.form-row { margin-bottom: 12px; text-align: right; }
.form-row label { display: block; margin-bottom: 5px; color: #555; font-size: 14px; }
.form-row input[type="time"], .form-row select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }

.switch-label { display: flex; align-items: center; gap: 10px; cursor: pointer; }
.switch-label input { width: 18px; height: 18px; }

.backup-list { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.backup-list h2 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px; }

.no-backups { text-align: center; padding: 40px; color: #95a5a6; }
.no-backups p { font-size: 18px; margin-bottom: 5px; }

.data-table { width: 100%; border-collapse: collapse; }
.data-table th, .data-table td { padding: 12px; text-align: center; border-bottom: 1px solid #eee; }
.data-table th { background: #f8f9fa; color: #2c3e50; font-weight: 600; }
.data-table .filename { text-align: right; font-family: monospace; font-size: 13px; }
.data-table .actions { white-space: nowrap; }

.btn-action { display: inline-block; padding: 5px 10px; margin: 0 2px; border-radius: 4px; text-decoration: none; font-size: 16px; transition: all 0.2s; }
.btn-download { background: #3498db; }
.btn-download:hover { background: #2980b9; }
.btn-restore { background: #f39c12; }
.btn-restore:hover { background: #d68910; }
.btn-delete { background: #e74c3c; }
.btn-delete:hover { background: #c0392b; }
.btn-cloud { background: #9b59b6; }
.btn-cloud:hover { background: #8e44ad; }

.btn-group { display: flex; gap: 10px; margin-top: 10px; }
.btn-test { display: inline-block; background: #1abc9c; color: white; padding: 8px 15px; border-radius: 6px; text-decoration: none; font-size: 13px; text-align: center; }
.btn-test:hover { background: #16a085; }
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
