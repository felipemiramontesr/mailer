<?php
require 'config.php';
header('Content-Type: application/json');

// Check if key is defined
$key = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : 'NOT_DEFINED';

// URL to list models
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $key;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For simple testing
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

$decodedResponse = json_decode($response, true);

echo json_encode([
    'diagnostic' => 'List Models',
    'http_code' => $httpCode,
    'curl_error' => $curlError,
    'key_prefix' => substr($key, 0, 4) . '...',
    'available_models' => $decodedResponse
], JSON_PRETTY_PRINT);
