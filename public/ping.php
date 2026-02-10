<?php
echo json_encode([
    "status" => "online",
    "version" => "7.8.2-DIAGNOSTIC",
    "cwd" => getcwd(),
    "php_version" => phpversion()
]);
?>