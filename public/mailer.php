<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if (!$action) {
    echo json_encode(['error' => 'No action specified']);
    exit;
}

// Check if we are in Mock Mode (Secrets NOT yet injected)
$isMockGemini = (GEMINI_API_KEY === '__GEMINI_API_KEY__');
$isMockSMTP = (SMTP_PASS === '__SMTP_PASS__');

// 1. AI Polish & Translation Logic
if ($action === 'polish' || $action === 'translate') {
    $message = $input['message'] ?? '';
    $prompt = $input['prompt'] ?? '';

    if (!$message || !$prompt) {
        echo json_encode(['error' => 'Missing message or prompt']);
        exit;
    }

    if ($isMockGemini) {
        $prefix = ($action === 'polish') ? "[MOCK POLISH] " : "[MOCK TRANSLATION] ";
        echo json_encode(['result' => $prefix . $message . " (Secrets pending, showing simulated result)"]);
        exit;
    }

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . GEMINI_API_KEY;

    $payload = [
        "contents" => [
            ["parts" => [["text" => $prompt]]]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        echo json_encode(['error' => 'Gemini API Error', 'details' => $response]);
        exit;
    }

    $data = json_decode($response, true);
    $resultText = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Error processing AI response';

    echo json_encode(['result' => $resultText]);
    exit;
}

// 2. Send Email Logic
if ($action === 'send') {
    $to_email = $input['to_email'] ?? '';
    $subject = $input['subject'] ?? '';
    $body = $input['body'] ?? '';

    if (!$to_email || !$body) {
        echo json_encode(['error' => 'Missing email details']);
        exit;
    }

    if ($isMockSMTP) {
        echo json_encode(['success' => true, 'message' => 'Simulated Email Sent! (SMTP secrets are pending)']);
        exit;
    }

    // Prepare headers for Quoted-Printable (High-end stability for HTML)
    $encoded_subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "Content-Transfer-Encoding: quoted-printable" . "\r\n";
    $headers .= "From: B. Eng. Felipe de Jesús Miramontes Romero <" . SMTP_USER . ">" . "\r\n";
    $headers .= "Reply-To: " . SMTP_USER . "\r\n";
    $headers .= "X-Mailer: Navy Tech Mailer/V2.5" . "\r\n";

    // quoted_printable_encode handles long HTML lines perfectly for SMTP filters
    $final_body = quoted_printable_encode($body);

    // Diagnostic Logging
    $log_entry = "[" . date("Y-m-d H:i:s") . "] To: $to_email | Subject: $subject | Size: " . strlen($body) . " bytes\n";
    file_put_contents(__DIR__ . "/mailer_debug.log", $log_entry, FILE_APPEND);

    if (mail($to_email, $encoded_subject, $final_body, $headers)) {
        echo json_encode(['success' => true, 'message' => 'Email sent via Secure Node (V2.4)']);
    } else {
        $error = error_get_last();
        file_put_contents(__DIR__ . "/mailer_debug.log", "FAILED: " . print_r($error, true) . "\n", FILE_APPEND);
        echo json_encode(['error' => 'Server failed to send email. Check mailer_debug.log on server.']);
    }
    exit;
}
?>