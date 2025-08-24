<?php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDBConnection();
    
    // Form verilerini al
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validasyon
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Ad Soyad alanı zorunludur.';
    }
    
    if (empty($email)) {
        $errors[] = 'E-posta alanı zorunludur.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Geçerli bir e-posta adresi giriniz.';
    }
    
    if (empty($subject)) {
        $errors[] = 'Konu alanı zorunludur.';
    }
    
    if (empty($message)) {
        $errors[] = 'Mesaj alanı zorunludur.';
    }
    
    // Hata yoksa mesajı kaydet
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $subject, $message]);
            
            $success = 'Mesajınız başarıyla gönderildi. En kısa sürede size dönüş yapacağız.';
        } catch (PDOException $e) {
            $errors[] = 'Mesaj gönderilirken bir hata oluştu. Lütfen tekrar deneyiniz.';
        }
    }
    
    // Sonucu JSON olarak döndür
    header('Content-Type: application/json');
    echo json_encode([
        'success' => empty($errors),
        'message' => $success ?? implode('<br>', $errors),
        'errors' => $errors
    ]);
    exit;
}

// GET isteği ise ana sayfaya yönlendir
header('Location: index.php');
exit;
?> 