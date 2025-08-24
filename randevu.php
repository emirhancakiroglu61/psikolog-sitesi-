<?php
require_once 'includes/config.php';

$message = '';
$message_type = '';

// Form gönderildiğinde veya yönlendirme ile gelen mesajlar
if (isset($_GET['success']) && $_GET['success'] === 'appointment_sent') {
    $message = 'Randevu talebiniz başarıyla alındı. En kısa sürede size dönüş yapacağız.';
    $message_type = 'success';
} elseif (isset($_GET['error'])) {
    if ($_GET['error'] === 'missing_fields') {
        $message = 'Lütfen tüm zorunlu alanları doldurun.';
    } elseif ($_GET['error'] === 'validation_error' && isset($_GET['details'])) {
        $error_details = explode('|', $_GET['details']);
        $message = implode('<br>', $error_details);
    } elseif ($_GET['error'] === 'database_error') {
        $message = 'Bir hata oluştu. Lütfen tekrar deneyin.';
    } else {
        $message = 'Bir hata oluştu. Lütfen tekrar deneyin.';
    }
    $message_type = 'error';
}

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token kontrolü
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $message = 'Güvenlik hatası. Lütfen tekrar deneyin.';
        $message_type = 'error';
    } else {
        // Form verilerini temizle
        $ad = cleanInput($_POST['name']);
        $soyad = cleanInput($_POST['name']); // Assuming name field is for both ad and soyad
        $email = cleanInput($_POST['email']);
        $telefon = cleanInput($_POST['phone']);
        $dogum_tarihi = cleanInput($_POST['dogum_tarihi']); // This field is not in the new form, so it will be empty
        $cinsiyet = cleanInput($_POST['cinsiyet']); // This field is not in the new form, so it will be empty
        $randevu_tarihi = cleanInput($_POST['preferred_date']);
        $randevu_saati = cleanInput($_POST['appointment_time']);
        $mesaj = cleanInput($_POST['message']);
        
        // Validasyon
        $errors = [];
        
        if (empty($ad)) $errors[] = 'Ad alanı zorunludur.';
        if (empty($soyad)) $errors[] = 'Soyad alanı zorunludur.';
        if (empty($email)) $errors[] = 'E-posta alanı zorunludur.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Geçerli bir e-posta adresi giriniz.';
        if (empty($telefon)) $errors[] = 'Telefon alanı zorunludur.';
        if (empty($randevu_tarihi)) $errors[] = 'Randevu tarihi zorunludur.';
        if (empty($randevu_saati)) $errors[] = 'Randevu saati zorunludur.';
        if (empty($mesaj)) $errors[] = 'Mesaj alanı zorunludur.';
        
        // Tarih kontrolü
        $today = date('Y-m-d');
        if ($randevu_tarihi < $today) {
            $errors[] = 'Randevu tarihi geçmiş bir tarih olamaz.';
        }
        
        if (empty($errors)) {
            try {
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("INSERT INTO randevu (ad, soyad, email, telefon, dogum_tarihi, cinsiyet, randevu_tarihi, randevu_saati, mesaj) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$ad, $soyad, $email, $telefon, $dogum_tarihi, $cinsiyet, $randevu_tarihi, $randevu_saati, $mesaj]);
                
                $message = 'Randevu talebiniz başarıyla alındı. En kısa sürede size dönüş yapacağız.';
                $message_type = 'success';
                
                // Formu temizle
                $_POST = array();
            } catch (PDOException $e) {
                $message = 'Bir hata oluştu. Lütfen tekrar deneyin.';
                $message_type = 'error';
            }
        } else {
            $message = implode('<br>', $errors);
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Randevu Al - Psikolog Merkezi</title>
    <meta name="description" content="Psikolog Merkezi'nde randevu alın. Online veya yüz yüze terapi seansları için hemen başvurun.">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="logo"><img src="img/logo2.png" alt="Psikolog Merkezi" style="width:300px; height:auto; display:block; margin:0; padding:0;"></a>
            <div class="mobile-menu-toggle">
                <span></span><span></span><span></span>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Ana Sayfa</a></li>
                <li><a href="hakkimizda.php">Hakkımızda</a></li>
                <li><a href="blog.php">Blog</a></li>
                <li><a href="beslenme-diyetetik.php">Beslenme & Diyetetik</a></li>
                <li><a href="randevu.php" class="active">Randevu Al</a></li>
                <li><a href="index.php#iletisim">İletişim</a></li>
            </ul>
        </nav>
    </header>
    <!-- Hero Section -->
    <section class="hero hero-modern">
        <div class="container hero-content" style="display: flex; justify-content: center; align-items: center;">
            <div class="hero-text text-center" style="text-align: center; width: 100%;">
                <h1 style="text-align: center;">Randevu Al</h1>
                <p style="text-align: center;">Uzman psikologlarımızla görüşmek için hemen randevu alın</p>
            </div>
        </div>
    </section>
    <!-- Randevu Formu -->
    <section class="section">
        <div class="container" style="display: flex; flex-direction: column; align-items: center;">
            <div class="appointment-form-container" style="width: 100%; max-width: 600px;">
                <h2 class="section-title text-center">Randevu Formu</h2>
                <p style="text-align: center; color: #4A5568; margin-bottom: 1rem; font-size: 1.1rem;">
                    Aşağıdaki formu doldurarak randevu talebinizi iletebilirsiniz. En kısa sürede size dönüş yapacağız.
                </p>
                <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 8px; padding: 1rem; margin-bottom: 2rem; color: #3b82f6; font-size: 0.9rem;">
                    <strong>ℹ️ Önemli:</strong> Bilgileri doğru ve tam giriniz.
                </div>
                <?php if (!empty($message)): ?>
                    <div style="margin-bottom: 2rem; padding: 1.25rem 1.5rem; border-radius: 12px; font-weight: 500; border: 1px solid #e2e8f0; background: <?php echo $message_type === 'success' ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)'; ?>; color: <?php echo $message_type === 'success' ? '#059669' : '#dc2626'; ?>; text-align: center;">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                <form id="appointmentForm" class="appointment-form" action="process_appointment.php" method="POST" data-validate>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Ad Soyad *</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="national_id">TC Kimlik No *</label>
                            <input type="text" id="national_id" name="national_id" maxlength="11" pattern="[0-9]{11}" required placeholder="11 haneli TC kimlik numarası">
                            <div id="tc-feedback" style="font-size: 0.875rem; margin-top: 0.25rem; font-weight: 500;"></div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Telefon *</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label for="email">E-posta *</label>
                            <input type="email" id="email" name="email" required pattern="^[^\s@]+@[^\s@]+\.[^\s@]+$" autocomplete="email">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="preferred_date">Tercih Edilen Tarih *</label>
                            <input type="date" id="preferred_date" name="preferred_date" required>
                        </div>
                        <div class="form-group">
                            <label for="appointment_time">Saat Seçimi *</label>
                            <select id="appointment_time" name="appointment_time" required>
                                <option value="">Saat Seçiniz</option>
                                <option value="09:00">09:00 - 09:45</option>
                                <option value="10:00">10:00 - 10:45</option>
                                <option value="11:00">11:00 - 11:45</option>
                                <option value="13:00">13:00 - 13:45</option>
                                <option value="14:00">14:00 - 14:45</option>
                                <option value="15:00">15:00 - 15:45</option>
                                <option value="16:00">16:00 - 16:45</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="service_type">Hizmet Türü *</label>
                            <select id="service_type" name="service_type" required>
                                <option value="">Seçiniz</option>
                                <option value="bireysel_terapi">Bireysel Terapi</option>
                                <option value="cift_terapisi">Çift Terapisi</option>
                                <option value="aile_danismanligi">Aile Danışmanlığı</option>
                                <option value="cocuk_ergen">Çocuk ve Ergen Psikolojisi</option>
                                <option value="beslenme_danismanligi">Beslenme Danışmanlığı</option>
                                <option value="diger">Diğer</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="message">Mesajınız *</label>
                        <textarea id="message" name="message" rows="5" required placeholder="Randevu talebiniz hakkında ek bilgiler..."></textarea>
                    </div>
                    
                    <!-- KVKK Onayı -->
                    <div class="form-group kvkk-section">
                        <div class="kvkk-checkbox">
                            <input type="checkbox" id="kvkk_consent" name="kvkk_consent" required>
                            <label for="kvkk_consent">
                                <strong>KVKK Aydınlatma Metni</strong>'ni okudum ve kişisel verilerimin işlenmesine onay veriyorum. *
                            </label>
                        </div>
                        <div class="kvkk-text" id="kvkkText" style="display: none; margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; font-size: 0.9rem; line-height: 1.5; max-height: 200px; overflow-y: auto;">
                            <h4>Kişisel Verilerin Korunması Kanunu (KVKK) Aydınlatma Metni</h4>
                            <p><strong>Veri Sorumlusu:</strong> Psikolog Merkezi</p>
                            <p><strong>Kişisel Verilerin İşlenme Amaçları:</strong></p>
                            <ul>
                                <li>Randevu taleplerinizi karşılamak</li>
                                <li>Hizmet kalitesini artırmak</li>
                                <li>Yasal yükümlülükleri yerine getirmek</li>
                                <li>İletişim kurmak ve bilgilendirme yapmak</li>
                            </ul>
                            <p><strong>İşlenen Kişisel Veriler:</strong> Ad, soyad, TC kimlik numarası, telefon, e-posta, randevu bilgileri</p>
                            <p><strong>Kişisel Verilerin Aktarılması:</strong> Kişisel verileriniz, yukarıda belirtilen amaçlar dışında üçüncü kişilerle paylaşılmaz.</p>
                            <p><strong>Haklarınız:</strong> KVKK'nın 11. maddesi kapsamında sahip olduğunuz haklar hakkında detaylı bilgi için bizimle iletişime geçebilirsiniz.</p>
                        </div>
                        <button type="button" class="kvkk-toggle" onclick="toggleKVKK()" style="background: none; border: none; color: #667eea; text-decoration: underline; cursor: pointer; font-size: 0.9rem; margin-top: 5px;">
                            KVKK metnini oku
                        </button>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Randevu Talebi Gönder</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <!-- Hizmetler Özeti -->
    <section class="section" style="background-color: #F7FAFC;">
        <div class="container" style="display: flex; flex-direction: column; align-items: center;">
            <h2 class="section-title text-center">Hizmetlerimiz</h2>
            <div class="services-grid" style="width: 100%; max-width: 800px;">
                <div class="service-card">
                    <h3>Bireysel Terapi</h3>
                    <p>Kişisel sorunlarınızı çözmek ve ruh sağlığınızı iyileştirmek için uzman psikologlarımızla birebir görüşün.</p>
                </div>
                <div class="service-card">
                    <h3>Çift Terapisi</h3>
                    <p>İlişki sorunlarınızı çözmek ve daha sağlıklı bir ilişki kurmak için çift terapisi hizmetimizden yararlanın.</p>
                </div>
                <div class="service-card">
                    <h3>Aile Danışmanlığı</h3>
                    <p>Aile içi iletişim sorunları ve çatışmaları çözmek için profesyonel aile danışmanlığı hizmeti sunuyoruz.</p>
                </div>
                <div class="service-card">
                    <h3>Çocuk ve Ergen Psikolojisi</h3>
                    <p>Çocuklarınızın ve ergenlerin ruh sağlığı için özel olarak tasarlanmış terapi programları.</p>
                </div>
            </div>
        </div>
    </section>
    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-flex">
            <div class="footer-brand">
                <h3>Psikolog Merkezi</h3>
                <p>Modern psikolojik danışmanlık ve terapi merkezi.</p>
            </div>
            <div class="footer-links">
                <a href="index.php">Ana Sayfa</a>
                <a href="hakkimizda.php">Hakkımızda</a>
                <a href="blog.php">Blog</a>
                <a href="beslenme-diyetetik.php">Beslenme & Diyetetik</a>
                <a href="randevu.php">Randevu Al</a>
            </div>
            <div class="footer-contact">
                <p>info@psikologmerkezi.com</p>
                <p>+90 (212) 555 0123</p>
                <div class="footer-socials">
                    <a href="#" aria-label="Instagram">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="2" y="2" width="20" height="20" rx="5"/>
                            <circle cx="12" cy="12" r="5"/>
                            <circle cx="17.5" cy="6.5" r="1"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="Twitter">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M23 3a10.9 10.9 0 01-3.14 1.53A4.48 4.48 0 0022.4.36a9.09 9.09 0 01-2.88 1.1A4.52 4.52 0 0016.11 0c-2.5 0-4.52 2.02-4.52 4.52 0 .35.04.7.11 1.03C7.69 5.4 4.07 3.7 1.64.9c-.38.65-.6 1.4-.6 2.2 0 1.52.77 2.86 1.94 3.65A4.48 4.48 0 01.96 6v.06c0 2.13 1.52 3.91 3.54 4.31-.37.1-.76.16-1.16.16-.28 0-.56-.03-.83-.08.56 1.75 2.19 3.02 4.13 3.06A9.05 9.05 0 010 19.54a12.8 12.8 0 006.92 2.03c8.3 0 12.85-6.88 12.85-12.85 0-.2 0-.39-.01-.58A9.22 9.22 0 0023 3z"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="Facebook">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M18 2h-3a4 4 0 00-4 4v3H7v4h4v8h4v-8h3l1-4h-4V6a1 1 0 011-1h3z"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="LinkedIn">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z"/>
                                <rect x="2" y="9" width="4" height="12"/>
                                <circle cx="4" cy="4" r="2"/>
                            </svg>
                        </a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Psikolog Merkezi. Tüm hakları saklıdır.</p>
        </div>
    </footer>
    <script src="js/main.js"></script>
    <script>
    // Telefon input maskesi (randevu formu için)
    function maskPhoneInput(input) {
        input.addEventListener('input', function(e) {
            let value = input.value.replace(/\D/g, '');
            if (value.startsWith('0')) value = value.substring(1); // baştaki 0'ı at
            if (value.length > 10) value = value.substring(0, 10);
            let formatted = '';
            if (value.length > 0) formatted = '0';
            if (value.length > 2) formatted += '(' + value.substring(0, 3) + ') ';
            else if (value.length > 0) formatted += '(' + value;
            if (value.length > 5) formatted += value.substring(3, 6) + ' ';
            else if (value.length > 3) formatted += value.substring(3);
            if (value.length > 7) formatted += value.substring(6, 8) + ' ';
            else if (value.length > 6) formatted += value.substring(6);
            if (value.length > 8) formatted += value.substring(8);
            input.value = formatted.trim();
        });
    }
    
    // TC Kimlik numarası sadece sayı girişi
    function maskTCInput(input) {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 11) {
                this.value = this.value.substring(0, 11);
            }
        });
    }
    
    // KVKK metnini göster/gizle
    function toggleKVKK() {
        const kvkkText = document.getElementById('kvkkText');
        const toggleBtn = document.querySelector('.kvkk-toggle');
        
        if (kvkkText.style.display === 'none') {
            kvkkText.style.display = 'block';
            toggleBtn.textContent = 'KVKK metnini gizle';
        } else {
            kvkkText.style.display = 'none';
            toggleBtn.textContent = 'KVKK metnini oku';
        }
    }
    
    // TC Kimlik Numarası Validasyon Fonksiyonu (JavaScript)
    function validateTurkishIDJS(tc) {
        // TC kimlik numarası 11 haneli olmalı
        if (tc.length !== 11) {
            return false;
        }
        
        // Sadece rakam içermeli
        if (!/^\d{11}$/.test(tc)) {
            return false;
        }
        
        // İlk hane 0 olamaz
        if (tc[0] === '0') {
            return false;
        }
        
        // 1, 3, 5, 7, 9. hanelerin toplamının 7 katından, 2, 4, 6, 8. hanelerin toplamı çıkartıldığında, elde edilen sonucun 10'a bölümünden kalan, 10. haneyi vermelidir
        const odd_sum = parseInt(tc[0]) + parseInt(tc[2]) + parseInt(tc[4]) + parseInt(tc[6]) + parseInt(tc[8]);
        const even_sum = parseInt(tc[1]) + parseInt(tc[3]) + parseInt(tc[5]) + parseInt(tc[7]);
        const digit_10 = (odd_sum * 7 - even_sum) % 10;
        
        if (digit_10 != parseInt(tc[9])) {
            return false;
        }
        
        // İlk 10 hanenin toplamının 10'a bölümünden kalan, 11. haneyi vermelidir
        let sum_first_10 = 0;
        for (let i = 0; i < 10; i++) {
            sum_first_10 += parseInt(tc[i]);
        }
        const digit_11 = sum_first_10 % 10;
        
        if (digit_11 != parseInt(tc[10])) {
            return false;
        }
        
        return true;
    }
    
    // Form validasyonu
    function validateForm() {
        const nationalId = document.getElementById('national_id').value;
        const kvkkConsent = document.getElementById('kvkk_consent').checked;
        
        if (nationalId.length !== 11) {
            alert('TC Kimlik numarası 11 haneli olmalıdır.');
            return false;
        }
        
        if (!/^\d{11}$/.test(nationalId)) {
            alert('TC Kimlik numarası sadece rakam içermelidir.');
            return false;
        }
        
        if (!validateTurkishIDJS(nationalId)) {
            alert('Geçersiz TC kimlik numarası. Lütfen doğru TC kimlik numarasını giriniz.');
            return false;
        }
        
        if (!kvkkConsent) {
            alert('KVKK aydınlatma metnini okumanız ve onaylamanız gerekmektedir.');
            return false;
        }
        
        return true;
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        var phoneInputs = document.querySelectorAll('input[type="tel"]');
        phoneInputs.forEach(maskPhoneInput);
        
        var tcInput = document.getElementById('national_id');
        if (tcInput) {
            maskTCInput(tcInput);
            
            // TC Kimlik numarası için gerçek zamanlı validasyon
            tcInput.addEventListener('input', function() {
                const value = this.value;
                const feedbackElement = document.getElementById('tc-feedback');
                
                if (value.length === 11) {
                    if (validateTurkishIDJS(value)) {
                        this.style.borderColor = '#10B981';
                        if (feedbackElement) {
                            feedbackElement.textContent = '✓ Geçerli TC kimlik numarası';
                            feedbackElement.style.color = '#10B981';
                        }
                    } else {
                        this.style.borderColor = '#EF4444';
                        if (feedbackElement) {
                            feedbackElement.textContent = '✗ Geçersiz TC kimlik numarası';
                            feedbackElement.style.color = '#EF4444';
                        }
                    }
                } else {
                    this.style.borderColor = '';
                    if (feedbackElement) {
                        feedbackElement.textContent = '';
                    }
                }
            });
        }
        
        // Form submit kontrolü
        var form = document.getElementById('appointmentForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                }
            });
        }

        // Tarihe göre dolu saatleri getir ve seçenekleri pasif yap
        const dateInput = document.getElementById('preferred_date');
        const timeSelect = document.getElementById('appointment_time');
        function refreshTimeOptions(unavailable) {
            if (!timeSelect) return;
            // Önce tüm seçenekleri aktif hale getir
            Array.from(timeSelect.options).forEach(function(opt) {
                opt.disabled = false;
                opt.textContent = opt.textContent.replace(/\s*\(Dolu\)$/,'');
                opt.style.textDecoration = 'none';
                opt.style.color = '';
            });
            // Dolu olanları pasif yap ve üstünü çiz
            (unavailable || []).forEach(function(t) {
                const opt = Array.from(timeSelect.options).find(function(o){ return o.value === t; });
                if (opt && opt.value) {
                    opt.disabled = true;
                    opt.textContent = opt.textContent + ' (Dolu)';
                    opt.style.textDecoration = 'line-through';
                    opt.style.color = '#9CA3AF';
                }
            });
            // Eğer seçili saat dolu ise, seçim temizle
            if (timeSelect.value && timeSelect.selectedOptions[0].disabled) {
                timeSelect.value = '';
            }
        }
        async function fetchUnavailable(dateStr) {
            if (!dateStr) { refreshTimeOptions([]); return; }
            try {
                const res = await fetch('get_unavailable_slots.php?date=' + encodeURIComponent(dateStr));
                const data = await res.json();
                if (data && data.success) {
                    refreshTimeOptions(data.unavailable);
                } else {
                    refreshTimeOptions([]);
                }
            } catch (e) {
                refreshTimeOptions([]);
            }
        }
        if (dateInput) {
            dateInput.addEventListener('change', function(){
                fetchUnavailable(dateInput.value);
            });
            if (dateInput.value) {
                fetchUnavailable(dateInput.value);
            }
        }
    });
    </script>
</body>
</html> 