<?php
define('SECURE_PATH', true);
require 'access_hash.php';

// Diagnostic script to read latest log
$logDir = __DIR__ . '/logs';
$files = glob("$logDir/*.log");
if (empty($files)) {
    echo "No logs found.";
    exit;
}
$latest = end($files);
echo "--- LOG: " . basename($latest) . " ---\n";
echo file_get_contents($latest);
?>