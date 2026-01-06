<?php
define('ROOT', __DIR__ . '/app');
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/Database.php';

try {
    echo "Connecting to database...\n";
    $pdo = Database::getConnection();
    
    $sqlFile = __DIR__ . '/database/phase3_queue_update.sql';
    if (!file_exists($sqlFile)) {
        die("Error: SQL file not found: $sqlFile\n");
    }
    
    $sqlContent = file_get_contents($sqlFile);
    
    // Remove comments
    $sqlContent = preg_replace('/--.*$/m', '', $sqlContent);
    
    $statements = explode(';', $sqlContent);
    
    foreach ($statements as $sql) {
        $sql = trim($sql);
        if (!empty($sql)) {
            echo "Executing: " . substr($sql, 0, 50) . "...\n";
            try {
                $pdo->exec($sql);
            } catch (PDOException $e) {
                // Ignore "Duplicate column name" error if re-running
                if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                    echo "Column already exists, skipping.\n";
                } elseif (strpos($e->getMessage(), 'Table') !== false && strpos($e->getMessage(), 'already exists') !== false) {
                    echo "Table already exists, skipping.\n";
                } else {
                    throw $e;
                }
            }
        }
    }
    
    echo "Migration completed successfully.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
