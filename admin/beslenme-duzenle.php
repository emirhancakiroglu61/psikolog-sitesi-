<?php
require_once '../includes/config.php';
requireAdmin();
$pdo = getDBConnection();
$message = '';
$message_type = '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: beslenme-yonetimi.php');
    exit;
}
$stmt = $pdo->prepare('SELECT * FROM beslenme_diyetetik WHERE id = ?');
$stmt->execute([$id]);
$beslenme = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$beslenme) {
    header('Location: beslenme-yonetimi.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $message = 'Güvenlik hatası. Lütfen tekrar deneyin.';
        $message_type = 'error';
    } else {
        $title = cleanInput($_POST['title']);
        $content = cleanInput($_POST['content']);
        $errors = [];
        if (empty($title)) $errors[] = 'Başlık alanı zorunludur.';
        if (empty($content)) $errors[] = 'İçerik alanı zorunludur.';
        $image_path = $beslenme['image_path'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file['type'], $allowed_types)) {
                $errors[] = 'Sadece JPG, PNG ve GIF dosyaları yüklenebilir.';
            }
            if ($file['size'] > 5 * 1024 * 1024) {
                $errors[] = 'Dosya boyutu 5MB\'dan büyük olamaz.';
            }
            if (empty($errors)) {
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'beslenme_' . time() . '_' . uniqid() . '.' . $extension;
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
                $stmt = $pdo->prepare("UPDATE beslenme_diyetetik SET title = ?, content = ?, image_path = ? WHERE id = ?");
                $stmt->execute([$title, $content, $image_path, $id]);
                $message = 'Beslenme yazısı başarıyla güncellendi.';
                $message_type = 'success';
                $beslenme['title'] = $title;
                $beslenme['content'] = $content;
                $beslenme['image_path'] = $image_path;
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
    <title>Beslenme Yazısı Düzenle - Admin Panel</title>
    <link rel="stylesheet" href="admin-styles.css">
</head>
<body>
    <div class="admin-container">
        <div class="container">
            <div class="admin-header">
                <div class="admin-header-content">
                    <h1>🥗 Beslenme Yazısı Düzenle</h1>
                </div>
            </div>
            <div class="admin-nav">
                <a href="dashboard.php">🏠 Dashboard</a>
                <a href="blog-yonetimi.php">📝 Blog Yönetimi</a>
                <a href="beslenme-yonetimi.php" style="font-weight:700; color:var(--primary-purple);">🥗 Beslenme Yönetimi</a>
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
                <h2 style="color: var(--white); font-size: 1.8rem; font-weight: 700; margin-bottom: 2rem; text-align: center;">🥗 Beslenme Yazısı Düzenle</h2>
                <form method="POST" action="beslenme-duzenle.php?id=<?php echo $id; ?>" enctype="multipart/form-data" id="beslenme-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="form-group">
                        <label for="title">📋 Başlık *</label>
                        <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($beslenme['title']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="image">🖼️ Resim (İsteğe bağlı)</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="image" name="image" class="file-input" accept="image/*" onchange="previewImage(this)">
                            <label for="image" class="file-input-label">
                                📁 Resim seçmek için tıklayın veya sürükleyin
                                <br><small>JPG, PNG, GIF - Maksimum 5MB</small>
                            </label>
                        </div>
                        <div id="image-preview">
                            <?php if ($beslenme['image_path']): ?>
                                <img src="../uploads/<?php echo htmlspecialchars($beslenme['image_path']); ?>" class="preview-image" alt="Önizleme">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="content">📝 İçerik *</label>
                        <textarea id="content" name="content" class="form-control" required><?php echo htmlspecialchars($beslenme['content']); ?></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn">💾 Yazıyı Güncelle</button>
                        <a href="beslenme-yonetimi.php" class="btn btn-secondary">❌ İptal</a>
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