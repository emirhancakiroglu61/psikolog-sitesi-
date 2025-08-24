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
    $token = trim($input['token'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($token) || !ctype_xdigit($token) || strlen($token) !== 64) {
        throw new Exception('Geçersiz token');
    }
    if (empty($password) || strlen($password) < 8) {
        throw new Exception('Geçersiz şifre');
    }
    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        throw new Exception('Şifre en az bir büyük harf, bir küçük harf ve bir rakam içermelidir');
    }

    $pdo = getDBConnection();

    $tokenHash = hash('sha256', $token);
    $stmt = $pdo->prepare("SELECT pr.id as reset_id, pr.admin_id, pr.email, pr.expires_at, pr.used_at, a.id as admin_id_real FROM password_resets pr JOIN admin a ON a.id = pr.admin_id WHERE pr.token_hash = ? LIMIT 1");
    $stmt->execute([$tokenHash]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        throw new Exception('Token bulunamadı veya geçersiz');
    }
    if (!empty($row['used_at'])) {
        throw new Exception('Token daha önce kullanılmış');
    }
    if (strtotime($row['expires_at']) < time()) {
        throw new Exception('Token süresi dolmuş');
    }

    // Şifreyi güncelle
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $upd = $pdo->prepare("UPDATE admin SET password = ? WHERE id = ?");
    $upd->execute([$hashed, $row['admin_id']]);

    // Token'i kullanılmış olarak işaretle ve aynı admin için diğer aktif tokenleri iptal et
    $pdo->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = ?")->execute([$row['reset_id']]);
    $pdo->prepare("UPDATE password_resets SET used_at = NOW() WHERE admin_id = ? AND used_at IS NULL")->execute([$row['admin_id']]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>

