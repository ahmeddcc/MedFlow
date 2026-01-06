<?php
require_once __DIR__ . '/app/config/config.php';
// Check if Database is in core or config
if (file_exists(__DIR__ . '/app/core/Database.php')) {
    require_once __DIR__ . '/app/core/Database.php';
} else {
    require_once __DIR__ . '/app/config/Database.php';
}

try {
    $db = Database::getConnection();
    
    echo "Executing Phase 10 (Permissions) SQL...\n";
    
    $sql = file_get_contents(__DIR__ . '/database/phase10_permissions_db.sql');
    
    // Split by semicolon usually works for simple dumps, but let's try to execute as one block if possible or split carefully
    // PDO might handle multiple statements if supported by driver, but splitting is safer usually.
    // However, Database::query prepares syntax.
    
    // Simple split by ;
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $db->exec($statement);
        }
    }
    
    echo "Success! Database updated.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
