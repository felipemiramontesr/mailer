<?php
/**
 * AI Secure Mailer - Core Engine v7.8.4 (Neuro-Enhanced)
 * Handles AI polishing, 2FA authorization, and SMTP transmission.
 * INCLUDING DEEP DIAGNOSTICS FOR 403 FORBIDDEN.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- Pre-flight Headers & Security ---
define('SECURE_PATH', true);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; connect-src 'self' https://generativelanguage.googleapis.com;");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("X-Security-Node: FELIPE-MIRAMONTES-B_ENG");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once 'config.php';
require_once 'access_hash.php';
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function rateLimitCheck($ip)
{
    $limitDir = __DIR__ . '/auth_codes/ratelimits';
    if (!is_dir($limitDir))
        mkdir($limitDir, 0700, true);
    $file = $limitDir . '/' . md5($ip) . '.json';
    $now = time();
    $capacity = 5;
    $refillRate = 1 / 60;
    $data = file_exists($file) ? json_decode(file_get_contents($file), true) : ['tokens' => $capacity, 'last' => $now];
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

function sendResponse($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function technicalLog($message, $isError = false)
{
    $logFile = __DIR__ . "/logs/mailer_" . date("Y-m-d") . ".log";
    if (!is_dir(__DIR__ . "/logs"))
        mkdir(__DIR__ . "/logs", 0755);
    $timestamp = date("Y-m-d H:i:s");
    $type = $isError ? "[ERROR]" : "[INFO]";
    file_put_contents($logFile, "$timestamp $type $message\n", FILE_APPEND);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input)
    sendResponse(['error' => 'Invalid JSON payload'], 400);

$action = $input['action'] ?? null;
$password = $input['password'] ?? '';
$authCode = $input['auth_code'] ?? '';

// Phase 1: Authentication
if (!in_array($action, ['fetch_signal', 'shred_signal'])) {
    if (!password_verify($password, MAILER_PASSWORD_HASH)) {
        technicalLog("Unauthorized access IP: " . $_SERVER['REMOTE_ADDR'], true);
        sendResponse(['error' => 'Access Denied. Invalid credentials.'], 401);
    }
}

// Action Routing (Signals)
if ($action === 'store_signal')
    handleStoreSignal($input);
if ($action === 'fetch_signal')
    handleFetchSignal($input);
if ($action === 'shred_signal')
    handleShredSignal($input);

// Phase 2: 2FA Bypass for AI
$ai_actions = ['polish', 'translate', 'command'];
$is_ai_action = in_array($action, $ai_actions);

if (!$is_ai_action && $action !== null && !in_array($action, ['fetch_signal', 'shred_signal'])) {
    $authDir = __DIR__ . '/auth_codes';
    $codeFile = $authDir . '/current_code.json';
    if (empty($authCode)) {
        if (!rateLimitCheck($_SERVER['REMOTE_ADDR']))
            sendResponse(['error' => 'Rate Limit exceeded.'], 429);
        $newPIN = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiry = time() + (5 * 60);
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
            $mail->setFrom(SMTP_USER, 'Felipe Miramontes (SECURE_NODE)');
            $mail->addAddress(MASTER_AUTH_EMAIL);
            $mail->Subject = "VERIFICATION PIN: $newPIN";
            $mail->isHTML(true);
            $mail->Body = "HUD 2FA PIN: <b>$newPIN</b>";
            $mail->send();
            sendResponse(['status' => '2fa_required']);
        } catch (Exception $e) {
            sendResponse(['error' => 'Security Shield Error.'], 500);
        }
    } else {
        if (!file_exists($codeFile))
            sendResponse(['error' => 'No active PIN.'], 403);
        $stored = json_decode(file_get_contents($codeFile), true);
        if (time() > $stored['expires'])
            sendResponse(['error' => 'PIN expired.'], 403);
        if ($authCode !== $stored['code'])
            sendResponse(['error' => 'Invalid PIN.'], 403);
        @unlink($codeFile);
    }
}

// Phase 3: Neuro-Engine (Gemini)
if (in_array($action, $ai_actions)) {
    $prompt = $input['prompt'] ?? null;
    $instruction = $input['instruction'] ?? null;
    if ($action !== 'command' && !$prompt)
        sendResponse(['error' => 'No data'], 400);

    $systemInstruction = "You are an Elite Security AI. Action: $action. Prompt: $prompt. Instruction: $instruction. Return ONLY the final text.";
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . GEMINI_API_KEY;
    $payload = ["contents" => [["parts" => [["text" => $systemInstruction . "\n\nTEXT:\n" . $prompt]]]]];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode !== 200) {
        $detail = ($httpCode === 0) ? "CURL_ERROR: $curlError" : "HTTP_$httpCode";
        // CRITICAL DIAGNOSTIC HOOK
        $keyPrefix = substr(GEMINI_API_KEY, 0, 4) . '...';
        technicalLog("Gemini API Failure: $detail | Response: $response | KeyPrefix: $keyPrefix", true);
        // FORCE HTTP 200 to avoid server error pages (Bad Gateway/Forbidden default pages)
        sendResponse(['error' => "Cognitive link unstable ($detail). Detail: " . $response], 200);
    }

    $data = json_decode($response, true);
    $result = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    if (!$result)
        sendResponse(['error' => 'Neuro-Engine parsing failed.'], 502);
    sendResponse(['result' => trim($result)]);
}

// Phase 4: Transmission
if ($action === 'send') {
    $recipient = $input['to_email'] ?? null;
    $subject = $input['subject'] ?? 'SIGNAL_TRANSMISSION';
    $messageBody = $input['body'] ?? null;
    if (!$recipient || !$messageBody)
        sendResponse(['error' => 'Payload incomplete'], 400);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = (SMTP_SECURE === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->setFrom(SMTP_USER, 'Felipe Miramontes (SECURE_NODE)');
        $mail->addAddress($recipient);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $messageBody;
        $mail->send();
        sendResponse(['success' => true]);
    } catch (Exception $e) {
        sendResponse(['error' => 'Transponder Failure.'], 500);
    }
}

// Helper Functions (Signals)
function handleStoreSignal($data)
{
    if (!isset($data['blob']) || !isset($data['id']))
        sendResponse(['error' => 'MALFORMED'], 400);
    $id = preg_replace('/[^a-zA-Z0-9_-]/', '', $data['id']);
    $file = __DIR__ . "/signals/sig_" . $id . ".dat";
    if (!is_dir(__DIR__ . "/signals"))
        mkdir(__DIR__ . "/signals", 0755);
    $payload = ['timestamp' => time(), 'blob' => $data['blob'], 'iv' => $data['iv'] ?? ''];
    file_put_contents($file, json_encode($payload));
    sendResponse(['status' => 'stored', 'id' => $id]);
}

function handleFetchSignal($data)
{
    $id = preg_replace('/[^a-zA-Z0-9_-]/', '', $data['id']);
    $file = __DIR__ . "/signals/sig_" . $id . ".dat";
    if (!file_exists($file))
        sendResponse(['error' => 'NOT_FOUND'], 404);
    $content = json_decode(file_get_contents($file), true);
    sendResponse(['status' => 'retrieved', 'blob' => $content['blob'], 'iv' => $content['iv']]);
}

function handleShredSignal($data)
{
    $id = preg_replace('/[^a-zA-Z0-9_-]/', '', $data['id']);
    $file = __DIR__ . "/signals/sig_" . $id . ".dat";
    if (file_exists($file))
        unlink($file);
    sendResponse(['status' => 'shredded']);
}

sendResponse(['error' => 'Unknown protocol'], 404);
