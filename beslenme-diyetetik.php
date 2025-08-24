<?php
require_once 'includes/config.php';
$pdo = getDBConnection();
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 6;
$offset = ($page - 1) * $per_page;
$stmt = $pdo->query("SELECT COUNT(*) FROM beslenme_diyetetik");
$total_nutrition = $stmt->fetchColumn();
$total_pages = ceil($total_nutrition / $per_page);
$stmt = $pdo->prepare("SELECT * FROM beslenme_diyetetik ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$nutrition_contents = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beslenme & Diyetetik - Psikolog Merkezi</title>
    <meta name="description" content="Beslenme ve diyetetik hakkında uzman görüşler. Sağlıklı beslenme, kilo verme sürecinde psikoloji ve yeme bozuklukları.">
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
                <li><a href="blog.php">Blog</a></li>
                <li><a href="beslenme-diyetetik.php" class="active">Beslenme & Diyetetik</a></li>
                <li><a href="randevu.php">Randevu Al</a></li>
                <li><a href="index.php#iletisim">İletişim</a></li>
            </ul>
        </nav>
    </header>
    <!-- Hero Section -->
    <section class="hero hero-modern">
        <div class="container hero-content" style="display: flex; justify-content: center; align-items: center;">
            <div class="hero-text">
                <h1>Beslenme & Diyetetik</h1>
                <p>Psikolojik faktörlerin beslenme alışkanlıklarına etkisi ve sağlıklı yaşam için uzman rehberliği.</p>
            </div>
        </div>
    </section>
    <!-- Beslenme İçerikleri -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Beslenme ve Diyetetik İçeriklerimiz</h2>
            <p style="text-align: center; color: #4A5568; margin-bottom: 3rem; font-size: 1.1rem;">
                Sağlıklı beslenme, kilo verme sürecinde psikoloji, yeme bozuklukları ve daha fazlası hakkında uzman görüşlerimizi paylaşıyoruz.
            </p>
            <?php if (empty($nutrition_contents)): ?>
            <div class="service-card text-center">
                <h3>Henüz içerik bulunmuyor</h3>
                <p>Yakında yeni beslenme ve diyetetik içeriklerimiz yayınlanacak.</p>
            </div>
            <?php else: ?>
            <div class="featured-blogs-grid">
                <?php foreach ($nutrition_contents as $content): ?>
                <div class="featured-blog-card">
                    <?php if ($content['image_path']): ?>
                    <img src="uploads/<?php echo htmlspecialchars($content['image_path']); ?>" alt="<?php echo htmlspecialchars($content['title']); ?>" class="featured-blog-image">
                    <?php endif; ?>
                    <div class="featured-blog-content">
                        <h3><?php echo htmlspecialchars($content['title']); ?></h3>
                        <p><?php 
                            $excerpt = strip_tags($content['content']);
                            echo htmlspecialchars(substr($excerpt, 0, 200)) . (strlen($excerpt) > 200 ? '...' : '');
                        ?></p>
                        <a href="beslenme-detay.php?id=<?php echo $content['id']; ?>" class="btn btn-sm">Devamını Oku</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <!-- Sayfalama -->
            <?php if ($total_pages > 1): ?>
            <div class="text-center" style="margin-top: 3rem;">
                <div style="display: inline-flex; gap: 0.5rem; align-items: center;">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="btn btn-secondary" style="margin: 0;">Önceki</a>
                    <?php endif; ?>
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="btn <?php echo $i == $page ? '' : 'btn-secondary'; ?>" style="margin: 0; min-width: 40px;"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="btn btn-secondary" style="margin: 0;">Sonraki</a>
                    <?php endif; ?>
                </div>
                <p style="margin-top: 1rem; color: #4A5568; font-size: 0.9rem;">
                    Sayfa <?php echo $page; ?> / <?php echo $total_pages; ?> 
                    (Toplam <?php echo $total_nutrition; ?> içerik)
                </p>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
    <!-- Hizmetler Section -->
    <section class="section" style="background-color: #F7FAFC;">
        <div class="container">
            <h2 class="section-title">Beslenme & Diyetetik Hizmetlerimiz</h2>
            <div class="grid">
                <div class="card">
                    <h3>Beslenme Danışmanlığı</h3>
                    <p>Kişiye özel beslenme planları hazırlayarak sağlıklı yaşam hedeflerinize ulaşmanıza yardımcı oluyoruz.</p>
                </div>
                <div class="card">
                    <h3>Kilo Verme Sürecinde Psikoloji</h3>
                    <p>Kilo verme sürecinde motivasyonu koruma ve psikolojik engelleri aşma konularında uzman desteği.</p>
                </div>
                <div class="card">
                    <h3>Yeme Bozuklukları Tedavisi</h3>
                    <p>Anoreksiya, bulimiya ve diğer yeme bozukluklarının tedavisinde psikolojik destek ve beslenme rehberliği.</p>
                </div>
                <div class="card">
                    <h3>Duygusal Yeme</h3>
                    <p>Stres, üzüntü veya mutluluk anlarında aşırı yeme davranışını kontrol etme ve sağlıklı alternatifler geliştirme.</p>
                </div>
                <div class="card">
                    <h3>Çocuk Beslenmesi</h3>
                    <p>Çocuklarda sağlıklı beslenme alışkanlıkları geliştirme ve ebeveynlere rehberlik.</p>
                </div>
                <div class="card">
                    <h3>Hastalık Durumlarında Beslenme</h3>
                    <p>Diyabet, kalp hastalıkları ve diğer kronik hastalıklarda uygun beslenme planları ve psikolojik destek.</p>
                </div>
            </div>
        </div>
    </section>
    <!-- CTA Section -->
    <section class="section">
        <div class="container">
            <div class="card" style="text-align: center; background: linear-gradient(135deg, #6B46C1 0%, #553C9A 100%); color: white;">
                <h3 style="margin-bottom: 1rem;">Beslenme Uzmanımızla Görüşün</h3>
                <p style="margin-bottom: 2rem; opacity: 0.9;">
                    Sağlıklı beslenme ve diyetetik konularında uzman psikolog ve beslenme uzmanımızla randevu alarak kişisel destek alabilirsiniz.
                </p>
                <a href="randevu.php" class="btn" style="background: white; color: #6B46C1;">Randevu Al</a>
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
                    <a href="#" aria-label="Instagram">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="2" y="2" width="20" height="20" rx="5"/>
                            <circle cx="12" cy="12" r="5"/>
                            <circle cx="17.5" cy="6.5" r="1"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="Twitter">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M23 3a10.9 10.9 0 01-3.14 1.53A4.48 4.48 0 0022.4.36a9.09 9.09 0 01-2.88 1.1A4.52 4.52 0 0016.11 0c-2.5 0-4.52 2.02-4.52 4.52 0 .35.04.7.11 1.03C7.69 5.4 4.07 3.7 1.64.9c-.38.65-.6 1.4-.6 2.2 0 1.52.77 2.86 1.94 3.65A4.48 4.48 0 01.96 6v.06c0 2.13 1.52 3.91 3.54 4.31-.37.1-.76.16-1.16.16-.28 0-.56-.03-.83-.08.56 1.75 2.19 3.02 4.13 3.06A9.05 9.05 0 010 19.54a12.8 12.8 0 006.92 2.03c8.3 0 12.85-6.88 12.85-12.85 0-.2 0-.39-.01-.58A9.22 9.22 0 0023 3z"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="Facebook">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M18 2h-3a4 4 0 00-4 4v3H7v4h4v8h4v-8h3l1-4h-4V6a1 1 0 011-1h3z"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="LinkedIn">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z"/>
                                <rect x="2" y="9" width="4" height="12"/>
                                <circle cx="4" cy="4" r="2"/>
                            </svg>
                        </a>
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