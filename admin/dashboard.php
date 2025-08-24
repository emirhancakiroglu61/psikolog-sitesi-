<?php
session_start();
require_once '../includes/config.php';
require_once 'csrf_middleware.php';

// Admin girişi kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Session timeout kontrolü (30 dakika)
$session_timeout = 30 * 60; // 30 dakika
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $session_timeout) {
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}

// Session'ı yenile
$_SESSION['login_time'] = time();

$pdo = getDBConnection();

// İstatistikler
$stmt = $pdo->query("SELECT COUNT(*) FROM blog");
$blog_count = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM beslenme_diyetetik");
$nutrition_count = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM appointments");
$appointment_count = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'");
$pending_appointments = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM messages");
$message_count = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM messages WHERE status = 'unread'");
$unread_messages = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Psikolog Merkezi</title>
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
                    <h1>🎛️ Admin Dashboard</h1>
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

            <!-- İstatistikler -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>📝 Blog Yazıları</h3>
                    <p><?php echo $blog_count; ?></p>
                </div>
                <div class="stat-card">
                    <h3>🥗 Beslenme Yazıları</h3>
                    <p><?php echo $nutrition_count; ?></p>
                </div>
                <div class="stat-card">
                    <h3>📅 Toplam Randevu</h3>
                    <p><?php echo $appointment_count; ?></p>
                </div>
                <div class="stat-card">
                    <h3>⏳ Bekleyen Randevu</h3>
                    <p><?php echo $pending_appointments; ?></p>
                </div>
                <div class="stat-card">
                    <h3>💬 Toplam Mesaj</h3>
                    <p><?php echo $message_count; ?></p>
                </div>
                <div class="stat-card">
                    <h3>📨 Okunmamış Mesaj</h3>
                    <p><?php echo $unread_messages; ?></p>
                </div>
            </div>

            <!-- Hızlı Erişim -->
            <div class="card">
                <h2 style="color: var(--white); font-size: 1.8rem; font-weight: 700; margin-bottom: 2rem; text-align: center;">🚀 Hızlı Erişim</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                    <div style="background: rgba(255, 255, 255, 0.05); border-radius: var(--border-radius); padding: 1.5rem; border: 1px solid rgba(255, 255, 255, 0.1);">
                        <h3 style="color: var(--primary-purple-light); font-size: 1.2rem; margin-bottom: 1rem;">📝 Blog Yönetimi</h3>
                        <p style="color: var(--gray-300); margin-bottom: 1.5rem;">Blog yazılarını ekleyin, düzenleyin ve yönetin.</p>
                        <a href="blog-ekle.php" class="btn">➕ Yeni Blog Ekle</a>
                        <a href="blog-yonetimi.php" class="btn btn-secondary" style="margin-left: 0.5rem;">📋 Tümünü Görüntüle</a>
                    </div>
                    
                    <div style="background: rgba(255, 255, 255, 0.05); border-radius: var(--border-radius); padding: 1.5rem; border: 1px solid rgba(255, 255, 255, 0.1);">
                        <h3 style="color: var(--primary-purple-light); font-size: 1.2rem; margin-bottom: 1rem;">📅 Randevu Yönetimi</h3>
                        <p style="color: var(--gray-300); margin-bottom: 1.5rem;">Gelen randevuları görüntüleyin ve durumlarını güncelleyin.</p>
                        <a href="randevu-yonetimi.php" class="btn">👁️ Randevuları Görüntüle</a>
                    </div>
                    
                    <div style="background: rgba(255, 255, 255, 0.05); border-radius: var(--border-radius); padding: 1.5rem; border: 1px solid rgba(255, 255, 255, 0.1);">
                        <h3 style="color: var(--primary-purple-light); font-size: 1.2rem; margin-bottom: 1rem;">💬 Mesaj Yönetimi</h3>
                        <p style="color: var(--gray-300); margin-bottom: 1.5rem;">Ziyaretçilerden gelen mesajları okuyun ve yanıtlayın.</p>
                        <a href="message-management.php" class="btn">📨 Mesajları Görüntüle</a>
                    </div>
                    
                    <div style="background: rgba(255, 255, 255, 0.05); border-radius: var(--border-radius); padding: 1.5rem; border: 1px solid rgba(255, 255, 255, 0.1);">
                        <h3 style="color: var(--primary-purple-light); font-size: 1.2rem; margin-bottom: 1rem;">⚙️ Profil Ayarları</h3>
                        <p style="color: var(--gray-300); margin-bottom: 1.5rem;">Hesap bilgilerinizi güncelleyin ve şifrenizi değiştirin.</p>
                        <a href="profile-settings.php" class="btn">🔧 Ayarları Düzenle</a>
                    </div>
                </div>
            </div>

            <!-- Son Aktiviteler -->
            <div class="card">
                <h2 style="color: var(--white); font-size: 1.8rem; font-weight: 700; margin-bottom: 2rem; text-align: center;">📊 Son Aktiviteler</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                    <div style="background: rgba(255, 255, 255, 0.05); border-radius: var(--border-radius); padding: 1.5rem; border: 1px solid rgba(255, 255, 255, 0.1);">
                        <h3 style="color: var(--success); font-size: 1.1rem; margin-bottom: 1rem;">✅ Sistem Durumu</h3>
                        <p style="color: var(--gray-300);">Tüm sistemler çalışıyor</p>
                        <p style="color: var(--gray-400); font-size: 0.9rem; margin-top: 0.5rem;">Son kontrol: <?php echo date('d.m.Y H:i'); ?></p>
                    </div>
                    
                    <div style="background: rgba(255, 255, 255, 0.05); border-radius: var(--border-radius); padding: 1.5rem; border: 1px solid rgba(255, 255, 255, 0.1);">
                        <h3 style="color: var(--warning); font-size: 1.1rem; margin-bottom: 1rem;">⏳ Bekleyen İşlemler</h3>
                        <p style="color: var(--gray-300);"><?php echo $pending_appointments; ?> bekleyen randevu</p>
                        <p style="color: var(--gray-300);"><?php echo $unread_messages; ?> okunmamış mesaj</p>
                    </div>
                    
                    <div style="background: rgba(255, 255, 255, 0.05); border-radius: var(--border-radius); padding: 1.5rem; border: 1px solid rgba(255, 255, 255, 0.1);">
                        <h3 style="color: var(--info); font-size: 1.1rem; margin-bottom: 1rem;">📈 İstatistikler</h3>
                        <p style="color: var(--gray-300);">Toplam <?php echo $blog_count + $nutrition_count; ?> yazı</p>
                        <p style="color: var(--gray-300);">Toplam <?php echo $appointment_count; ?> randevu</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Admin güvenlik sistemi
        class AdminSecurity {
            constructor() {
                this.csrfToken = '<?php echo generateCSRFToken(); ?>';
                this.lastActivity = Date.now();
                this.setupActivityTracking();
                this.preventRightClick();
                this.preventDevTools();
            }
            
            setupActivityTracking() {
                const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
                events.forEach(event => {
                    document.addEventListener(event, () => {
                        this.lastActivity = Date.now();
                    });
                });
                
                // Her dakika kontrol et
                setInterval(() => {
                    if (Date.now() - this.lastActivity > 5 * 60 * 1000) {
                        if (confirm('Uzun süredir hareketsizsiniz. Oturumunuzu yenilemek istiyor musunuz?')) {
                            this.refreshSession();
                        }
                    }
                }, 60000);
            }
            
            async refreshSession() {
                try {
                    const response = await fetch('auth_api.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': this.csrfToken
                        },
                        body: JSON.stringify({
                            action: 'refresh_session'
                        })
                    });
                    
                    if (!response.ok) {
                        this.logout();
                    }
                } catch (error) {
                    console.error('Session refresh failed:', error);
                    this.logout();
                }
            }
            
            async logout() {
                try {
                    const response = await fetch('logout.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': this.csrfToken
                        },
                        body: JSON.stringify({
                            csrf_token: this.csrfToken
                        })
                    });
                    
                    if (response.ok) {
                        window.location.href = 'login.php';
                    } else {
                        window.location.href = 'logout.php';
                    }
                } catch (error) {
                    console.error('Logout failed:', error);
                    window.location.href = 'logout.php';
                }
            }
            
            preventRightClick() {
                document.addEventListener('contextmenu', (e) => {
                    e.preventDefault();
                    return false;
                });
            }
            
            preventDevTools() {
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'F12' || 
                        (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J')) ||
                        (e.ctrlKey && e.key === 'u')) {
                        e.preventDefault();
                        return false;
                    }
                });
            }
        }
        
        // Güvenli logout fonksiyonu
        function secureLogout() {
            if (confirm('Çıkış yapmak istediğinizden emin misiniz?')) {
                window.location.href = 'logout.php';
            }
        }
        
        // Admin güvenlik sistemini başlat
        let adminSecurity;
        document.addEventListener('DOMContentLoaded', () => {
            adminSecurity = new AdminSecurity();
        });
        
        // Sayfa kapatılırken logout
        window.addEventListener('beforeunload', () => {
            if (typeof navigator.sendBeacon === 'function') {
                const data = new FormData();
                data.append('csrf_token', adminSecurity?.csrfToken || '');
                navigator.sendBeacon('logout.php', data);
            }
        });
    </script>
</body>
</html> 