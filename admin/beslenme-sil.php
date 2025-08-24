<?php
require_once '../includes/config.php';
requireAdmin();
$pdo = getDBConnection();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id) {
    $stmt = $pdo->prepare('DELETE FROM beslenme_diyetetik WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: beslenme-yonetimi.php?deleted=1');
    exit;
}
header('Location: beslenme-yonetimi.php');
exit;
?><!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Beslenme Yazısı Sil - Admin Panel</title>
    <link rel="stylesheet" href="admin-styles.css">
</head>
<body>
    <div class="admin-container">
        <div class="container">
            <div class="admin-header">
                <div class="admin-header-content">
                    <h1>🥗 Beslenme Yazısı Sil</h1>
                </div>
            </div>
            <div class="admin-nav">
                <a href="dashboard.php">🏠 Dashboard</a>
                <a href="blog-yonetimi.php">📝 Blog Yönetimi</a>
                <a href="beslenme-yonetimi.php" style="font-weight:700; color:var(--primary-purple);">🥗 Beslenme Yönetimi</a>
                <a href="randevu-yonetimi.php">📅 Randevu Yönetimi</a>
                <a href="message-management.php">💬 Mesaj Yönetimi</a>
                <a href="profile-settings.php">⚙️ Profil Ayarları</a>
            </div>
            <div class="card" style="text-align:center; padding:3rem;">
                <h2>Silme özelliği yakında eklenecek.</h2>
                <a href="beslenme-yonetimi.php" class="btn btn-secondary" style="margin-top:2rem;">← Geri Dön</a>
            </div>
        </div>
    </div>
</body>
</html> 