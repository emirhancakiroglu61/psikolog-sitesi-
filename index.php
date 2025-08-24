<?php
require_once 'includes/config.php';
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM blog ORDER BY created_at DESC LIMIT 3");
$featured_blogs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Psikolog Merkezi - Modern Psikolojik Danışmanlık</title>
    <meta name="description" content="Uzman psikologlarımızla ruh sağlığınızı iyileştirin. Bireysel terapi, çift terapisi, aile danışmanlığı ve daha fazlası için hemen randevu alın.">
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
                <li><a href="index.php" class="active">Ana Sayfa</a></li>
                <li><a href="hakkimizda.php">Hakkımızda</a></li>
                <li><a href="blog.php">Blog</a></li>
                <li><a href="beslenme-diyetetik.php">Beslenme & Diyetetik</a></li>
                <li><a href="randevu.php">Randevu Al</a></li>
                <li><a href="#iletisim">İletişim</a></li>
            </ul>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container hero-content">
            <div class="hero-text">
                <h1>Ruh Sağlığınız İçin <span class="highlight">Profesyonel</span> Destek</h1>
                <p>Deneyimli uzman psikologlarımızla birlikte, yaşam kalitenizi artırmak ve psikolojik sorunlarınızı çözmek için buradayız. Modern terapi yöntemleri ve sıcak bir ortamda hizmetinizdeyiz.</p>
                <div class="hero-buttons">
                    <a href="randevu.php" class="btn">Hemen Randevu Al</a>
                    <a href="hakkimizda.php" class="btn btn-secondary">Hakkımızda</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="img/profil.jpg" alt="Psikolog Merkezi - Profesyonel Psikolog">
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Hizmetlerimiz</h2>
            <p class="section-subtitle">Size özel, kişiselleştirilmiş terapi programları ile ruh sağlığınızı iyileştiriyoruz</p>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3>Bireysel Terapi</h3>
                    <p>Kişisel sorunlarınızı çözmek, özgüveninizi artırmak ve yaşam kalitenizi iyileştirmek için uzman psikologlarımızla birebir görüşün.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3>Çift Terapisi</h3>
                    <p>İlişki sorunlarınızı çözmek, iletişimi güçlendirmek ve daha sağlıklı bir ilişki kurmak için çift terapisi hizmetimizden yararlanın.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2zM8 5h8M8 11h8M8 17h8"/>
                        </svg>
                    </div>
                    <h3>Aile Danışmanlığı</h3>
                    <p>Aile içi iletişim sorunları, çatışmalar ve ebeveyn-çocuk ilişkilerini iyileştirmek için profesyonel aile danışmanlığı hizmeti.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h3>Çocuk ve Ergen Psikolojisi</h3>
                    <p>Çocuklarınızın ve ergenlerin ruh sağlığı için özel olarak tasarlanmış terapi programları ve oyun terapisi.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3>Stres ve Anksiyete Yönetimi</h3>
                    <p>Günlük stres, anksiyete ve panik atak gibi sorunlarla başa çıkma teknikleri ve bilişsel davranışçı terapi.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <h3>Beslenme ve Diyetetik</h3>
                    <p>Psikolojik faktörlerin beslenme alışkanlıklarına etkisi ve sağlıklı yaşam için uzman rehberliği.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Blogs Section -->
    <section class="section" style="background: linear-gradient(135deg, var(--gray-50) 0%, var(--accent) 100%);">
        <div class="container">
            <h2 class="section-title">Öne Çıkan Blog Yazılarımız</h2>
            <p class="section-subtitle">Psikoloji ve ruh sağlığı hakkında uzman görüşlerimizi paylaştığımız blog yazılarımızı okuyabilirsiniz</p>
            <?php if (empty($featured_blogs)): ?>
            <div class="service-card text-center">
                <h3>Henüz blog yazısı bulunmuyor</h3>
                <p>Yakında yeni blog yazılarımız yayınlanacak.</p>
            </div>
            <?php else: ?>
            <div class="featured-blogs-grid">
                <?php foreach ($featured_blogs as $blog): ?>
                <div class="featured-blog-card">
                    <?php if ($blog['image_path']): ?>
                    <img src="uploads/<?php echo htmlspecialchars($blog['image_path']); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>" class="featured-blog-image">
                    <?php else: ?>
                    <img src="https://images.unsplash.com/photo-1559757148-5c350d0d3c56?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" alt="<?php echo htmlspecialchars($blog['title']); ?>" class="featured-blog-image">
                    <?php endif; ?>
                    <div class="featured-blog-content">
                        <h3><?php echo htmlspecialchars($blog['title']); ?></h3>
                        <p><?php 
                            $excerpt = strip_tags($blog['content']);
                            echo htmlspecialchars(substr($excerpt, 0, 150)) . (strlen($excerpt) > 150 ? '...' : '');
                        ?></p>
                        <a href="blog-detay.php?id=<?php echo $blog['id']; ?>" class="btn btn-sm">Devamını Oku</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center" style="margin-top: 3rem;">
                <a href="blog.php" class="btn">Tüm Blog Yazılarını Görüntüle</a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Beslenme & Diyetetik Bilgilendirme Section -->
    <section class="section testimonials-section">
        <div class="container">
            <h2 class="section-title">Beslenme & Diyetetik Hizmetlerimiz</h2>
            <p class="section-subtitle">Psikolojik faktörlerin beslenme alışkanlıklarına etkisi ve sağlıklı yaşam rehberliği</p>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <h4>Yeme Bozuklukları ve Psikoloji</h4>
                        <p>Anoreksiya, bulimiya ve tıkınırcasına yeme bozukluğu gibi durumların altında yatan psikolojik faktörleri anlayarak, bütüncül bir yaklaşımla tedavi sürecini yönetiyoruz. Beslenme alışkanlıklarının ruh sağlığıyla olan karmaşık ilişkisini ele alarak, kalıcı çözümler sunuyoruz.</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">YB</div>
                        <div class="testimonial-info">
                            <h4>Uzman Görüşü</h4>
                            <p>Psikoloji & Beslenme</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <h4>Kilo Verme Sürecinde Psikolojik Destek</h4>
                        <p>Kilo verme sürecinde yaşanan motivasyon kaybı, stres yeme ve duygusal yeme gibi psikolojik engelleri aşmak için bireyselleştirilmiş terapi programları uyguluyoruz. Sadece fiziksel değil, zihinsel sağlığınızı da destekleyerek sürdürülebilir sonuçlar elde etmenizi sağlıyoruz.</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">KV</div>
                        <div class="testimonial-info">
                            <h4>Uzman Görüşü</h4>
                            <p>Diyet & Psikoloji</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <h4>Sağlıklı Beslenme Alışkanlıkları</h4>
                        <p>Beslenme alışkanlıklarının değiştirilmesi sürecinde karşılaşılan psikolojik dirençleri anlayarak, davranış değişikliği teknikleri kullanıyoruz. Stres yönetimi, farkındalık ve bilişsel davranışçı terapi yöntemleriyle sağlıklı beslenme alışkanlıklarını kalıcı hale getiriyoruz.</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">SB</div>
                        <div class="testimonial-info">
                            <h4>Uzman Görüşü</h4>
                            <p>Beslenme & Davranış</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="section contact-section" id="iletisim">
        <div class="container">
            <div class="contact-content">
                <div class="contact-info">
                    <h2>İletişime Geçin</h2>
                    <p>Ruh sağlığınız için profesyonel destek almak istiyorsanız, hemen bizimle iletişime geçin. Size en uygun zamanı bulup randevu oluşturalım.</p>
                    
                    <div class="contact-details">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div class="contact-text">
                                <h4>Adres</h4>
                                <p>Örnek Mahallesi, Psikoloji Caddesi No:123<br>Merkez / İSTANBUL</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div class="contact-text">
                                <h4>Telefon</h4>
                                <p>+90 (212) 555 0123<br>+90 (532) 123 4567</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="contact-text">
                                <h4>E-posta</h4>
                                <p>info@psikologmerkezi.com<br>randevu@psikologmerkezi.com</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="contact-text">
                                <h4>Çalışma Saatleri</h4>
                                <p>Pazartesi - Cuma: 09:00 - 18:00<br>Cumartesi: 09:00 - 15:00</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="contact-socials">
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
                
                <!-- Mesaj Gönderme Formu: harita yerine -->
                <div style="margin-top: 2rem;">
                    <div style="max-width: 600px; margin: 0 auto;">
                        <h3 style="text-align: center; margin-bottom: 2rem; color: #fff;">Bize Mesaj Gönderin</h3>
                        <form id="contactForm" style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div>
                                    <label for="name" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #4A5568;">Ad Soyad *</label>
                                    <input type="text" id="name" name="name" required style="width: 100%; padding: 0.75rem; border: 2px solid #E2E8F0; border-radius: 8px; font-size: 1rem;">
                                </div>
                                <div>
                                    <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #4A5568;">E-posta *</label>
                                    <input type="email" id="email" name="email" required style="width: 100%; padding: 0.75rem; border: 2px solid #E2E8F0; border-radius: 8px; font-size: 1rem;">
                                </div>
                            </div>
                            <div style="margin-bottom: 1rem;">
                                <label for="phone" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #4A5568;">Telefon</label>
                                <input type="tel" id="phone" name="phone" style="width: 100%; padding: 0.75rem; border: 2px solid #E2E8F0; border-radius: 8px; font-size: 1rem;">
                            </div>
                            <div style="margin-bottom: 1rem;">
                                <label for="subject" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #4A5568;">Konu *</label>
                                <input type="text" id="subject" name="subject" required style="width: 100%; padding: 0.75rem; border: 2px solid #E2E8F0; border-radius: 8px; font-size: 1rem;">
                            </div>
                            <div style="margin-bottom: 1.5rem;">
                                <label for="message" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #4A5568;">Mesajınız *</label>
                                <textarea id="message" name="message" rows="5" required style="width: 100%; padding: 0.75rem; border: 2px solid #E2E8F0; border-radius: 8px; font-size: 1rem; resize: vertical;"></textarea>
                            </div>
                            <div style="text-align: center;">
                                <button type="submit" style="background: linear-gradient(135deg, #8B5CF6 0%, #667eea 100%); color: white; padding: 1rem 2rem; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                                    Mesaj Gönder
                                </button>
                            </div>
                            <div id="formMessage" style="margin-top: 1rem; text-align: center; font-weight: 600;"></div>
                        </form>
                    </div>
                </div>
            </div> <!-- .contact-content -->
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-flex">
            <div class="footer-brand">
                <h3>Psikolog Merkezi</h3>
                <p>Modern psikolojik danışmanlık ve terapi merkezi. Uzman kadromuzla ruh sağlığınızı iyileştirmek için buradayız. Bireysel terapi, çift terapisi, aile danışmanlığı ve daha fazlası için hizmetinizdeyiz.</p>
            </div>
            <div class="footer-links">
                <a href="index.php">Ana Sayfa</a>
                <a href="hakkimizda.php">Hakkımızda</a>
                <a href="blog.php">Blog</a>
                <a href="beslenme-diyetetik.php">Beslenme & Diyetetik</a>
                <a href="randevu.php">Randevu Al</a>
                <a href="#iletisim">İletişim</a>
            </div>
            <div class="footer-contact">
                <p>info@psikologmerkezi.com</p>
                <p>+90 (212) 555 0123</p>
                <p>Örnek Mahallesi, Psikoloji Caddesi No:123</p>
                <p>Merkez / İSTANBUL</p>
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
            <p>&copy; 2024 Psikolog Merkezi. Tüm hakları saklıdır. | Gizlilik Politikası | Kullanım Şartları</p>
        </div>
    </footer>

    <script src="js/main.js"></script>
    <script>
    // Telefon input maskesi (hem iletişim hem randevu formu için)
    function maskPhoneInput(input) {
        input.addEventListener('input', function(e) {
            let value = input.value.replace(/\D/g, '');
            if (value.startsWith('0')) value = value.substring(1); // baştaki 0'ı at
            if (value.length > 10) value = value.substring(0, 10);
            let formatted = '';
            if (value.length > 0) formatted = '0';
            if (value.length > 2) formatted += '(' + value.substring(0, 3) + ') ';
            else if (value.length > 0) formatted += '(' + value;
            if (value.length > 5) formatted += value.substring(3, 6) + ' ';
            else if (value.length > 3) formatted += value.substring(3);
            if (value.length > 7) formatted += value.substring(6, 8) + ' ';
            else if (value.length > 6) formatted += value.substring(6);
            if (value.length > 8) formatted += value.substring(8);
            input.value = formatted.trim();
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        var phoneInputs = document.querySelectorAll('input[type="tel"]');
        phoneInputs.forEach(maskPhoneInput);
    });
    </script>
</body>
</html> 