<?php
require_once '../includes/config.php';
require_once 'csrf_middleware.php';

// Giriş kontrolü
requireAdmin();

// Veritabanı bağlantısı
$pdo = getDBConnection();

$message = '';
$message_type = '';

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCSRFToken();
    $first_name = cleanInput($_POST['first_name']);
    $last_name = cleanInput($_POST['last_name']);
    $national_id = cleanInput($_POST['national_id']);
    $details = cleanInput($_POST['details']);
    $active = isset($_POST['active']) ? 1 : 0;
    
    // Validasyon
    $errors = [];
    
    if (empty($first_name)) $errors[] = 'Ad alanı zorunludur.';
    if (empty($last_name)) $errors[] = 'Soyad alanı zorunludur.';
    if (empty($national_id)) $errors[] = 'TC Kimlik alanı zorunludur.';
    
    // TC Kimlik format kontrolü
    if (!empty($national_id) && (!is_numeric($national_id) || strlen($national_id) !== 11)) {
        $errors[] = 'TC Kimlik numarası 11 haneli sayı olmalıdır.';
    }
    
    // Mevcut danışan kontrolü (ad, soyad ve TC kimlik ile)
    $existing_patient = null;
    if (!empty($first_name) && !empty($last_name) && !empty($national_id)) {
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, national_id, details, active, created_at FROM patients WHERE first_name = ? AND last_name = ? AND national_id = ?");
        $stmt->execute([$first_name, $last_name, $national_id]);
        $existing_patient = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    if (empty($errors)) {
        try {
            if ($existing_patient) {
                // Mevcut danışanı güncelle - detayları birleştir
                $old_details = $existing_patient['details'];
                $new_details = $details;
                
                // Eski ve yeni detayları birleştir
                $combined_details = '';
                if (!empty($old_details)) {
                    $combined_details .= $old_details . "\n\n";
                }
                $combined_details .= "--- YENİ EKLEME (" . date('d.m.Y H:i') . ") ---\n" . $new_details;
                
                $stmt = $pdo->prepare("UPDATE patients SET details = ?, active = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$combined_details, $active, $existing_patient['id']]);
                
                $message = 'Mevcut danışan bulundu ve bilgileri güncellendi. Detaylar birleştirildi.';
                $message_type = 'success';
                
                // Formu temizle
                $_POST = array();
            } else {
                // Yeni danışan ekle
                $stmt = $pdo->prepare("INSERT INTO patients (first_name, last_name, national_id, details, active) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$first_name, $last_name, $national_id, $details, $active]);
                
                $message = 'Yeni danışan başarıyla eklendi.';
                $message_type = 'success';
                
                // Formu temizle
                $_POST = array();
            }
        } catch (PDOException $e) {
            $message = 'Bir hata oluştu. Lütfen tekrar deneyin.';
            $message_type = 'error';
        }
    } else {
        $message = implode('<br>', $errors);
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danışan Ekle - Admin Panel</title>
    <link rel="stylesheet" href="admin-styles.css">
    <style>
        .admin-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        
        .admin-user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-user-info span {
            color: var(--white);
            font-weight: 500;
            font-size: 1rem;
        }
        
        .admin-logout-btn {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        
        .admin-logout-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            transform: translateY(-1px);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            color: var(--white);
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-purple-light);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }
        .form-control::placeholder {
            color: var(--gray-400);
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary-purple-light);
        }
        .checkbox-group label {
            color: var(--gray-300);
            font-size: 1rem;
            cursor: pointer;
        }
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-primary {
            background: var(--primary-purple-light);
            color: var(--white);
        }
        .btn-primary:hover {
            background: var(--primary-purple-dark);
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: var(--gray-600);
            color: var(--white);
        }
        .btn-secondary:hover {
            background: var(--gray-700);
            transform: translateY(-1px);
        }
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        .message.success {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }
        .message.error {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="container">
            <!-- Admin Header -->
            <div class="admin-header">
                <div class="admin-header-content">
                    <h1>👤 Danışan Ekle</h1>
                    <div class="admin-user-info">
                        <span>Hoş geldin, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                        <a href="logout.php" class="admin-logout-btn">🚪 Çıkış Yap</a>
                    </div>
                </div>
            </div>

            <!-- Admin Navigation -->
            <div class="admin-nav">
                <a href="dashboard.php">🏠 Dashboard</a>
                <a href="blog-yonetimi.php">📝 Blog Yönetimi</a>
                <a href="beslenme-yonetimi.php">🥗 Beslenme Yönetimi</a>
                <a href="randevu-yonetimi.php">📅 Randevu Yönetimi</a>
                <a href="message-management.php">💬 Mesaj Yönetimi</a>
                <a href="patients-management.php">👤 Danışan Yönetimi</a>
                <a href="profile-settings.php">⚙️ Profil Ayarları</a>
            </div>

            <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <h2 style="color: var(--white); font-size: 1.8rem; font-weight: 700; margin-bottom: 2rem; text-align: center;">👤 Yeni Danışan Ekle</h2>
                <form method="POST" action="patient-add.php" id="patient-form">
                    <?php echo getCSRFTokenInput(); ?>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="first_name">👤 Ad *</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" 
                                   value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" 
                                   placeholder="Danışanın adını girin..." required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">👤 Soyad *</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" 
                                   value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" 
                                   placeholder="Danışanın soyadını girin..." required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="national_id">🆔 TC Kimlik No *</label>
                        <input type="text" id="national_id" name="national_id" class="form-control" 
                               value="<?php echo isset($_POST['national_id']) ? htmlspecialchars($_POST['national_id']) : ''; ?>" 
                               placeholder="11 haneli TC kimlik numarasını girin..." 
                               maxlength="11" pattern="[0-9]{11}" required>
                        <small style="color: var(--gray-400); margin-top: 0.5rem; display: block;">
                            TC Kimlik numarası 11 haneli sayı olmalıdır.
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="details">📝 Danışan Detayları</label>
                        <textarea id="details" name="details" class="form-control" 
                                  placeholder="Danışan hakkında detayları buraya yazın..." 
                                  rows="5"><?php echo isset($_POST['details']) ? htmlspecialchars($_POST['details']) : ''; ?></textarea>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="active" name="active" <?php echo (!isset($_POST['active']) || $_POST['active']) ? 'checked' : ''; ?>>
                        <label for="active">✅ Aktif (Danışan aktif durumda)</label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">💾 Danışanı Kaydet</button>
                        <a href="patients-management.php" class="btn btn-secondary">❌ İptal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // TC Kimlik numarası sadece sayı girişi
        document.getElementById('national_id').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        // Form validasyonu
        document.getElementById('patient-form').addEventListener('submit', function(e) {
            const nationalId = document.getElementById('national_id').value;
            
            if (nationalId.length !== 11) {
                e.preventDefault();
                alert('TC Kimlik numarası 11 haneli olmalıdır.');
                return false;
            }
            
            if (!/^\d{11}$/.test(nationalId)) {
                e.preventDefault();
                alert('TC Kimlik numarası sadece rakam içermelidir.');
                return false;
            }
        });
    </script>
</body>
</html> 