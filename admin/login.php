<?php
session_start();
// Zaten giriş yapmışsa dashboard'a yönlendir
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi - Psikolog Merkezi</title>
    <link rel="stylesheet" href="admin-styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .password-input-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .password-toggle-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            transition: all 0.2s ease;
            z-index: 10;
        }
        
        .password-toggle-btn:hover {
            background: rgba(102, 126, 234, 0.1);
        }
        
        .password-toggle-btn:active {
            transform: translateY(-50%) scale(0.95);
        }
        
        .eye-icon {
            font-size: 16px;
            opacity: 0.7;
            transition: opacity 0.2s ease;
        }
        
        .password-toggle-btn:hover .eye-icon {
            opacity: 1;
        }
        
        .password-toggle-btn.showing .eye-icon {
            opacity: 1;
            color: #667eea;
        }
        
        .form-control[type="password"] {
            padding-right: 45px;
        }
        
        .form-control[type="text"] {
            padding-right: 45px;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .field-error {
            color: #EF4444;
            font-size: 0.85rem;
            margin-top: 0.25rem;
            display: block;
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 10;
            background: rgba(239, 68, 68, 0.1);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            border-left: 3px solid #EF4444;
        }
        
        .caps-lock-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #F59E0B;
            font-size: 0.85rem;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            border: 1px solid rgba(245, 158, 11, 0.3);
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2);
            animation: fadeIn 0.3s ease-in-out;
            font-weight: 500;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="admin-container" style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
        <div class="card" style="max-width: 420px; width: 100%; margin: 0 auto; padding: 2.5rem 2rem;">
            <div style="text-align: center; margin-bottom: 2rem;">
                <img src="../img/logo1.png" alt="Psikolog Merkezi" style="height:140px; margin-bottom:2rem;">
                <h1 style="font-size: 2.2rem; font-weight: 800; background: linear-gradient(135deg, var(--white) 0%, var(--primary-purple-light) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; text-shadow: 0 0 30px rgba(139, 92, 246, 0.3);">🔐 Admin Girişi</h1>
                <p style="color: var(--gray-300); font-size: 1.1rem;">Psikolog Merkezi Yönetim Paneli</p>
            </div>
            <div id="errorMessage" class="message error" style="display:none;"></div>
            <div id="successMessage" class="message success" style="display:none;"></div>
            <form id="loginForm" method="post" autocomplete="off">
                <div class="form-group">
                    <label for="email">📧 E-posta Adresi</label>
                    <input type="email" id="email" name="email" class="form-control" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="password">🔒 Şifre</label>
                    <div class="password-input-container">
                        <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
                        <button type="button" id="togglePassword" class="password-toggle-btn" title="Şifreyi göster/gizle">
                            <span class="eye-icon">👁️</span>
                        </button>
                    </div>
                    <div id="passwordStrength" style="margin-top:0.5rem; font-size:0.95rem;"></div>
                </div>
                <div class="form-group">
                    <!-- Google reCAPTCHA widget -->
                    <div class="g-recaptcha" data-sitekey="6LfET5wrAAAAAHWoKZDZy0gP1FX50V068Z4-XqZc"></div>
                </div>
                <button type="submit" class="btn" id="loginBtn" style="width: 100%; margin-top: 0.5rem;">
                    <span class="spinner" id="spinner" style="display:none; width:20px; height:20px; border:2px solid transparent; border-top:2px solid white; border-radius:50%; animation:spin 1s linear infinite; margin-right:0.5rem;"></span>
                    <span id="btnText">Giriş Yap</span>
                </button>
                <div style="text-align:center; margin-top: 1rem;">
                    <a href="forgot_password.php" style="color:#667eea; text-decoration:none; font-size:0.95rem;">Şifremi Unuttum?</a>
                </div>
            </form>
            <div class="security-info" style="margin-top: 2rem;">
                <h4 style="margin:0 0 0.5rem 0; font-size:0.95rem; color:var(--info);">🛡️ Güvenlik Özellikleri</h4>
                <ul style="color:var(--info); font-size:0.9rem;">
                    <li>Rate limiting (5 dakikada max 5 deneme)</li>
                    <li>SQL injection koruması</li>
                    <li>Session fixation koruması</li>
                    <li>CSRF token koruması</li>
                    <li>Güvenli şifre hash'leme</li>
                </ul>
            </div>
            <div style="text-align: center; margin-top: 2rem;">
                <a href="../index.php" class="btn btn-secondary" style="padding:0.75rem 1.5rem; font-size:0.98rem;">← Ana Sayfaya Dön</a>
            </div>
        </div>
    </div>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        class SecureLogin {
            constructor() {
                this.form = document.getElementById('loginForm');
                this.emailInput = document.getElementById('email');
                this.passwordInput = document.getElementById('password');
                this.loginBtn = document.getElementById('loginBtn');
                this.spinner = document.getElementById('spinner');
                this.btnText = document.getElementById('btnText');
                this.errorDiv = document.getElementById('errorMessage');
                this.successDiv = document.getElementById('successMessage');
                this.init();
            }
            init() {
                this.form.addEventListener('submit', (e) => this.handleSubmit(e));
                this.setupInputValidation();
                this.setupSecurityFeatures();
                this.setupPasswordToggle();
            }
            setupInputValidation() {
                const inputs = [this.emailInput, this.passwordInput];
                inputs.forEach(input => {
                    input.addEventListener('input', () => {
                        this.clearError();
                        this.clearFieldError(input);
                    });
                    input.addEventListener('blur', () => this.validateField(input));
                });
            }
            setupSecurityFeatures() {
                this.emailInput.addEventListener('input', (e) => {
                    e.target.value = this.sanitizeInput(e.target.value);
                });
                this.passwordInput.addEventListener('input', (e) => {
                    this.checkPasswordStrength(e.target.value);
                });
                this.setupCapsLockWarning();
                this.checkSessionTimeout();
            }
            sanitizeInput(input) {
                return input.replace(/[<>]/g, '');
            }
            validateField(field) {
                const value = field.value.trim();
                if (field.hasAttribute('required') && !value) {
                    this.showFieldError(field, 'Bu alan zorunludur');
                    return false;
                }
                if (field.type === 'email' && value && !this.isValidEmail(value)) {
                    this.showFieldError(field, 'Geçerli bir email adresi giriniz');
                    return false;
                }
                this.clearFieldError(field);
                return true;
            }
            isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }
            showFieldError(field, message) {
                field.classList.add('error');
                const errorDiv = field.parentNode.querySelector('.field-error') || 
                               document.createElement('div');
                errorDiv.className = 'field-error';
                errorDiv.textContent = message;
                if (!field.parentNode.querySelector('.field-error')) {
                    field.parentNode.appendChild(errorDiv);
                }
            }
            clearFieldError(field) {
                field.classList.remove('error');
                const errorDiv = field.parentNode.querySelector('.field-error');
                if (errorDiv) {
                    errorDiv.remove();
                }
            }
            checkPasswordStrength(password) {
                const strength = this.calculatePasswordStrength(password);
                const feedback = document.getElementById('passwordStrength');
                if (!feedback) return;
                if (password.length === 0) {
                    feedback.textContent = '';
                    return;
                }
                if (password.length >= 8 && /[a-z]/.test(password) && /[A-Z]/.test(password) && /[0-9]/.test(password)) {
                    feedback.textContent = 'Şifre güçlü ✔️';
                    feedback.style.color = '#10B981'; // yeşil
                } else {
                    feedback.textContent = 'Şifreniz güvenlik açısından zayıf! En az 8 karakter, büyük/küçük harf ve rakam içermelidir.';
                    feedback.style.color = '#EF4444'; // kırmızı
                }
            }
            calculatePasswordStrength(password) {
                let strength = 0;
                if (password.length >= 8) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^A-Za-z0-9]/.test(password)) strength++;
                return strength;
            }
            checkSessionTimeout() {
                setTimeout(() => {
                    if (confirm('Güvenlik nedeniyle oturumunuz sonlandırılacak. Devam etmek istiyor musunuz?')) {
                        this.refreshSession();
                    } else {
                        window.location.href = 'logout.php';
                    }
                }, 30 * 60 * 1000);
            }
            async refreshSession() {
                try {
                    const response = await fetch('auth_api.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'refresh_session'
                        })
                    });
                    if (!response.ok) {
                        window.location.href = 'logout.php';
                    }
                } catch (error) {
                    console.error('Session refresh failed:', error);
                }
            }
            async handleSubmit(e) {
                e.preventDefault();
                if (!this.validateForm()) {
                    return;
                }
                this.setLoading(true);
                this.clearError();
                try {
                    const formData = {
                        email: this.emailInput.value.trim(),
                        password: this.passwordInput.value,
                        recaptcha: grecaptcha.getResponse()
                    };
                    const response = await fetch('auth_api.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(formData)
                    });
                    let data;
                    const responseText = await response.text();
                    try {
                        data = JSON.parse(responseText);
                    } catch (jsonError) {
                        console.error('JSON parse error:', jsonError);
                        console.error('Response text:', responseText);
                        throw new Error('Sunucu yanıtı işlenemedi');
                    }
                    if (response.ok && data.success) {
                        this.showSuccess('Giriş başarılı! Yönlendiriliyorsunuz...');
                        if (data.csrf_token) {
                            localStorage.setItem('csrf_token', data.csrf_token);
                        }
                        setTimeout(() => {
                            window.location.href = data.redirect || 'dashboard.php';
                        }, 2000);
                    } else {
                        this.showError(data.error || 'Giriş başarısız. Lütfen bilgilerinizi kontrol edin.');
                        this.passwordInput.value = '';
                        this.passwordInput.focus();
                    }
                } catch (error) {
                    console.error('Login error:', error);
                    console.error('Error details:', error.message);
                    this.showError('Bağlantı hatası. Lütfen tekrar deneyin. Hata: ' + error.message);
                } finally {
                    this.setLoading(false);
                }
            }
            validateForm() {
                let isValid = true;
                if (!this.validateField(this.emailInput)) {
                    isValid = false;
                }
                if (!this.validateField(this.passwordInput)) {
                    isValid = false;
                }
                return isValid;
            }
            setLoading(loading) {
                this.loginBtn.disabled = loading;
                this.spinner.style.display = loading ? 'inline-block' : 'none';
                this.btnText.textContent = loading ? 'Giriş Yapılıyor...' : 'Giriş Yap';
            }
            showError(message) {
                this.errorDiv.textContent = message;
                this.errorDiv.style.display = 'block';
                this.successDiv.style.display = 'none';
            }
            showSuccess(message) {
                this.successDiv.textContent = message;
                this.successDiv.style.display = 'block';
                this.errorDiv.style.display = 'none';
            }
            clearError() {
                this.errorDiv.style.display = 'none';
                this.successDiv.style.display = 'none';
            }
            
            setupPasswordToggle() {
                const toggleBtn = document.getElementById('togglePassword');
                const passwordInput = this.passwordInput;
                
                toggleBtn.addEventListener('click', () => {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    // Buton durumunu güncelle
                    if (type === 'text') {
                        toggleBtn.classList.add('showing');
                        toggleBtn.querySelector('.eye-icon').textContent = '🙈';
                        toggleBtn.title = 'Şifreyi gizle';
                    } else {
                        toggleBtn.classList.remove('showing');
                        toggleBtn.querySelector('.eye-icon').textContent = '👁️';
                        toggleBtn.title = 'Şifreyi göster';
                    }
                });
                
                // Klavye kısayolu (Ctrl+Shift+P)
                document.addEventListener('keydown', (e) => {
                    if (e.ctrlKey && e.shiftKey && e.key === 'P') {
                        e.preventDefault();
                        toggleBtn.click();
                    }
                });
            }
            
            setupCapsLockWarning() {
                const inputs = [this.emailInput, this.passwordInput];
                const capsLockWarning = document.createElement('div');
                capsLockWarning.id = 'capsLockWarning';
                capsLockWarning.className = 'caps-lock-warning';
                capsLockWarning.style.display = 'none';
                capsLockWarning.innerHTML = '⚠️ Caps Lock açık!';
                
                // Uyarıyı geçici olarak sayfaya ekle (sonra dinamik olarak taşınacak)
                document.body.appendChild(capsLockWarning);
                
                // Her input için Caps Lock kontrolü
                inputs.forEach(input => {
                    input.addEventListener('keydown', (e) => {
                        this.checkCapsLock(e, capsLockWarning, input);
                    });
                    
                    input.addEventListener('keyup', (e) => {
                        this.checkCapsLock(e, capsLockWarning, input);
                    });
                    
                    input.addEventListener('blur', () => {
                        capsLockWarning.style.display = 'none';
                    });
                });
            }
            
            checkCapsLock(e, warningElement, inputElement) {
                // Caps Lock durumunu kontrol et
                const isCapsLockOn = e.getModifierState('CapsLock');
                
                if (isCapsLockOn) {
                    // Uyarıyı form-group içine taşı
                    const formGroup = inputElement.closest('.form-group');
                    
                    // Eğer uyarı başka bir yerdeyse, form-group'a taşı
                    if (warningElement.parentElement !== formGroup) {
                        formGroup.appendChild(warningElement);
                    }
                    
                    warningElement.style.display = 'block';
                    warningElement.style.position = 'absolute';
                    warningElement.style.top = '100%';
                    warningElement.style.left = '0px';
                    warningElement.style.zIndex = '1000';
                    warningElement.style.width = '100%';
                    warningElement.style.marginTop = '5px';
                } else {
                    warningElement.style.display = 'none';
                }
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            new SecureLogin();
        });
        window.addEventListener('beforeunload', () => {
            if (typeof navigator.sendBeacon === 'function') {
                navigator.sendBeacon('logout.php');
            }
        });
    </script>
</body>
</html> 