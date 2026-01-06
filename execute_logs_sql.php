<?php
require_once __DIR__ . '/app/config/config.php';
if (file_exists(__DIR__ . '/app/core/Database.php')) {
    require_once __DIR__ . '/app/core/Database.php';
} else {
    require_once __DIR__ . '/app/config/Database.php';
}

try {
    $db = Database::getConnection();
    
    echo "Executing Phase 10 (Activity Logs) SQL...\n";
    
    $sql = file_get_contents(__DIR__ . '/database/phase10_activity_logs.sql');
    $db->exec($sql);
    
    echo "Success! Database updated.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
