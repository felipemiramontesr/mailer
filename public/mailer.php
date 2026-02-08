<?php
/**
 * Professional Mailer Proxy v3.0 (Senior+ Edition)
 * Handles AI polishing, 2FA authorization, and SMTP transmission.
 */

// --- Pre-flight Headers & Security ---
define('SECURE_PATH', true);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// World-Class Security: Content Security Policy
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; connect-src 'self' https://generativelanguage.googleapis.com;");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

// --- MIMICRY HEADERS (Cyber-Warfare Camouflage) ---
header("X-Powered-By: Siemens-S7-1200/PLC-OS");
header("X-Security-Node: FELIPE-MIRAMONTES-B_ENG");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once 'config.php';
require_once 'access_hash.php';

/**
 * Technical Rate Limiter (Token Bucket Pattern)
 */
function rateLimitCheck($ip)
{
    $limitDir = __DIR__ . '/auth_codes/ratelimits';
    if (!is_dir($limitDir))
        mkdir($limitDir, 0700, true);

    $file = $limitDir . '/' . md5($ip) . '.json';
    $now = time();
    $capacity = 5; // Max 5 tokens
    $refillRate = 1 / 60; // 1 token per 60 seconds

    $data = file_exists($file) ? json_decode(file_get_contents($file), true) : ['tokens' => $capacity, 'last' => $now];

    // Refill tokens
    $data['tokens'] = min($capacity, $data['tokens'] + ($now - $data['last']) * $refillRate);
    $data['last'] = $now;

    if ($data['tokens'] < 1) {
        file_put_contents($file, json_encode($data));
        return false;
    }

    $data['tokens'] -= 1;
    file_put_contents($file, json_encode($data));
    return true;
}

/**
 * Standardized Response Wrapper
 */
function sendResponse($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

/**
 * Secure Logger
 */
function technicalLog($message, $isError = false)
{
    $logFile = __DIR__ . "/logs/mailer_" . date("Y-m-d") . ".log";
    if (!is_dir(__DIR__ . "/logs"))
        mkdir(__DIR__ . "/logs", 0755);
    $timestamp = date("Y-m-d H:i:s");
    $type = $isError ? "[ERROR]" : "[INFO]";
    file_put_contents($logFile, "$timestamp $type $message\n", FILE_APPEND);
}

// --- Bootstrap ---
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    sendResponse(['error' => 'Invalid JSON payload'], 400);
}

$action = $input['action'] ?? null;
$password = $input['password'] ?? '';
$authCode = $input['auth_code'] ?? '';

// --- Security Phase 1: Master Password ---
$public_actions = ['fetch_signal', 'shred_signal'];

if (!in_array($action, $public_actions)) {
    if (!password_verify($password, MAILER_PASSWORD_HASH)) {
        technicalLog("Unauthorized access attempt. IP: " . $_SERVER['REMOTE_ADDR'], true);
        sendResponse(['error' => 'Access Denied. Invalid credentials.'], 401);
    }
}

// --- BLACK-OPS ACTION ROUTING ---
if ($action === 'store_signal')
    handleStoreSignal($input);
if ($action === 'fetch_signal')
    handleFetchSignal($input);
if ($action === 'shred_signal')
    handleShredSignal($input);

// --- Security Phase 2: 2FA Authorization ---
$authDir = __DIR__ . '/auth_codes';
if (!is_dir($authDir))
    mkdir($authDir, 0700);
$codeFile = $authDir . '/current_code.json';

// Reuse PHPMailer logic
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (empty($authCode)) {
    // Phase 5: Rate Limiting
    if (!rateLimitCheck($_SERVER['REMOTE_ADDR'])) {
        technicalLog("Rate Limit EXCEEDED for IP: " . $_SERVER['REMOTE_ADDR'], true);
        sendResponse(['error' => 'Rate Limit exceeded. Please wait 60 seconds before retrying.'], 429);
    }

    // Generate new PIN
    $newPIN = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiry = time() + (5 * 60); // 5 Minutes

    file_put_contents($codeFile, json_encode(['code' => $newPIN, 'expires' => $expiry]));

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = (SMTP_SECURE === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(SMTP_USER, 'Felipe Miramontes (SECURE_NODE)');
        $mail->addAddress(MASTER_AUTH_EMAIL);
        $mail->Subject = "VERIFICATION PIN: $newPIN";

        // Elite HUD v5.0 2FA Template (Universal Elite)
        $mail->isHTML(true);
        $currentDate = date('Y-m-d');
        $mail->Body = "
        <div style=\"background: radial-gradient(circle at 50% 0%, #1a224d 0%, #080b2a 100%); color: #ffffff; padding: 0; font-family: 'Inter', Arial, sans-serif; width: 600px; margin: 20px auto; border: 1px solid rgba(0, 247, 255, 0.25); border-radius: 12px; overflow: hidden; box-shadow: 0 30px 60px rgba(0,0,0,0.7);\">
            <div style=\"height: 4px; background: linear-gradient(90deg, transparent, #00f7ff, transparent);\"></div>
            
            <div style=\"padding: 30px;\">
                <div style=\"border-bottom: 1px solid rgba(0, 247, 255, 0.3); padding-bottom: 10px; margin-bottom: 15px;\">
                    <a href=\"https://felipemiramontesr.net\" style=\"color: #00f7ff !important; text-decoration: none !important; font-family: 'Outfit', sans-serif; font-size: 13px; font-weight: 500; letter-spacing: 2px;\">
                        felipemiramontesr&zwnj;.net
                    </a>
                    <span style=\"float: right; color: #7e8ec2; font-size: 9px; letter-spacing: 1.5px; font-family: Arial, sans-serif;\">DATA_STREAM // $currentDate</span>
                </div>

                <div style=\"background: rgba(255, 255, 255, 0.05); padding: 25px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1); text-align: center;\">
                    <div style=\"margin-bottom: 15px;\">
                        <span style=\"color: #7e8ec2; font-size: 9px; letter-spacing: 2px; text-transform: uppercase; font-family: Arial, sans-serif;\">🛡️ SECURITY_AUTHORIZATION_REQUIRED</span>
                    </div>
                    
                    <h2 style=\"color: #00f7ff; font-size: 11px; margin-bottom: 25px; letter-spacing: 1.5px; font-family: Arial, sans-serif; text-transform: lowercase; text-shadow: 0 0 10px rgba(0, 247, 255, 0.3);\">intent: external_transmission</h2>

                    <div style=\"background: #000; border: 1px solid #00f7ff; padding: 25px; border-radius: 8px; display: block; margin-bottom: 25px; box-shadow: 0 0 20px rgba(0, 247, 255, 0.2);\">
                        <div style=\"color: rgba(0, 247, 255, 0.4); font-size: 8px; margin-bottom: 15px; letter-spacing: 2px; text-align: left; font-family: Arial, sans-serif;\">// [ START_PIN ]</div>
                        <span style=\"font-size: 38px; font-weight: bold; letter-spacing: 15px; color: #00f7ff; text-shadow: 0 0 15px rgba(0, 247, 255, 0.6); padding-left: 15px; font-family: 'Courier New', monospace;\">$newPIN</span>
                        <div style=\"color: rgba(0, 247, 255, 0.4); font-size: 8px; margin-top: 15px; letter-spacing: 2px; text-align: left; font-family: Arial, sans-serif;\">// [ END_PIN ]</div>
                    </div>

                    <p style=\"color: #a0a0c0; font-size: 11px; line-height: 1.5; margin: 0; opacity: 0.8; font-family: 'Inter', Arial, sans-serif; text-transform: lowercase;\">
                        this sequence expires in 5 minutes. <br>
                        input the authorization code into the secure node to proceed.
                    </p>
                </div>

                <div style=\"margin-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 15px; display: table; width: 100%;\">
                    <div style=\"display: table-cell; vertical-align: middle;\">
                        <span style=\"color: #00f7ff; font-size: 9px; letter-spacing: 2px; font-weight: bold; font-family: Arial, sans-serif;\">🛡️ INTEGRITY_INDEX: NOMINAL</span>
                    </div>
                    <div style=\"display: table-cell; vertical-align: middle; text-align: right;\">
                        <div style=\"display: inline-block; width: 80px; height: 3px; background: linear-gradient(90deg, rgba(0,247,255,0.05) 0%, rgba(0,247,255,0.8) 50%, rgba(0,247,255,0.05) 100%); border-radius: 2px;\"></div>
                    </div>
                </div>
            </div>
        </div>";

        $mail->send();
        sendResponse(['status' => '2fa_required', 'message' => 'PIN dispatched to master email.']);
    } catch (Exception $e) {
        technicalLog("2FA Dispatch failed: " . $e->getMessage(), true);
        sendResponse(['error' => 'Security Shield Error: Could not dispatch 2FA PIN.'], 500);
    }
} else {
    // Validate PIN
    if (!file_exists($codeFile)) {
        sendResponse(['error' => 'No active PIN found.'], 403);
    }

    $stored = json_decode(file_get_contents($codeFile), true);
    if (time() > $stored['expires']) {
        @unlink($codeFile);
        sendResponse(['error' => 'PIN expired. Request new authorization.'], 403);
    }

    if ($authCode !== $stored['code']) {
        sendResponse(['error' => 'Invalid Security PIN.'], 403);
    }

    // Success: Consume code
    @unlink($codeFile);
}

// --- Operational Phase ---
if (!$action)
    sendResponse(['error' => 'Command missing'], 400);

$isMockGemini = (GEMINI_API_KEY === '__GEMINI_API_KEY__');
$isMockSMTP = (SMTP_PASS === '__SMTP_PASS__');

// 1. AI Logic
if ($action === 'polish' || $action === 'translate') {
    $prompt = $input['prompt'] ?? null;
    if (!$prompt)
        sendResponse(['error' => 'Logic parameters missing'], 400);

    if ($isMockGemini) {
        sendResponse(['result' => "[MOCKED] AI Logic applied to signal data. (Gemini offline)"]);
    }

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . GEMINI_API_KEY;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["contents" => [["parts" => [["text" => $prompt]]]]]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        technicalLog("Gemini API Error: $response", true);
        sendResponse(['error' => 'Cognitive service failure'], 502);
    }

    $data = json_decode($response, true);
    $result = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Data processing failure';
    sendResponse(['result' => $result]);
}

// 2. Transmission Logic
if ($action === 'send') {
    $recipient = $input['to_email'] ?? null;
    $subject = $input['subject'] ?? 'SIGNAL_TRANSMISSION';
    $messageBody = $input['body'] ?? null;

    if (!$recipient || !$messageBody) {
        technicalLog("Transmission aborted: Missing recipient or body. Action=$action", true);
        sendResponse(['error' => 'Payload incomplete'], 400);
    }

    technicalLog("COMMENCING_FINAL_TRANSMISSION: Recipient=" . substr($recipient, 0, 3) . "***" . strrchr($recipient, "@"));

    if ($isMockSMTP) {
        sendResponse(['success' => true, 'message' => 'MOCKED: Signal transmitted successfully.']);
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = (SMTP_SECURE === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(SMTP_USER, 'Felipe Miramontes (SECURE_NODE)');
        $mail->addAddress($recipient);
        $mail->addReplyTo(SMTP_USER, 'Felipe Miramontes');

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $messageBody;
        $mail->AltBody = strip_tags($messageBody);

        $mail->send();
        technicalLog("Successful transmission to $recipient");
        sendResponse(['success' => true, 'message' => 'Signal successfully transmitted via SECURE_NODE_V3']);
    } catch (Exception $e) {
        technicalLog("Transmission failed for $recipient: " . $mail->ErrorInfo, true);
        sendResponse(['error' => 'Transponder Failure: Could not reach destination.'], 500);
    }
}

/**
 * Stores an encrypted signal blob ephemerally
 */
function handleStoreSignal($data)
{
    if (!isset($data['blob']) || !isset($data['id'])) {
        sendResponse(['error' => 'MALFORMED_SIGNAL_PACKET'], 400);
    }

    $id = preg_replace('/[^a-zA-Z0-9_-]/', '', $data['id']);
    $file = __DIR__ . "/signals/sig_" . $id . ".dat";

    $payload = [
        'timestamp' => time(),
        'blob' => $data['blob'],
        'iv' => $data['iv'] ?? ''
    ];

    if (file_put_contents($file, json_encode($payload))) {
        technicalLog("SIGNAL_STORED: ID=$id");
        sendResponse(['status' => 'stored', 'id' => $id]);
    } else {
        technicalLog("STORAGE_FAILURE: ID=$id", true);
        sendResponse(['error' => 'STORAGE_FAILURE'], 500);
    }
}

/**
 * Fetches an encrypted signal for the recipient portal
 */
function handleFetchSignal($data)
{
    if (!isset($data['id']))
        sendResponse(['error' => 'MISSING_ID'], 400);

    $id = preg_replace('/[^a-zA-Z0-9_-]/', '', $data['id']);
    $file = __DIR__ . "/signals/sig_" . $id . ".dat";

    if (!file_exists($file)) {
        sendResponse(['error' => 'SIGNAL_NOT_FOUND_OR_SHREDDED'], 404);
    }

    $content = json_decode(file_get_contents($file), true);

    // TTL Check (24 hours)
    if (time() - $content['timestamp'] > 86400) {
        unlink($file);
        sendResponse(['error' => 'SIGNAL_EXPIRED'], 410);
    }

    sendResponse([
        'status' => 'retrieved',
        'blob' => $content['blob'],
        'iv' => $content['iv']
    ]);
}

/**
 * Surgically shreds a signal after successful client-side decryption
 */
function handleShredSignal($data)
{
    if (!isset($data['id']))
        sendResponse(['error' => 'MISSING_ID'], 400);

    $id = preg_replace('/[^a-zA-Z0-9_-]/', '', $data['id']);
    $file = __DIR__ . "/signals/sig_" . $id . ".dat";

    if (file_exists($file)) {
        unlink($file);
        technicalLog("SIGNAL_SHREDDED: ID=$id");
        sendResponse(['status' => 'shredded']);
    } else {
        sendResponse(['status' => 'already_gone']);
    }
}

sendResponse(['error' => 'Unknown protocol'], 404);