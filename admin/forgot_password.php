<?php
session_start();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Sıfırlama - Admin</title>
    <link rel="stylesheet" href="admin-styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container" style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
        <div class="card" style="max-width: 480px; width: 100%; margin: 0 auto; padding: 2.5rem 2rem;">
            <div style="text-align: center; margin-bottom: 2rem;">
                <img src="../img/logo1.png" alt="Psikolog Merkezi" style="height:120px; margin-bottom:1rem;">
                <h1 style="font-size: 1.8rem; font-weight: 800; background: linear-gradient(135deg, var(--white) 0%, var(--primary-purple-light) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Şifre Sıfırlama</h1>
                <p style="color: var(--gray-300);">E-posta adresinizi girin, size bir sıfırlama bağlantısı gönderelim.</p>
            </div>

            <div id="errorMessage" class="message error" style="display:none;"></div>
            <div id="successMessage" class="message success" style="display:none;"></div>

            <form id="forgotForm" method="post" autocomplete="off">
                <div class="form-group">
                    <label for="email">📧 E-posta Adresi</label>
                    <input type="email" id="email" name="email" class="form-control" required autocomplete="email">
                </div>
                <div class="form-group">
                    <div class="g-recaptcha" data-sitekey="6LfET5wrAAAAAHWoKZDZy0gP1FX50V068Z4-XqZc"></div>
                </div>
                <button type="submit" class="btn" id="sendBtn" style="width: 100%; margin-top: 0.5rem;">
                    <span class="spinner" id="spinner" style="display:none; width:20px; height:20px; border:2px solid transparent; border-top:2px solid white; border-radius:50%; animation:spin 1s linear infinite; margin-right:0.5rem;"></span>
                    <span id="btnText">Bağlantıyı Gönder</span>
                </button>
            </form>

            <div style="text-align: center; margin-top: 2rem;">
                <a href="login.php" class="btn btn-secondary" style="padding:0.6rem 1.2rem; font-size:0.95rem;">← Girişe Dön</a>
            </div>
        </div>
    </div>

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        (function() {
            const form = document.getElementById('forgotForm');
            const emailInput = document.getElementById('email');
            const sendBtn = document.getElementById('sendBtn');
            const spinner = document.getElementById('spinner');
            const btnText = document.getElementById('btnText');
            const errorDiv = document.getElementById('errorMessage');
            const successDiv = document.getElementById('successMessage');

            function setLoading(loading) {
                sendBtn.disabled = loading;
                spinner.style.display = loading ? 'inline-block' : 'none';
                btnText.textContent = loading ? 'Gönderiliyor...' : 'Bağlantıyı Gönder';
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
                const email = emailInput.value.trim();
                if (!email) {
                    showError('Lütfen e-posta adresinizi girin.');
                    return;
                }
                const recaptcha = grecaptcha.getResponse();
                if (!recaptcha) {
                    showError('Lütfen reCAPTCHA doğrulamasını tamamlayın.');
                    return;
                }
                setLoading(true);
                try {
                    const res = await fetch('request_password_reset.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ email, recaptcha })
                    });
                    const text = await res.text();
                    let data;
                    try { data = JSON.parse(text); } catch (err) { throw new Error('Sunucu yanıtı işlenemedi'); }
                    if (res.ok && data.success) {
                        showSuccess('Eğer e-posta adresi kayıtlıysa, sıfırlama bağlantısı gönderildi. Lütfen e-postanızı kontrol edin.');
                        form.reset();
                        grecaptcha.reset();
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

