<?php
require_once 'includes/config.php';

// JSON response header
header('Content-Type: application/json');

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Sadece POST istekleri kabul edilir.']);
    exit();
}

// Admin kontrolü
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Bu işlem için yetkiniz bulunmamaktadır.']);
    exit();
}

// CSRF token kontrolü
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Güvenlik hatası. Lütfen tekrar deneyin.']);
    exit();
}

try {
    // Form verilerini al ve temizle
    $title = cleanInput($_POST['title'] ?? '');
    $content = cleanInput($_POST['content'] ?? '');
    $blog_id = isset($_POST['blog_id']) ? (int)$_POST['blog_id'] : null;
    
    // Validasyon
    $errors = [];
    
    if (empty($title)) {
        $errors[] = 'Başlık alanı zorunludur.';
    } elseif (strlen($title) < 5) {
        $errors[] = 'Başlık en az 5 karakter olmalıdır.';
    } elseif (strlen($title) > 255) {
        $errors[] = 'Başlık en fazla 255 karakter olabilir.';
    }
    
    if (empty($content)) {
        $errors[] = 'İçerik alanı zorunludur.';
    } elseif (strlen($content) < 50) {
        $errors[] = 'İçerik en az 50 karakter olmalıdır.';
    }
    
    // Hata varsa döndür
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => implode(' ', $errors)
        ]);
        exit();
    }
    
    // Veritabanı bağlantısı
    $pdo = getDBConnection();
    
    // Resim yükleme işlemi
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_path = handleImageUpload($_FILES['image']);
        if (!$image_path) {
            echo json_encode([
                'success' => false,
                'message' => 'Resim yüklenirken bir hata oluştu.'
            ]);
            exit();
        }
    }
    
    if ($blog_id) {
        // Güncelleme işlemi
        $sql = "UPDATE blog SET title = ?, content = ?";
        $params = [$title, $content];
        
        if ($image_path) {
            $sql .= ", image_path = ?";
            $params[] = $image_path;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $blog_id;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $message = 'Blog yazısı başarıyla güncellendi!';
    } else {
        // Yeni blog yazısı ekleme
        $stmt = $pdo->prepare("
            INSERT INTO blog (title, content, image_path)
            VALUES (?, ?, ?)
        ");
        
        $stmt->execute([$title, $content, $image_path]);
        $blog_id = $pdo->lastInsertId();
        
        $message = 'Blog yazısı başarıyla eklendi!';
    }
    
    // Başarılı response
    echo json_encode([
        'success' => true,
        'message' => $message,
        'blog_id' => $blog_id,
        'redirect' => 'admin/blog-yonetimi.php'
    ]);
    
} catch (Exception $e) {
    // Hata logla
    error_log('Blog hatası: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Bir hata oluştu. Lütfen daha sonra tekrar deneyin.'
    ]);
}

// Resim yükleme fonksiyonu
function handleImageUpload($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // Dosya türü kontrolü
    if (!in_array($file['type'], $allowed_types)) {
        return false;
    }
    
    // Dosya boyutu kontrolü
    if ($file['size'] > $max_size) {
        return false;
    }
    
    // Dosya adı oluştur
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    
    // Upload dizini oluştur
    $upload_dir = 'uploads/blog/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filepath = $upload_dir . $filename;
    
    // Dosyayı yükle
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filepath;
    }
    
    return false;
}
?> 