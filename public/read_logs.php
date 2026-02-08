<?php
define('SECURE_PATH', true);
require 'access_hash.php';

$password = $_GET['password'] ?? '';
if (!password_verify($password, MAILER_PASSWORD_HASH)) {
    header('HTTP/1.0 401 Unauthorized');
    exit('Unauthorized.');
}

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