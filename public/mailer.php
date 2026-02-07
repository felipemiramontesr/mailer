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
$submitted_pass = $input['password'] ?? '';
$auth_code_input = $input['auth_code'] ?? '';

// --- BLOQUE DE SEGURIDAD 2FA ---
define('SECURE_PATH', true);
require_once 'access_hash.php';

if (!password_verify($submitted_pass, MAILER_PASSWORD_HASH)) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Contraseña incorrecta o no proporcionada.']);
    exit;
}

// Si la contraseña es correcta, verificamos el 2FA
$code_file = __DIR__ . '/auth_codes/current_code.json';

if (empty($auth_code_input)) {
    // Paso 1: Generar y enviar código
    $new_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiry = time() + AUTH_CODE_EXPIRATION;

    file_put_contents($code_file, json_encode(['code' => $new_code, 'expires' => $expiry]));

    // Reutilizamos PHPMailer para mandarte el código a TI (Email Maestro)
    require 'PHPMailer/Exception.php';
    require 'PHPMailer/PHPMailer.php';
    require 'PHPMailer/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = (SMTP_SECURE === 'ssl') ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom(SMTP_USER, 'Seguridad felipemiramontesr.net');
        $mail->addAddress(MASTER_AUTH_EMAIL);
        $mail->Subject = "AUTORIZACIÓN REQUERIDA: $new_code";

        $html_body = "
        <div style=\"background-color: #080b1a; color: #ffffff; padding: 20px; font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #00f7ff33; border-radius: 8px;\">
            <div style=\"border-bottom: 1px solid rgba(0, 247, 255, 0.2); padding-bottom: 10px; margin-bottom: 20px;\">
                <span style=\"color: #00f7ff; font-size: 14px; letter-spacing: 2px;\">SECURITY PROTOCOL v9.1</span>
            </div>
            
            <div style=\"background: rgba(17, 22, 51, 0.6); padding: 30px; border-radius: 12px; border: 1px solid rgba(0, 247, 255, 0.1); text-align: center;\">
                <p style=\"color: #5b6ea3; font-size: 10px; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px;\">Verification PIN Requested</p>
                
                <div style=\"display: inline-block; padding: 15px 40px; background: rgba(0, 247, 255, 0.05); border: 2px solid #00f7ff; border-radius: 4px; box-shadow: 0 0 20px rgba(0, 247, 255, 0.2);\">
                    <span style=\"font-size: 32px; font-weight: bold; color: #00f7ff; letter-spacing: 8px; text-shadow: 0 0 10px rgba(0, 247, 255, 0.5);\">$new_code</span>
                </div>
                
                <p style=\"color: #cbd5e1; font-size: 12px; margin-top: 25px; line-height: 1.6;\">
                    Se ha detectado un intento de acceso al Mailer.<br>
                    Introduce este PIN en la consola de control para autorizar la transmisión.
                </p>
            </div>
            
            <div style=\"margin-top: 20px; text-align: right;\">
                <p style=\"color: #00f7ff; font-size: 8px; opacity: 0.6;\">
                    EXPIRES IN: 05:00 MIN | SYSTEM INTEGRITY: VERIFIED
                </p>
            </div>
        </div>";

        $mail->Body = $html_body;
        $mail->AltBody = "Tu código de seguridad es: $new_code (Expira en 5 minutos)";
        $mail->isHTML(true);
        $mail->send();

        echo json_encode(['status' => '2fa_required', 'message' => 'Código enviado a tu email maestro.']);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error al enviar código 2FA.', 'details' => $mail->ErrorInfo]);
    }
    exit;
} else {
    // Paso 2: Validar el código enviado
    if (!file_exists($code_file)) {
        echo json_encode(['error' => 'No hay un código pendiente o ha expirado.']);
        exit;
    }

    $stored_data = json_decode(file_get_contents($code_file), true);
    if (time() > $stored_data['expires']) {
        unlink($code_file);
        echo json_encode(['error' => 'El código ha expirado. Por favor, solicita uno nuevo.']);
        exit;
    }

    if ($auth_code_input !== $stored_data['code']) {
        echo json_encode(['error' => 'Código 2FA incorrecto.']);
        exit;
    }

    // Código correcto! Lo borramos para que no se use dos veces y seguimos
    unlink($code_file);
}
// --- FIN BLOQUE DE SEGURIDAD ---

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

    // --- V2.6: SMTP Authentication via PHPMailer ---
    require 'PHPMailer/Exception.php';
    require 'PHPMailer/PHPMailer.php';
    require 'PHPMailer/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = (SMTP_SECURE === 'ssl') ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        // Custom Trust Headers
        $mail->Priority = 3; // Normal
        $mail->addCustomHeader('X-Priority', '3');
        $mail->addCustomHeader('X-Mailer', 'Professional Mail Connector');

        // Recipients
        $mail->setFrom(SMTP_USER, 'B. Eng. Felipe de Jesús Miramontes Romero');
        $mail->addAddress($to_email);
        $mail->addReplyTo(SMTP_USER, 'B. Eng. Felipe de Jesús Miramontes Romero');

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);

        // Diagnostic Logging
        $log_entry = "[" . date("Y-m-d H:i:s") . "] To: $to_email | Subject: $subject | Size: " . strlen($body) . " bytes\n";
        file_put_contents(__DIR__ . "/mailer_debug.log", $log_entry, FILE_APPEND);

        $mail->send();
        echo json_encode(['success' => true, 'message' => 'Email sent via Secure Node (Authenticated V2.6)']);
    } catch (Exception $e) {
        file_put_contents(__DIR__ . "/mailer_debug.log", "FAILED: " . $mail->ErrorInfo . "\n", FILE_APPEND);
        echo json_encode(['error' => 'SMTP Authentication failed. Check credentials in config.php. Details recorded in log.']);
    }
    exit;
}
?>