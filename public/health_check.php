<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>System Health Check</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";

$files = [
    'config.php',
    'access_hash.php',
    'PHPMailer/index.php', // Check if directory exists (index might not, but let's check dir)
    'PHPMailer/PHPMailer.php'
];

echo "<h2>File Checks (relative to " . __DIR__ . ")</h2><ul>";
foreach ($files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<li style='color:green'>Found: $file</li>";
    } else {
        echo "<li style='color:red'>MISSING: $file</li>";
    }
}
echo "</ul>";

echo "<h2>Permission Checks</h2>";
$dirs = ['auth_codes', 'logs', 'signals'];
foreach ($dirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (!is_dir($path)) {
        echo "<p>Creating $dir... ";
        if (@mkdir($path, 0755, true))
            echo "OK";
        else
            echo "FAILED";
        echo "</p>";
    }
    if (is_writable($path)) {
        echo "<p style='color:green'>$dir is writable</p>";
    } else {
        echo "<p style='color:red'>$dir is NOT writable</p>";
    }
}

echo "<h2>Test Include</h2>";
try {
    require_once 'config.php';
    echo "<p style='color:green'>Included config.php successfully.</p>";
    if (defined('GEMINI_API_KEY')) {
        $len = strlen(GEMINI_API_KEY);
        echo "<p>GEMINI_API_KEY is defined (Length: $len)</p>";
    } else {
        echo "<p style='color:red'>GEMINI_API_KEY not defined!</p>";
    }
} catch (Throwable $e) {
    echo "<p style='color:red'>Error including config.php: " . $e->getMessage() . "</p>";
}
