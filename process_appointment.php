<?php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDBConnection();
    
    $name = cleanInput($_POST['name'] ?? '');
    $national_id = cleanInput($_POST['national_id'] ?? '');
    $phone = cleanInput($_POST['phone'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $preferred_date = cleanInput($_POST['preferred_date'] ?? '');
    $appointment_time = cleanInput($_POST['appointment_time'] ?? '');
    $service_type = cleanInput($_POST['service_type'] ?? '');
    $message = cleanInput($_POST['message'] ?? '');
    $kvkk_consent = isset($_POST['kvkk_consent']) ? true : false;
    
    // Validasyon
    $errors = [];
    
    if (empty($name)) $errors[] = 'Ad soyad alanı zorunludur.';
    if (empty($national_id)) $errors[] = 'TC kimlik numarası zorunludur.';
    if (empty($phone)) $errors[] = 'Telefon alanı zorunludur.';
    if (empty($email)) $errors[] = 'E-posta alanı zorunludur.';
    if (empty($preferred_date)) $errors[] = 'Randevu tarihi zorunludur.';
    if (empty($appointment_time)) $errors[] = 'Randevu saati zorunludur.';
    if (empty($service_type)) $errors[] = 'Hizmet türü zorunludur.';
    if (empty($message)) $errors[] = 'Mesaj alanı zorunludur.';
    
    // TC Kimlik validasyonu
    if (!empty($national_id)) {
        if (!is_numeric($national_id) || strlen($national_id) !== 11) {
            $errors[] = 'TC kimlik numarası 11 haneli sayı olmalıdır.';
        } elseif (!validateTurkishID($national_id)) {
            $errors[] = 'Geçersiz TC kimlik numarası. Lütfen doğru TC kimlik numarasını giriniz.';
        }
    }
    
    // E-posta validasyonu
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Geçerli bir e-posta adresi giriniz.';
    }
    
    // KVKK onayı kontrolü
    if (!$kvkk_consent) {
        $errors[] = 'KVKK aydınlatma metnini okumanız ve onaylamanız gerekmektedir.';
    }
    
    // Tarih kontrolü
    if (!empty($preferred_date)) {
        $today = date('Y-m-d');
        if ($preferred_date < $today) {
            $errors[] = 'Randevu tarihi geçmiş bir tarih olamaz.';
        }
    }
    
    // TC Kimlik numarası ile daha önce randevu alınmış mı kontrolü
    // Kural: Aynı TC ile ad-soyad VE telefon aynıysa tekrar randevu OLUŞTURULABİLİR.
    // Aynı TC ile ad-soyad veya telefon farklıysa yeni randevu OLUŞTURULAMAZ.
    if (!empty($national_id)) {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM appointments 
             WHERE national_id = ? 
               AND status IN ('pending', 'approved')
               AND (
                    name <> ? OR 
                    phone <> ?
               )"
        );
        $stmt->execute([$national_id, $name, $phone]);
        $different_details_count = $stmt->fetchColumn();

        if ($different_details_count > 0) {
            $errors[] = 'Aynı TC ile farklı bilgilerle yeni randevu oluşturamazsınız.';
        }
    }

    // Seçilen tarih ve saat için çakışma engeli
    if (empty($errors) && !empty($preferred_date) && !empty($appointment_time)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE preferred_date = ? AND appointment_time = ? AND status IN ('pending','approved')");
        $stmt->execute([$preferred_date, $appointment_time]);
        $slot_taken = $stmt->fetchColumn();
        if ($slot_taken > 0) {
            $errors[] = 'Seçtiğiniz tarih ve saat doludur. Lütfen farklı bir saat seçiniz.';
        }
    }
    
    if (!empty($errors)) {
        $error_message = implode('|', $errors);
        header('Location: randevu.php?error=validation_error&details=' . urlencode($error_message));
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO appointments (name, national_id, phone, email, preferred_date, appointment_time, service_type, message, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
        $stmt->execute([$name, $national_id, $phone, $email, $preferred_date, $appointment_time, $service_type, $message]);
        
        header('Location: randevu.php?success=appointment_sent');
        exit;
    } catch (Exception $e) {
        header('Location: randevu.php?error=database_error');
        exit;
    }
} else {
    header('Location: randevu.php');
    exit;
}
?> 