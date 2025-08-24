<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/config.php';

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Rate limiting için basit kontrol
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_time = time();
$last_attempt = $_SESSION['login_attempts'] ?? 0;
$attempt_count = $_SESSION['attempt_count'] ?? 0;

function logSecurityEvent($event, $email = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $date = date('[Y-m-d H:i:s]');
    $log = "$date IP: $ip | $event";
    if ($email) $log .= " | Email: $email";
    error_log($log . "\n", 3, __DIR__ . '/../php-error.log');
}

// 5 dakika içinde 5'ten fazla deneme varsa engelle
if ($current_time - $last_attempt < 300 && $attempt_count >= 5) {
    logSecurityEvent('ÇOK FAZLA BAŞARISIZ GİRİŞ DENEMESİ', $input['email'] ?? null);
    // Admin e-posta uyarısı
    sendMailSMTP('emirhan678c@gmail.com', 'Çok Fazla Başarısız Giriş Denemesi',
        'IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\nEmail: " . ($input['email'] ?? '-') . "\nTarih: " . date('Y-m-d H:i:s'));
    http_response_code(429);
    echo json_encode(['error' => 'Too many login attempts. Please try again later.']);
    exit;
}

// 5 dakika geçtiyse sayacı sıfırla
if ($current_time - $last_attempt >= 300) {
    $_SESSION['attempt_count'] = 0;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    
    // Input validasyonu
    if (empty($email) || empty($password)) {
        throw new Exception('Email and password are required');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address');
    }
    
    if (strlen($email) > 255 || strlen($password) > 255) {
        throw new Exception('Invalid input length');
    }
    
    // reCAPTCHA doğrulaması
    $recaptchaSecret = '6LfET5wrAAAAAFCTHSuoWWq7L3U_LM3I4IGYqZHX';
    $recaptchaResponse = $input['recaptcha'] ?? '';
    if (empty($recaptchaResponse)) {
        throw new Exception('Lütfen reCAPTCHA doğrulamasını tamamlayın.');
    }
    $verify = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptchaSecret . '&response=' . $recaptchaResponse);
    $captchaSuccess = json_decode($verify);
    if (!$captchaSuccess->success) {
        throw new Exception('reCAPTCHA doğrulaması başarısız.');
    }
    
    // SQL injection koruması için prepared statement kullan
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id, username, first_name, last_name, email, password FROM admin WHERE email = ? AND active = 1 LIMIT 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Deneme sayısını artır
    $_SESSION['attempt_count'] = $attempt_count + 1;
    $_SESSION['login_attempts'] = $current_time;
    
    // Hatalı girişlerde logla
    if (!$admin) {
        logSecurityEvent('KULLANICI BULUNAMADI', $email);
        throw new Exception('Invalid email or password');
    }
    
    // Şifre doğrulama (hash ile)
    if (!password_verify($password, $admin['password'])) {
        logSecurityEvent('HATALI ŞİFRE', $email);
        // Eğer eski düz metin şifreyle giriş yapılırsa, otomatik hash'le ve güncelle
        if ($password === $admin['password']) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admin SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $admin['id']]);
        } else {
            throw new Exception('Invalid email or password');
        }
    }
    
    // Başarılı giriş - session'ı temizle
    unset($_SESSION['attempt_count']);
    unset($_SESSION['login_attempts']);
    
    // Last login'i güncelle
    $stmt = $pdo->prepare("UPDATE admin SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$admin['id']]);
    
    // Session'ı başlat
    session_regenerate_id(true); // Session fixation koruması
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_first_name'] = $admin['first_name'];
    $_SESSION['admin_last_name'] = $admin['last_name'];
    $_SESSION['login_time'] = time();
    
    // CSRF token oluştur
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    
    // 2FA kodu üret ve e-posta ile gönder
    $_SESSION['2fa_pending'] = true;
    $_SESSION['2fa_verified'] = false;
    $code = random_int(100000, 999999);
    $_SESSION['2fa_code'] = $code;
    $_SESSION['2fa_code_time'] = time();
    $subject = 'Güvenlik Kodu (2FA)';
    $body = "Girişinizi tamamlamak için doğrulama kodunuz: <b>$code</b>\n\nKod 10 dakika geçerlidir.";
    sendMailSMTP($admin['email'], $subject, $body, true);
    // Yanıt olarak 2FA gerektiğini bildir
    echo json_encode([
        'success' => true,
        '2fa_required' => true,
        'message' => 'Giriş başarılı, lütfen e-posta adresinize gelen doğrulama kodunu girin.'
    ]);
    exit;

// 2FA kod doğrulama endpointi
if (isset($input['action']) && $input['action'] === 'verify_2fa') {
    if (!isset($_SESSION['2fa_pending']) || !$_SESSION['2fa_pending']) {
        echo json_encode(['error' => '2FA oturumu yok.']);
        exit;
    }
    $userCode = $input['code'] ?? '';
    $realCode = $_SESSION['2fa_code'] ?? '';
    $codeTime = $_SESSION['2fa_code_time'] ?? 0;
    if (time() - $codeTime > 600) {
        unset($_SESSION['2fa_code'], $_SESSION['2fa_code_time'], $_SESSION['2fa_pending']);
        echo json_encode(['error' => 'Kodun süresi doldu.']);
        exit;
    }
    if ($userCode == $realCode) {
        $_SESSION['2fa_verified'] = true;
        unset($_SESSION['2fa_code'], $_SESSION['2fa_code_time'], $_SESSION['2fa_pending']);
        // Giriş tamamlandı, dashboard'a yönlendir
        echo json_encode(['success' => true, 'redirect' => 'dashboard.php']);
    } else {
        echo json_encode(['error' => 'Kod hatalı.']);
    }
    exit;
}
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'debug_info' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?> 