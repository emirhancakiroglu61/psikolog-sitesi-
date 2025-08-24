<?php
session_start();
require_once '../includes/config.php';

$token = $_GET['token'] ?? '';
$token = is_string($token) ? trim($token) : '';
$isTokenProvided = !empty($token) && ctype_xdigit($token) && strlen($token) === 64;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifreyi Yenile - Admin</title>
    <link rel="stylesheet" href="admin-styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container" style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
        <div class="card" style="max-width: 480px; width: 100%; margin: 0 auto; padding: 2.5rem 2rem;">
            <div style="text-align: center; margin-bottom: 2rem;">
                <img src="../img/logo1.png" alt="Psikolog Merkezi" style="height:120px; margin-bottom:1rem;">
                <h1 style="font-size: 1.8rem; font-weight: 800; background: linear-gradient(135deg, var(--white) 0%, var(--primary-purple-light) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Yeni Şifre Oluştur</h1>
                <?php if (!$isTokenProvided): ?>
                    <p style="color: var(--danger);">Geçersiz veya eksik token. Lütfen e-postanızdaki bağlantıyı kullanın.</p>
                <?php else: ?>
                    <p style="color: var(--gray-300);">Yeni şifrenizi belirleyin.</p>
                <?php endif; ?>
            </div>

            <div id="errorMessage" class="message error" style="display:none;"></div>
            <div id="successMessage" class="message success" style="display:none;"></div>

            <?php if ($isTokenProvided): ?>
            <form id="resetForm" method="post" autocomplete="off">
                <input type="hidden" id="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="form-group">
                    <label for="password">Yeni Şifre</label>
                    <input type="password" id="password" name="password" class="form-control" required autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Yeni Şifre (Tekrar)</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn" id="saveBtn" style="width: 100%; margin-top: 0.5rem;">
                    <span class="spinner" id="spinner" style="display:none; width:20px; height:20px; border:2px solid transparent; border-top:2px solid white; border-radius:50%; animation:spin 1s linear infinite; margin-right:0.5rem;"></span>
                    <span id="btnText">Şifreyi Güncelle</span>
                </button>
            </form>
            <?php endif; ?>

            <div style="text-align: center; margin-top: 2rem;">
                <a href="login.php" class="btn btn-secondary" style="padding:0.6rem 1.2rem; font-size:0.95rem;">← Girişe Dön</a>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('resetForm');
            if (!form) return;
            const token = document.getElementById('token').value;
            const pass = document.getElementById('password');
            const pass2 = document.getElementById('confirm_password');
            const saveBtn = document.getElementById('saveBtn');
            const spinner = document.getElementById('spinner');
            const btnText = document.getElementById('btnText');
            const errorDiv = document.getElementById('errorMessage');
            const successDiv = document.getElementById('successMessage');

            function setLoading(loading) {
                saveBtn.disabled = loading;
                spinner.style.display = loading ? 'inline-block' : 'none';
                btnText.textContent = loading ? 'Güncelleniyor...' : 'Şifreyi Güncelle';
            }

            function showError(message) {
                errorDiv.textContent = message;
                errorDiv.style.display = 'block';
                successDiv.style.display = 'none';
            }

            function showSuccess(message) {
                successDiv.textContent = message;
                successDiv.style.display = 'block';
                errorDiv.style.display = 'none';
            }

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const p1 = pass.value;
                const p2 = pass2.value;
                if (p1.length < 8 || !/[A-Z]/.test(p1) || !/[a-z]/.test(p1) || !/[0-9]/.test(p1)) {
                    showError('Şifre en az 8 karakter olmalı ve büyük/küçük harf ile rakam içermelidir.');
                    return;
                }
                if (p1 !== p2) {
                    showError('Şifreler eşleşmiyor.');
                    return;
                }
                setLoading(true);
                try {
                    const res = await fetch('handle_password_reset.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ token: token, password: p1 })
                    });
                    const text = await res.text();
                    let data;
                    try { data = JSON.parse(text); } catch (err) { throw new Error('Sunucu yanıtı işlenemedi'); }
                    if (res.ok && data.success) {
                        showSuccess('Şifreniz başarıyla güncellendi. Giriş sayfasına yönlendiriliyorsunuz...');
                        setTimeout(() => { window.location.href = 'login.php'; }, 2000);
                    } else {
                        showError(data.error || 'İşlem başarısız. Lütfen tekrar deneyin.');
                    }
                } catch (err) {
                    showError('Bağlantı hatası: ' + err.message);
                } finally {
                    setLoading(false);
                }
            });
        })();
    </script>
</body>
</html>

