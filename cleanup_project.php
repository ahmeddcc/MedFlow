<?php
// cleanup.php
header('Content-Type: text/plain');

$setupDir = __DIR__ . '/_setup';
if (!is_dir($setupDir)) {
    mkdir($setupDir);
    echo "Created directory: _setup\n";
}

$filesToMove = glob(__DIR__ . '/execute_*.php');
$sqlFiles = glob(__DIR__ . '/*.sql');
$filesToMove = array_merge($filesToMove, $sqlFiles);

$count = 0;
foreach ($filesToMove as $file) {
    if (basename($file) === 'cleanup.php') continue;
    
    $dest = $setupDir . '/' . basename($file);
    if (rename($file, $dest)) {
        echo "Moved: " . basename($file) . "\n";
        $count++;
    } else {
        echo "Failed to move: " . basename($file) . "\n";
    }
}

// Protect the setup directory
file_put_contents($setupDir . '/.htaccess', "Order allow,deny\nDeny from all");
echo "\nMoved $count files to _setup/\n";
echo "Protected _setup/ with .htaccess\n";
echo "Cleanup Complete.";
