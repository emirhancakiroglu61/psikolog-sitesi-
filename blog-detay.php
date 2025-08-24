<?php
require_once 'includes/config.php';
$pdo = getDBConnection();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM blog WHERE id = ?");
$stmt->execute([$id]);
$blog = $stmt->fetch();
if (!$blog) {
    header("Location: blog.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($blog['title']); ?> - Psikolog Merkezi</title>
    <meta name="description" content="<?php echo htmlspecialchars(substr(strip_tags($blog['content']), 0, 160)); ?>">
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
                <li><a href="blog.php" class="active">Blog</a></li>
                <li><a href="beslenme-diyetetik.php">Beslenme & Diyetetik</a></li>
                <li><a href="randevu.php">Randevu Al</a></li>
                <li><a href="index.php#iletisim">İletişim</a></li>
            </ul>
        </nav>
    </header>
    <!-- Blog Detay -->
    <section class="section">
        <div class="container">
            <div class="blog-detail-container">
                <div class="blog-detail-header">
                    <h1><?php echo htmlspecialchars($blog['title']); ?></h1>
                    <div class="blog-meta">
                        <span class="blog-date"><?php echo date('d.m.Y', strtotime($blog['created_at'])); ?></span>
                    </div>
                </div>
                <?php if ($blog['image_path']): ?>
                <div class="blog-detail-image">
                    <img src="uploads/<?php echo htmlspecialchars($blog['image_path']); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>">
                </div>
                <?php endif; ?>
                <div class="blog-detail-content">
                    <?php echo nl2br(htmlspecialchars($blog['content'])); ?>
                </div>
                <div class="blog-detail-footer">
                    <a href="blog.php" class="btn btn-secondary">← Blog'a Dön</a>
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
                    <a href="#" aria-label="Instagram"><svg width="20" height="20" fill="none" stroke="#667eea" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r="1"/></svg></a>
                    <a href="#" aria-label="Twitter"><svg width="20" height="20" fill="none" stroke="#667eea" stroke-width="2" viewBox="0 0 24 24"><path d="M23 3a10.9 10.9 0 01-3.14 1.53A4.48 4.48 0 0022.4.36a9.09 9.09 0 01-2.88 1.1A4.52 4.52 0 0016.11 0c-2.5 0-4.52 2.02-4.52 4.52 0 .35.04.7.11 1.03C7.69 5.4 4.07 3.7 1.64.9c-.38.65-.6 1.4-.6 2.2 0 1.52.77 2.86 1.94 3.65A4.48 4.48 0 01.96 6v.06c0 2.13 1.52 3.91 3.54 4.31-.37.1-.76.16-1.16.16-.28 0-.56-.03-.83-.08.56 1.75 2.19 3.02 4.13 3.06A9.05 9.05 0 010 19.54a12.8 12.8 0 006.92 2.03c8.3 0 12.85-6.88 12.85-12.85 0-.2 0-.39-.01-.58A9.22 9.22 0 0023 3z"/></svg></a>
                    <a href="#" aria-label="Facebook"><svg width="20" height="20" fill="none" stroke="#667eea" stroke-width="2" viewBox="0 0 24 24"><path d="M18 2h-3a4 4 0 00-4 4v3H7v4h4v8h4v-8h3l1-4h-4V6a1 1 0 011-1h3z"/></svg></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Psikolog Merkezi. Tüm hakları saklıdır.</p>
        </div>
    </footer>
    <script src="js/main.js"></script>
</body>
</html> 