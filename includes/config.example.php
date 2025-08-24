<?php
// Veritabanı bağlantı konfigürasyonu
define('DB_HOST', 'localhost');
define('DB_NAME', 'psikolog_merkezi');
define('DB_USER', 'root');
define('DB_PASS', '');

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session başlat (eğer zaten başlatılmamışsa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Veritabanı bağlantısı
function getDBConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        error_log(date('[Y-m-d H:i:s] ') . 'Veritabanı bağlantı hatası: ' . $e->getMessage() . "\n", 3, MAIL_LOG);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Veritabanı bağlantı hatası: ' . $e->getMessage(),
            'server' => DB_HOST,
            'database' => DB_NAME,
            'user' => DB_USER
        ]);
        exit;
    }
}

// Mail ayarları
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_USERNAME', 'your-email@gmail.com');
define('MAIL_PASSWORD', 'your-app-password');
define('MAIL_FROM', 'your-email@gmail.com');
define('MAIL_FROM_NAME', 'Uzman Psikoloji Merkezi');
define('MAIL_PORT', 587);
define('MAIL_SECURE', 'tls');
define('MAIL_LOG', __DIR__ . '/../mail-error.log');

// Anti-spam ayarları
define('MAIL_DOMAIN', 'psikologmerkezi.com');
define('MAIL_REPLY_TO', 'info@psikologmerkezi.com');

// Güvenlik fonksiyonları
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Admin kontrolü
function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: admin/login.php');
        exit();
    }
}

// PHPMailer ile SMTP üzerinden mail gönderme fonksiyonu
function sendMailSMTP($to, $subject, $body, $isHtml = false, $replyTo = null) {
    require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
    require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';
    require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        error_log(date('[Y-m-d H:i:s] ') . "Geçersiz e-posta adresi: $to\n", 3, MAIL_LOG);
        return 'Geçersiz e-posta adresi';
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_SECURE;
        $mail->Port = MAIL_PORT;
        
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        $mail->Timeout = 30;
        $mail->SMTPKeepAlive = true;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        
        $mail->addCustomHeader('X-Mailer', 'Uzman Psikoloji Merkezi Mail System');
        $mail->addCustomHeader('X-Priority', '3');
        $mail->addCustomHeader('X-MSMail-Priority', 'Normal');
        $mail->addCustomHeader('Importance', 'Normal');
        $mail->addCustomHeader('X-Report-Abuse', 'Please report abuse here: abuse@psikologmerkezi.com');
        $mail->addCustomHeader('List-Unsubscribe', '<mailto:unsubscribe@psikologmerkezi.com>');
        $mail->addCustomHeader('Precedence', 'bulk');

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($to);
        
        if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo($replyTo);
        } else {
            $mail->addReplyTo(MAIL_REPLY_TO, MAIL_FROM_NAME);
        }

        $mail->isHTML($isHtml);
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        if ($isHtml) {
            $mail->AltBody = strip_tags($body);
        }

        $mail->send();
        return true;
    } catch (PHPMailer\PHPMailer\Exception $e) {
        $errorMsg = date('[Y-m-d H:i:s] ') . "Mail gönderilemedi: {$mail->ErrorInfo} (To: $to, Subject: $subject)\n";
        error_log($errorMsg, 3, MAIL_LOG);
        return $mail->ErrorInfo;
    }
}

// Anti-spam için mail içeriğini optimize eden fonksiyon
function optimizeMailContent($body, $isHtml = false) {
    if ($isHtml) {
        $htmlHeader = '<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Psikolog Merkezi</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #667eea; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f8f9fa; }
        .footer { background: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d; }
        .unsubscribe { color: #6c757d; font-size: 11px; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Psikolog Merkezi</h1>
        </div>
        <div class="content">';
        
        $htmlFooter = '</div>
        <div class="footer">
            <p>Bu e-posta Psikolog Merkezi tarafından gönderilmiştir.</p>
            <p>Adres: İstanbul, Türkiye | Tel: +90 (212) 555 0123</p>
            <p><a href="mailto:unsubscribe@psikologmerkezi.com" class="unsubscribe">E-posta listesinden çık</a></p>
        </div>
    </div>
</body>
</html>';
        
        return $htmlHeader . nl2br($body) . $htmlFooter;
    } else {
        $textHeader = "Uzman Psikoloji Merkezi\n";
        $textHeader .= "İstanbul, Türkiye\n";
        $textHeader .= "Tel: +90 (212) 555 0123\n";
        $textHeader .= "E-posta: info@psikologmerkezi.com\n";
        $textHeader .= str_repeat("=", 50) . "\n\n";
        
        $textFooter = "\n\n" . str_repeat("=", 50) . "\n";
        $textFooter .= "Bu e-posta Uzman Psikoloji Merkezi tarafından gönderilmiştir.\n";
        $textFooter .= "E-posta listesinden çıkmak için: unsubscribe@psikologmerkezi.com\n";
        $textFooter .= "Abuse raporlamak için: abuse@psikologmerkezi.com\n";
        
        return $textHeader . $body . $textFooter;
    }
}

// Güvenlik header'ları
if (php_sapi_name() !== 'cli') {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: no-referrer-when-downgrade');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
}
?>


