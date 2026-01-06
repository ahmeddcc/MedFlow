<?php
// test_settings_debug.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('MEDFLOW', true);
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/Database.php';
require_once __DIR__ . '/app/helpers/functions.php';
require_once __DIR__ . '/app/core/Controller.php';

echo "Database loaded.<br>";

try {
    require_once __DIR__ . '/app/services/BackupService.php';
    echo "BackupService file included.<br>";
    
    $svc = new BackupService();
    echo "BackupService instantiated.<br>";
    
    $backups = $svc->listBackups();
    echo "Backups listed: " . count($backups) . "<br>";
    
} catch (Throwable $e) {
    echo "Error in BackupService: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "Testing SettingsController logic...<br>";
// We won't instantiate controller because it checks session/auth which we don't have here.
// But we checked the core logic above.

echo "Done.";
