<?php
require_once '../includes/config.php';

// Giriş kontrolü
requireAdmin();

// Veritabanı bağlantısı
$pdo = getDBConnection();

$message = '';
$message_type = '';

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCSRFToken();
    $title = cleanInput($_POST['title']);
    $content = cleanInput($_POST['content']);
    
    // Validasyon
    $errors = [];
    
    if (empty($title)) $errors[] = 'Başlık alanı zorunludur.';
    if (empty($content)) $errors[] = 'İçerik alanı zorunludur.';
    
    // Resim yükleme kontrolü
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        
        // Dosya türü kontrolü
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = 'Sadece JPG, PNG ve GIF dosyaları yüklenebilir.';
        }
        
        // Dosya boyutu kontrolü (5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Dosya boyutu 5MB\'dan büyük olamaz.';
        }
        
        if (empty($errors)) {
            // Benzersiz dosya adı oluştur
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'blog_' . time() . '_' . uniqid() . '.' . $extension;
            $upload_path = '../uploads/' . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $image_path = $filename;
            } else {
                $errors[] = 'Dosya yüklenirken bir hata oluştu.';
            }
        }
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO blog (title, content, image_path) VALUES (?, ?, ?)");
            $stmt->execute([$title, $content, $image_path]);
            
            $message = 'Blog yazısı başarıyla eklendi.';
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
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Yazısı Ekle - Admin Panel</title>
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
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="container">
            <!-- Admin Header -->
            <div class="admin-header">
                <div class="admin-header-content">
                    <h1>✍️ Blog Yazısı Ekle</h1>
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
                <h2 style="color: var(--white); font-size: 1.8rem; font-weight: 700; margin-bottom: 2rem; text-align: center;">📝 Yeni Blog Yazısı Oluştur</h2>
                <form method="POST" action="blog-ekle.php" enctype="multipart/form-data" id="blog-form">
                    <?php echo getCSRFTokenInput(); ?>
                    
                    <div class="form-group">
                        <label for="title">📋 Blog Başlığı *</label>
                        <input type="text" id="title" name="title" class="form-control" 
                               value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                               placeholder="Blog yazınızın başlığını girin..." required>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">🖼️ Blog Resmi (İsteğe bağlı)</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="image" name="image" class="file-input" accept="image/*" onchange="previewImage(this)">
                            <label for="image" class="file-input-label">
                                📁 Resim seçmek için tıklayın veya sürükleyin
                                <br><small>JPG, PNG, GIF - Maksimum 5MB</small>
                            </label>
                        </div>
                        <div id="image-preview"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="content">📝 Blog İçeriği *</label>
                        <textarea id="content" name="content" class="form-control" 
                                  placeholder="Blog yazınızın içeriğini buraya yazın..." required><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn">💾 Blog Yazısını Kaydet</button>
                        <a href="blog-yonetimi.php" class="btn btn-secondary">❌ İptal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('image-preview');
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="preview-image" alt="Önizleme">`;
                }
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '';
            }
        }
    </script>
</body>
</html> 