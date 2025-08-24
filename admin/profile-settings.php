<?php
session_start();
require_once '../includes/config.php';
require_once 'csrf_middleware.php';

// Admin girişi kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Session timeout kontrolü (30 dakika)
$session_timeout = 30 * 60; // 30 dakika
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $session_timeout) {
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}

// Session'ı yenile
$_SESSION['login_time'] = time();

$pdo = getDBConnection();

// Profil güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCSRFToken();
    
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Validasyon
    if (empty($first_name)) $errors[] = 'Ad alanı zorunludur.';
    if (empty($last_name)) $errors[] = 'Soyad alanı zorunludur.';
    if (empty($email)) $errors[] = 'E-posta alanı zorunludur.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Geçerli bir e-posta adresi giriniz.';
    
    // E-posta benzersizlik kontrolü
    if ($email !== $_SESSION['admin_email']) {
        $stmt = $pdo->prepare("SELECT id FROM admin WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['admin_id']]);
        if ($stmt->fetch()) {
            $errors[] = 'Bu e-posta adresi zaten kullanılıyor.';
        }
    }
    
    // Şifre değişikliği varsa
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = 'Mevcut şifrenizi girmelisiniz.';
        } else {
            // Mevcut şifre kontrolü (hash ile)
            $stmt = $pdo->prepare("SELECT password FROM admin WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $admin = $stmt->fetch();
            if (!password_verify($current_password, $admin['password'])) {
                // Eski düz metin şifreyle giriş yapılırsa, otomatik hash'le ve güncelle
                if ($current_password === $admin['password']) {
                    $hashed = password_hash($current_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE admin SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed, $_SESSION['admin_id']]);
                } else {
                    $errors[] = 'Mevcut şifreniz hatalı.';
                }
            }
        }
        
        // Parola politikası kontrolü (benim eklediğim satırı kaldırıyorum)
        if ($new_password !== $confirm_password) {
            $errors[] = 'Yeni şifreler eşleşmiyor.';
        }
    }
    
    // Hata yoksa güncelle
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Temel bilgileri güncelle
            $stmt = $pdo->prepare("UPDATE admin SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
            $stmt->execute([$first_name, $last_name, $email, $_SESSION['admin_id']]);
            
            // Şifre değişikliği varsa
            if (!empty($new_password)) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admin SET password = ? WHERE id = ?");
                $stmt->execute([$hashed, $_SESSION['admin_id']]);
            }
            
            $pdo->commit();
            
            // Session'ı güncelle
            $_SESSION['admin_first_name'] = $first_name;
            $_SESSION['admin_last_name'] = $last_name;
            $_SESSION['admin_email'] = $email;
            
            $success = 'Profil bilgileriniz başarıyla güncellendi.';
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Güncelleme sırasında bir hata oluştu.';
        }
    }
}

// Mevcut admin bilgilerini getir
$stmt = $pdo->prepare("SELECT first_name, last_name, email, last_login FROM admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin_info = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Ayarları - Admin Panel</title>
    <link rel="stylesheet" href="admin-styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <div class="container" style="max-width: 700px; margin: 0 auto;">
            <div class="admin-header" style="margin-bottom: 2rem;">
                <div class="admin-header-content">
                    <h1>⚙️ Profil Ayarları</h1>
                </div>
            </div>
            <div class="card" style="padding: 2.5rem 2rem;">
                <?php if (isset($success)): ?>
                <div class="message success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                <div class="message error">
                    <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <form method="POST" id="profileForm" autocomplete="off">
                    <?php echo getCSRFTokenInput(); ?>
                    <div class="form-section">
                        <h3>👤 Kişisel Bilgiler</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="first_name">Ad *</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($admin_info['first_name']); ?>" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="last_name">Soyad *</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($admin_info['last_name']); ?>" required class="form-control">
                            </div>
                            <div class="form-group full-width">
                                <label for="email">E-posta Adresi *</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin_info['email']); ?>" required class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <div class="password-section">
                            <h3>🔒 Şifre Değiştir</h3>
                            <p style="color: var(--gray-300); margin-bottom: 1.5rem;">Şifrenizi değiştirmek istemiyorsanız bu alanları boş bırakın.</p>
                            <div class="form-group">
                                <label for="current_password">Mevcut Şifre</label>
                                <input type="password" id="current_password" name="current_password" autocomplete="off" class="form-control">
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="new_password">Yeni Şifre</label>
                                    <input type="password" id="new_password" name="new_password" minlength="8" autocomplete="off" class="form-control">
                                    <div id="passwordStrength" style="margin-top:0.5rem; font-size:0.95rem;"></div>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Yeni Şifre (Tekrar)</label>
                                    <input type="password" id="confirm_password" name="confirm_password" minlength="8" autocomplete="off" class="form-control">
                                    <div id="passwordMatch" style="margin-top:0.5rem; font-size:0.95rem;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                        <a href="dashboard.php" class="btn btn-secondary">İptal</a>
                        <button type="button" class="btn btn-primary" id="saveProfileBtn">Değişiklikleri Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Şifre Onay Modalı -->
    <div id="passwordConfirmModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:var(--black-soft,#1E293B); color:#fff; border-radius:16px; max-width:350px; width:90%; padding:2rem; box-shadow:0 8px 32px rgba(0,0,0,0.2); margin:auto;">
            <h3 style="margin-bottom:1.5rem; text-align:center;">Yeni Şifreyi Onayla</h3>
            <p style="margin-bottom:1rem; text-align:center; color:var(--gray-300);">Yeni şifreniz aşağıda gösterilmektedir. Onaylıyor musunuz?</p>
            <div id="modalNewPassword" style="background:rgba(255,255,255,0.08); color:#fff; font-size:1.1rem; padding:0.75rem 1rem; border-radius:8px; margin-bottom:1.5rem; text-align:center; letter-spacing:2px;"></div>
            <div style="display:flex; gap:1rem; justify-content:center;">
                <button class="btn btn-secondary" type="button" id="cancelModalBtn">İptal</button>
                <button class="btn btn-primary" type="button" id="confirmModalBtn">Onayla ve Kaydet</button>
            </div>
        </div>
    </div>
    <script>
    // Modal açma ve form submit engelleme
    document.getElementById('saveProfileBtn').addEventListener('click', function(e) {
        var newPassword = document.getElementById('new_password').value;
        if (newPassword) {
            document.getElementById('modalNewPassword').textContent = newPassword;
            document.getElementById('passwordConfirmModal').style.display = 'flex';
        } else {
            document.getElementById('profileForm').submit();
        }
    });
    document.getElementById('cancelModalBtn').onclick = function() {
        document.getElementById('passwordConfirmModal').style.display = 'none';
    };
    document.getElementById('confirmModalBtn').onclick = function() {
        document.getElementById('passwordConfirmModal').style.display = 'none';
        document.getElementById('profileForm').submit();
    };
    document.getElementById('passwordConfirmModal').addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });

    // Şifre güvenliği kontrolü ve buton kontrolü
    const newPasswordInput = document.getElementById('new_password');
    const saveBtn = document.getElementById('saveProfileBtn');
    function checkPasswordStrengthProfile(password) {
        const feedback = document.getElementById('passwordStrength');
        if (!feedback) return;
        if (password.length === 0) {
            feedback.textContent = '';
            saveBtn.disabled = false;
            return;
        }
        if (password.length >= 8 && /[a-z]/.test(password) && /[A-Z]/.test(password) && /[0-9]/.test(password)) {
            feedback.textContent = 'Şifre güçlü ✔️';
            feedback.style.color = '#10B981';
            saveBtn.disabled = false;
        } else {
            feedback.textContent = 'Şifreniz güvenlik açısından zayıf! En az 8 karakter, büyük/küçük harf ve rakam içermelidir.';
            feedback.style.color = '#EF4444';
            saveBtn.disabled = true;
        }
    }
    const confirmPasswordInput = document.getElementById('confirm_password');
    function checkPasswordMatch() {
        const matchFeedback = document.getElementById('passwordMatch');
        if (!matchFeedback) return;
        if (newPasswordInput.value.length === 0 && confirmPasswordInput.value.length === 0) {
            matchFeedback.textContent = '';
            return;
        }
        if (newPasswordInput.value !== confirmPasswordInput.value) {
            matchFeedback.textContent = 'Şifreler eşleşmiyor!';
            matchFeedback.style.color = '#EF4444';
            saveBtn.disabled = true;
        } else if (newPasswordInput.value.length > 0) {
            matchFeedback.textContent = 'Şifreler uyumlu ✔️';
            matchFeedback.style.color = '#10B981';
            // Şifre güçlü ise buton aktif, değilse zaten disable
            if (newPasswordInput.value.length >= 8 && /[a-z]/.test(newPasswordInput.value) && /[A-Z]/.test(newPasswordInput.value) && /[0-9]/.test(newPasswordInput.value)) {
                saveBtn.disabled = false;
            }
        } else {
            matchFeedback.textContent = '';
        }
    }
    newPasswordInput.addEventListener('input', function() {
        checkPasswordStrengthProfile(newPasswordInput.value);
        checkPasswordMatch();
    });
    confirmPasswordInput.addEventListener('input', function() {
        checkPasswordMatch();
    });
    // Sayfa yüklendiğinde ilk kontrol
    checkPasswordStrengthProfile(newPasswordInput.value);
    </script>
</body>
</html> 