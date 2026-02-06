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

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: AI Secure Mailer <" . SMTP_USER . ">" . "\r\n";

    // Wrap lines safely (900 chars is close to RFC limit but safe for HTML tags)
    $final_body = wordwrap($body, 900, "\r\n");

    if (mail($to_email, $subject, $final_body, $headers)) {
        echo json_encode(['success' => true, 'message' => 'Email sent via Hostinger SMTP Proxy']);
    } else {
        echo json_encode(['error' => 'Server failed to send email. Check SMTP setup in config.php.']);
    }
    exit;
}
?>