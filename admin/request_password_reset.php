<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    $email = trim($input['email'] ?? '');
    $recaptchaResponse = $input['recaptcha'] ?? '';

    if (empty($email)) {
        throw new Exception('E-posta gereklidir');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Geçerli bir e-posta adresi giriniz');
    }
    if (empty($recaptchaResponse)) {
        throw new Exception('Lütfen reCAPTCHA doğrulamasını tamamlayın.');
    }

    // reCAPTCHA doğrulaması
    $recaptchaSecret = '6LfET5wrAAAAAFCTHSuoWWq7L3U_LM3I4IGYqZHX';
    $verify = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptchaSecret . '&response=' . $recaptchaResponse);
    $captchaSuccess = json_decode($verify);
    if (!$captchaSuccess || empty($captchaSuccess->success)) {
        throw new Exception('reCAPTCHA doğrulaması başarısız.');
    }

    $pdo = getDBConnection();

    // password_resets tablosu yoksa oluştur
    $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        email VARCHAR(255) NOT NULL,
        token_hash CHAR(64) NOT NULL,
        ip VARCHAR(45) DEFAULT NULL,
        user_agent VARCHAR(255) DEFAULT NULL,
        expires_at DATETIME NOT NULL,
        used_at DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_token_hash (token_hash),
        INDEX idx_expires_at (expires_at),
        FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Admin kullanıcısını bul (varsa buna bağlayacağız, yoksa ilk aktif admin kullanılacak)
    $stmt = $pdo->prepare("SELECT id, email, first_name FROM admin WHERE email = ? AND active = 1 LIMIT 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        // İlk aktif admini fallback olarak kullan
        $fallbackStmt = $pdo->query("SELECT id, email, first_name FROM admin WHERE active = 1 ORDER BY id ASC LIMIT 1");
        $admin = $fallbackStmt->fetch(PDO::FETCH_ASSOC);
        if (!$admin) {
            throw new Exception('Aktif admin hesabı bulunamadı');
        }
    }

    // Token oluştur ve DB'ye kaydet
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 saat
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

    $ins = $pdo->prepare("INSERT INTO password_resets (admin_id, email, token_hash, ip, user_agent, expires_at) VALUES (?, ?, ?, ?, ?, ?)");
    $ins->execute([$admin['id'], $email, $tokenHash, $ip, $ua, $expiresAt]);

    // Sıfırlama linkini oluştur
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $resetUrl = $scheme . '://' . $host . $base . '/reset_password.php?token=' . urlencode($token);

    $firstName = 'Kullanıcı';
    $toEmail = $email;
    $subject = 'Admin Şifre Sıfırlama Bağlantısı';
    $body = '<p>Merhaba ' . htmlspecialchars($firstName) . ',</p>' .
            '<p>Şifrenizi sıfırlamak için aşağıdaki bağlantıya tıklayın. Bu bağlantı 1 saat boyunca geçerlidir.</p>' .
            '<p><a href="' . $resetUrl . '">Şifremi Sıfırla</a></p>' .
            '<p>Bağlantı çalışmazsa, şu adresi tarayıcınıza yapıştırın:</p>' .
            '<p>' . htmlspecialchars($resetUrl) . '</p>' .
            '<p>Bu işlemi siz başlatmadıysanız, bu e-postayı yok sayabilirsiniz.</p>';

    // Mail gönder (kayıtlı olmasa da)
    sendMailSMTP($toEmail, $subject, $body, true);

    echo json_encode(['success' => true, 'message' => 'Sıfırlama bağlantısı e-posta adresine gönderildi.']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>

