<?php
/**
 * =====================================================
 * MedFlow - فئة الاتصال بقاعدة البيانات
 * =====================================================
 */

class Database
{
    private static ?PDO $connection = null;
    
    /**
     * الحصول على اتصال قاعدة البيانات (Singleton)
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    DB_HOST,
                    DB_PORT,
                    DB_NAME,
                    DB_CHARSET
                );
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ];
                
                self::$connection = new PDO($dsn, DB_USER, DB_PASS, $options);
                
            } catch (PDOException $e) {
                if (APP_ENV === 'development') {
                    die('خطأ في الاتصال بقاعدة البيانات: ' . $e->getMessage());
                } else {
                    die('خطأ في الاتصال بقاعدة البيانات. يرجى المحاولة لاحقاً.');
                }
            }
        }
        
        return self::$connection;
    }
    
    /**
     * تنفيذ استعلام مع معاملات
     */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * جلب صف واحد
     */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = self::query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * جلب جميع الصفوف
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * إدراج سجل جديد
     */
    public static function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        self::query($sql, array_values($data));
        
        return (int) self::getConnection()->lastInsertId();
    }
    
    /**
     * تحديث سجل
     */
    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = ?";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $stmt = self::query($sql, array_merge(array_values($data), $whereParams));
        
        return $stmt->rowCount();
    }
    
    /**
     * حذف سجل
     */
    public static function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * عد السجلات
     */
    public static function count(string $table, string $where = '1=1', array $params = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$where}";
        $result = self::fetchOne($sql, $params);
        return (int) ($result['count'] ?? 0);
    }
    
    /**
     * بدء معاملة
     */
    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }
    
    /**
     * تأكيد المعاملة
     */
    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }
    
    /**
     * التراجع عن المعاملة
     */
    public static function rollback(): bool
    {
        return self::getConnection()->rollBack();
    }
    
    /**
     * إغلاق الاتصال
     */
    public static function close(): void
    {
        self::$connection = null;
    }
}
