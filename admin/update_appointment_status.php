<?php
ini_set('display_errors', 0); // Hataları ekrana basma
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../php-error.log');
error_reporting(E_ALL);
require_once '../includes/config.php';

// Giriş kontrolü
requireAdmin();

// Veritabanı bağlantısı
$pdo = getDBConnection();

// JSON response header
header('Content-Type: application/json');

// POST kontrolü
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
    exit();
}

// CSRF token kontrolü
if (!validateCSRFToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Güvenlik hatası.']);
    exit();
}

// Parametre kontrolü
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz randevu ID.']);
    exit();
}

if (!isset($_POST['status']) || !in_array($_POST['status'], ['pending', 'approved', 'rejected', 'cancelled'])) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz durum.']);
    exit();
}

$appointment_id = (int)$_POST['id'];
$status = $_POST['status'];

try {
    // Randevuyu güncelle
    $stmt = $pdo->prepare("UPDATE appointments SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$status, $appointment_id]);
    
    if ($stmt->rowCount() > 0) {
        // Kullanıcı bilgilerini al
        $stmt2 = $pdo->prepare("SELECT name, email, phone, preferred_date, appointment_time, service_type FROM appointments WHERE id = ?");
        $stmt2->execute([$appointment_id]);
        $appt = $stmt2->fetch(PDO::FETCH_ASSOC);
        $mailResult = true;
        $mailError = '';
        if ($appt) {
            $status_text = [
                'approved' => 'Onaylandı',
                'rejected' => 'Reddedildi',
                'cancelled' => 'İptal Edildi',
                'pending' => 'Beklemede'
            ];
            $subject = 'Randevu Durumu Güncellendi - Uzman Psikoloji Merkezi';
            $message = "Merhaba {$appt['name']},\n\n";
            $message .= "Randevu talebinizin durumu güncellendi.\n";
            $message .= "Yeni Durum: " . ($status_text[$status] ?? $status) . "\n";
            $message .= "Randevu Tarihi: ".$appt['preferred_date']."\n";
            $message .= "Randevu Saati: ".$appt['appointment_time']."\n";
            $message .= "Hizmet Türü: ".$appt['service_type']."\n\n";
            if ($status === 'approved') {
                $message .= "Randevunuz onaylandı. Belirtilen tarih ve saatte sizi bekliyoruz.";
            } elseif ($status === 'rejected') {
                $message .= "Üzgünüz, randevu talebiniz reddedildi.";
            } elseif ($status === 'cancelled') {
                $message .= "Randevunuz iptal edildi.";
            }
            $message .= "\n\nUzman Psikoloji Merkezi";
            // E-posta gönder
            if (!empty($appt['email'])) {
                $mailResult = sendMailSMTP($appt['email'], $subject, $message);
                if ($mailResult !== true) {
                    $mailError = $mailResult;
                    $mailResult = false;
                }
            }
            // SMS gönder (placeholder)
            // sendSMS($appt['phone'], $message); // Gerçek SMS API ile entegre edilmeli
        }
        if ($mailResult) {
            echo json_encode(['success' => true, 'message' => 'Randevu durumu başarıyla güncellendi.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Randevu güncellendi fakat mail gönderilemedi. ' . $mailError]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Randevu bulunamadı.']);
    }
    
} catch (PDOException $e) {
    error_log(date('[Y-m-d H:i:s] ') . 'Veritabanı hatası: ' . $e->getMessage() . "\n", 3, __DIR__ . '/../php-error.log');
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?> 