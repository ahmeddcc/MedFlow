<?php
require_once __DIR__ . '/app/config/config.php';
if (file_exists(__DIR__ . '/app/core/Database.php')) {
    require_once __DIR__ . '/app/core/Database.php';
} else {
    require_once __DIR__ . '/app/config/Database.php';
}

try {
    $db = Database::getConnection();
    
    echo "Executing Phase 11 (Smart Printing) SQL...\n";
    
    $sql = file_get_contents(__DIR__ . '/database/phase11_printing_db.sql');
    
    // Split by semi-colon to execute statement by statement if needed, 
    // but exec() usually handles multiple statements if driver supports it.
    // PDO::exec supports multiple statements in mysql.
    $db->exec($sql);
    
    echo "Success! Database updated.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
