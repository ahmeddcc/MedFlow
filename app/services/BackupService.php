<?php
/**
 * خدمة النسخ الاحتياطي
 * Backup Service
 * تقوم بإنشاء نسخ احتياطية لقاعدة البيانات
 */
class BackupService
{
    private $backupDir;
    private $dbConfig;

    public function __construct()
    {
        $this->backupDir = dirname(dirname(__DIR__)) . '/backups/';
        if (!file_exists($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
        
        // جلب إعدادات الاتصال من ملف الكونفيج
        $this->dbConfig = [
            'host' => defined('DB_HOST') ? DB_HOST : 'localhost',
            'user' => defined('DB_USER') ? DB_USER : 'root',
            'pass' => defined('DB_PASS') ? DB_PASS : '',
            'name' => defined('DB_NAME') ? DB_NAME : 'medflow_db'
        ];
    }

    /**
     * إنشاء نسخة احتياطية جديدة
     * @return string اسم الملف الذي تم إنشاؤه
     */
    public function createBackup(): string
    {
        $date = date('Y-m-d_H-i-s');
        $filename = "backup_{$this->dbConfig['name']}_{$date}.sql";
        $filepath = $this->backupDir . $filename;
        
        $content = "-- MedFlow Database Backup\n";
        $content .= "-- Date: " . date('Y-m-d H:i:s') . "\n";
        $content .= "-- Database: " . $this->dbConfig['name'] . "\n\n";
        $content .= "SET NAMES utf8mb4;\n";
        $content .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        try {
            $tables = Database::fetchAll("SHOW TABLES");
            foreach ($tables as $row) {
                $table = $row[0]; // اسم الجدول
                $content .= $this->dumpTable($table);
            }
            
            $content .= "SET FOREIGN_KEY_CHECKS = 1;\n";
            
            if (file_put_contents($filepath, $content)) {
                return $filename;
            } else {
                throw new Exception("فشل في كتابة ملف النسخة الاحتياطية");
            }
            
        } catch (Exception $e) {
            error_log("Backup Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * تفريغ جدول واحد
     */
    private function dumpTable($table): string
    {
        $output = "-- Table structure for table `$table`\n";
        $createTable = Database::fetchOne("SHOW CREATE TABLE `$table`");
        $output .= "DROP TABLE IF EXISTS `$table`;\n";
        $output .= $createTable['Create Table'] . ";\n\n";
        
        $output .= "-- Dumping data for table `$table`\n";
        $data = Database::fetchAll("SELECT * FROM `$table`");
        
        if (!empty($data)) {
            $output .= "INSERT INTO `$table` VALUES ";
            $rows = [];
            foreach ($data as $row) {
                $values = [];
                foreach ($row as $value) {
                    if ($value === null) {
                        $values[] = "NULL";
                    } else {
                        $values[] = "'" . addslashes($value) . "'";
                    }
                }
                $rows[] = "(" . implode(", ", $values) . ")";
            }
            $output .= implode(",\n", $rows) . ";\n";
        }
        
        $output .= "\n";
        return $output;
    }

    /**
     * الحصول على قائمة النسخ الاحتياطية
     */
    public function listBackups(): array
    {
        if (!file_exists($this->backupDir)) return [];

        $files = [];
        foreach (glob($this->backupDir . "*.sql") as $path) {
            $files[] = [
                'name' => basename($path),
                'size' => filesize($path),
                'time' => filemtime($path),
                'path' => $path
            ];
        }
        
        // ترتيب تنازلي بالتاريخ
        usort($files, function($a, $b) {
            return $b['time'] - $a['time'];
        });
        
        return $files;
    }

    /**
     * الحصول على مسار ملف
     */
    public function getBackupPath(string $filename): ?string
    {
        $path = $this->backupDir . basename($filename);
        return file_exists($path) ? $path : null;
    }
}
