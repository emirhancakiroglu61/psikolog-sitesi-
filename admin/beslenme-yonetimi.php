<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM beslenme_diyetetik ORDER BY created_at DESC");
$contents = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beslenme Yönetimi - Psikolog Merkezi</title>
    <link rel="stylesheet" href="admin-styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
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
        <!-- Admin Header -->
        <div class="container">
            <div class="admin-header">
                <div class="admin-header-content">
                    <h1>🥗 Beslenme Yönetimi</h1>
                    <div class="admin-user-info">
                        <span><?php echo htmlspecialchars($_SESSION['admin_first_name'] . ' ' . $_SESSION['admin_last_name']); ?></span>
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
            <div class="admin-page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
                <h1 class="admin-page-title">Beslenme & Diyetetik Yönetimi</h1>
                <a href="beslenme-ekle.php" class="btn">+ Yeni Beslenme Yazısı</a>
            </div>
            <div class="admin-table-container card">
                <?php if (empty($contents)): ?>
                <div class="admin-empty-state">
                    <h3>Henüz beslenme yazısı bulunmuyor</h3>
                    <p>İlk beslenme yazınızı eklemek için "Yeni Beslenme Yazısı" butonuna tıklayın.</p>
                </div>
                <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Başlık</th>
                            <th>Yayın Tarihi</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contents as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                            <td><?php echo date('d.m.Y H:i', strtotime($item['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="beslenme-duzenle.php?id=<?php echo $item['id']; ?>" class="action-btn btn-detail">Düzenle</a>
                                    <button onclick="deleteBeslenme(<?php echo $item['id']; ?>)" class="action-btn btn-reject">Sil</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
    function deleteBeslenme(id) {
        if (confirm('Bu beslenme yazısını silmek istediğinizden emin misiniz?')) {
            window.location.href = 'beslenme-sil.php?id=' + id;
        }
    }
    </script>
</body>
</html> 