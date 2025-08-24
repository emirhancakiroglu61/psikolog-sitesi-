<?php
require_once '../includes/config.php';

// Giriş kontrolü
requireAdmin();

// Veritabanı bağlantısı
$pdo = getDBConnection();

// Filtreleme
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$service_type_filter = isset($_GET['service_type']) ? $_GET['service_type'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Hizmet türlerini benzersiz olarak çek
$service_types = [];
$stmt_types = $pdo->query("SELECT DISTINCT service_type FROM appointments WHERE service_type IS NOT NULL AND service_type != ''");
while ($row = $stmt_types->fetch(PDO::FETCH_ASSOC)) {
    $service_types[] = $row['service_type'];
}

// Sayfalama
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// SQL sorgusu oluştur
$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "status = :status";
    $params[':status'] = $status_filter;
}

if ($date_filter) {
    $where_conditions[] = "DATE(preferred_date) = :date";
    $params[':date'] = $date_filter;
}

if ($service_type_filter) {
    $where_conditions[] = "service_type = :service_type";
    $params[':service_type'] = $service_type_filter;
}

if ($search) {
    $where_conditions[] = "(name LIKE :search OR national_id LIKE :search)";
    $params[':search'] = "%$search%";
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Toplam randevu sayısını al
$count_sql = "SELECT COUNT(*) FROM appointments " . $where_clause;
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_appointments = $stmt->fetchColumn();
$total_pages = ceil($total_appointments / $per_page);

// Randevuları al
$sql = "SELECT * FROM appointments " . $where_clause . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

// Tüm parametreleri bind et
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

// LIMIT ve OFFSET parametrelerini integer olarak bind et
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$appointments = $stmt->fetchAll();

// Durum istatistikleri
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM appointments GROUP BY status");
$status_stats = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Randevu Yönetimi - Admin Panel</title>
    <link rel="stylesheet" href="admin-styles.css">
    <style>
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-xl);
            table-layout: fixed;
            min-width: 1200px;
        }
        
        /* Responsive tasarım */
        @media (max-width: 1400px) {
            .admin-table {
                min-width: 1100px;
            }
            .admin-table th:nth-child(1), .admin-table td:nth-child(1) { width: 12%; }
            .admin-table th:nth-child(2), .admin-table td:nth-child(2) { width: 10%; }
            .admin-table th:nth-child(3), .admin-table td:nth-child(3) { width: 15%; }
            .admin-table th:nth-child(4), .admin-table td:nth-child(4) { width: 12%; }
            .admin-table th:nth-child(5), .admin-table td:nth-child(5) { width: 10%; }
            .admin-table th:nth-child(6), .admin-table td:nth-child(6) { width: 10%; }
            .admin-table th:nth-child(7), .admin-table td:nth-child(7) { width: 15%; }
            .admin-table th:nth-child(8), .admin-table td:nth-child(8) { width: 8%; }
            .admin-table th:nth-child(9), .admin-table td:nth-child(9) { width: 10%; }
            .admin-table th:nth-child(10), .admin-table td:nth-child(10) { width: 8%; }
        }
        
        @media (max-width: 1200px) {
            .admin-table {
                min-width: 1000px;
            }
            .admin-table th:nth-child(1), .admin-table td:nth-child(1) { width: 11%; }
            .admin-table th:nth-child(2), .admin-table td:nth-child(2) { width: 9%; }
            .admin-table th:nth-child(3), .admin-table td:nth-child(3) { width: 14%; }
            .admin-table th:nth-child(4), .admin-table td:nth-child(4) { width: 11%; }
            .admin-table th:nth-child(5), .admin-table td:nth-child(5) { width: 9%; }
            .admin-table th:nth-child(6), .admin-table td:nth-child(6) { width: 9%; }
            .admin-table th:nth-child(7), .admin-table td:nth-child(7) { width: 14%; }
            .admin-table th:nth-child(8), .admin-table td:nth-child(8) { width: 7%; }
            .admin-table th:nth-child(9), .admin-table td:nth-child(9) { width: 9%; }
            .admin-table th:nth-child(10), .admin-table td:nth-child(10) { width: 7%; }
        }
        
        @media (max-width: 1000px) {
            .admin-table {
                min-width: 900px;
            }
            .admin-table th:nth-child(1), .admin-table td:nth-child(1) { width: 10%; }
            .admin-table th:nth-child(2), .admin-table td:nth-child(2) { width: 8%; }
            .admin-table th:nth-child(3), .admin-table td:nth-child(3) { width: 13%; }
            .admin-table th:nth-child(4), .admin-table td:nth-child(4) { width: 10%; }
            .admin-table th:nth-child(5), .admin-table td:nth-child(5) { width: 8%; }
            .admin-table th:nth-child(6), .admin-table td:nth-child(6) { width: 8%; }
            .admin-table th:nth-child(7), .admin-table td:nth-child(7) { width: 13%; }
            .admin-table th:nth-child(8), .admin-table td:nth-child(8) { width: 6%; }
            .admin-table th:nth-child(9), .admin-table td:nth-child(9) { width: 8%; }
            .admin-table th:nth-child(10), .admin-table td:nth-child(10) { width: 6%; }
        }
        
        .appointments-table {
            width: 100%;
            overflow-x: auto;
            margin: 0;
            padding: 0;
        }
        
        body {
            overflow-x: auto;
        }
        .admin-table th,
        .admin-table td {
            padding: 1rem 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: var(--gray-300);
            word-break: break-word;
            vertical-align: middle;
            line-height: 1.2;
            height: auto;
        }
        
        .admin-table th {
            vertical-align: top;
            font-weight: 600;
            white-space: nowrap;
            background: rgba(124, 58, 237, 0.1);
            border-bottom: 2px solid rgba(124, 58, 237, 0.2);
        }
        
        @media (max-width: 1200px) {
            .admin-table th,
            .admin-table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.85rem;
            }
            .admin-table th {
                vertical-align: top;
                white-space: nowrap;
            }
        }
        
        @media (max-width: 1000px) {
            .admin-table th,
            .admin-table td {
                padding: 0.5rem 0.4rem;
                font-size: 0.8rem;
            }
            .admin-table th {
                vertical-align: top;
                white-space: nowrap;
                font-weight: 600;
            }
        }
        .admin-table th:nth-child(1), .admin-table td:nth-child(1) { width: 13%; } /* Ad Soyad */
        .admin-table th:nth-child(2), .admin-table td:nth-child(2) { width: 11%; } /* TC Kimlik */
        .admin-table th:nth-child(3), .admin-table td:nth-child(3) { width: 16%; } /* İletişim */
        .admin-table th:nth-child(4), .admin-table td:nth-child(4) { width: 13%; } /* Hizmet Türü */
        .admin-table th:nth-child(5), .admin-table td:nth-child(5) { width: 11%; } /* Randevu Tarihi */
        .admin-table th:nth-child(6), .admin-table td:nth-child(6) { width: 11%; } /* Randevu Saati */
        .admin-table th:nth-child(7), .admin-table td:nth-child(7) { width: 16%; } /* Mesaj */
        .admin-table th:nth-child(8), .admin-table td:nth-child(8) { width: 9%; }  /* Durum */
        .admin-table th:nth-child(9), .admin-table td:nth-child(9) { width: 11%; } /* Kayıt Tarihi */
        .admin-table th:nth-child(10), .admin-table td:nth-child(10) { width: 9%; } /* İşlemler */
        
        .message-preview {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: help;
        }
        
        @media (max-width: 1400px) {
            .message-preview {
                max-width: 200px;
            }
        }
        
        @media (max-width: 1200px) {
            .message-preview {
                max-width: 150px;
            }
        }
        
        @media (max-width: 1000px) {
            .message-preview {
                max-width: 120px;
            }
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
            text-align: center;
            display: inline-block;
            min-width: 80px;
        }
        .status-pending { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }
        .status-approved { background: rgba(34, 197, 94, 0.2); color: #22c55e; }
        .status-rejected { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
        .status-cancelled { background: rgba(107, 114, 128, 0.2); color: #6b7280; }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
        }
        
        .action-btn {
            padding: 4px 8px;
            border: none;
            border-radius: 4px;
            font-size: 0.8rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-detail { background: #3b82f6; color: white; }
        .btn-approve { background: #22c55e; color: white; }
        .btn-reject { background: #ef4444; color: white; }
        .btn-cancel { background: #6b7280; color: white; }
        
        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        /* Loading Animation Styles */
        .loading-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            backdrop-filter: blur(5px);
        }
        
        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        .loading-text {
            color: white;
            font-size: 18px;
            font-weight: 600;
        }
        
        /* Arama çubuğu stilleri */
        .search-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius-lg);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-xl);
        }
        .search-form {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .search-input {
            flex: 1;
            min-width: 200px;
            padding: 12px 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            color: var(--white);
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .search-input:focus {
            outline: none;
            border-color: var(--primary-purple-light);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }
        .search-input::placeholder {
            color: var(--gray-400);
        }
        .search-btn {
            background: var(--primary-purple-light);
            color: var(--white);
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .search-btn:hover {
            background: var(--primary-purple-dark);
            transform: translateY(-1px);
        }
        .clear-btn {
            background: var(--gray-600);
            color: var(--white);
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .clear-btn:hover {
            background: var(--gray-700);
            transform: translateY(-1px);
        }
        .search-info {
            margin-top: 10px;
            color: var(--gray-300);
            font-size: 0.9rem;
        }
        .status-filter {
            padding: 12px 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            color: var(--white);
            font-size: 1rem;
            transition: all 0.3s ease;
            min-width: 150px;
        }
        .status-filter:focus {
            outline: none;
            border-color: var(--primary-purple-light);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }
        .status-filter option {
            background: var(--black-soft);
            color: var(--white);
        }
        
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
            text-align: center;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Message Popup Styles */
        .message-popup {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            animation: slideIn 0.3s ease-out;
        }
        
        .message-popup.success {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .message-popup.error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        .message-content {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            color: white;
            min-width: 300px;
        }
        
        .message-icon {
            font-size: 24px;
            margin-right: 15px;
        }
        
        .message-text {
            font-weight: 600;
            font-size: 14px;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>

</head>
<body>
    <div class="admin-container" style="overflow-x: auto; width: 100%;">
        <div class="container" style="max-width: 95%; width: 95%; padding: 0 1rem; margin: 0 auto;">
            <!-- Admin Header -->
            <div class="admin-header" style="width: 100%;">
                <div class="admin-header-content">
                    <h1>📅 Randevu Yönetimi</h1>
                    <div class="admin-user-info">
                        <span><?php echo htmlspecialchars($_SESSION['admin_first_name'] . ' ' . $_SESSION['admin_last_name']); ?></span>
                        <a href="logout.php" class="admin-logout-btn">🚪 Çıkış Yap</a>
                    </div>
                </div>
            </div>

        <!-- Admin Navigation -->
        <div class="admin-nav" style="width: 100%;">
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="blog-yonetimi.php">📝 Blog Yönetimi</a>
            <a href="beslenme-yonetimi.php">🥗 Beslenme Yönetimi</a>
            <a href="randevu-yonetimi.php">📅 Randevu Yönetimi</a>
            <a href="message-management.php">💬 Mesaj Yönetimi</a>
            <a href="patients-management.php">👤 Danışan Yönetimi</a>
            <a href="profile-settings.php">⚙️ Profil Ayarları</a>
        </div>

        <!-- Durum İstatistikleri -->
        <div class="stats-grid">
            <?php foreach ($status_stats as $stat): ?>
            <div class="stat-card">
                <h3><?php 
                    $status_text = [
                        'pending' => 'Beklemede',
                        'approved' => 'Onaylandı',
                        'rejected' => 'Reddedildi',
                        'cancelled' => 'İptal Edildi'
                    ];
                    echo htmlspecialchars($status_text[$stat['status']] ?? $stat['status']); 
                ?></h3>
                <p><?php echo $stat['count']; ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Arama ve Filtreleme Çubuğu -->
        <div class="search-container">
            <form method="GET" class="search-form">
                <input type="text" name="search" class="search-input" 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Ad, soyad veya TC kimlik ile arama...">
                
                <select name="status" class="status-filter">
                    <option value="">Tüm Durumlar</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Beklemede</option>
                    <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Onaylandı</option>
                    <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Reddedildi</option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>İptal Edildi</option>
                </select>
                
                <input type="date" name="date" class="search-input" value="<?php echo htmlspecialchars($date_filter); ?>">
                
                <select name="service_type" class="status-filter">
                    <option value="">Tüm Hizmet Türleri</option>
                    <?php foreach ($service_types as $stype): ?>
                        <option value="<?php echo htmlspecialchars($stype); ?>" <?php echo $service_type_filter === $stype ? 'selected' : ''; ?>><?php echo htmlspecialchars($stype); ?></option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="search-btn">🔍 Filtrele</button>
                <?php if (!empty($status_filter) || !empty($date_filter) || !empty($service_type_filter) || !empty($search)): ?>
                    <a href="randevu-yonetimi.php" class="clear-btn">❌ Temizle</a>
                <?php endif; ?>
            </form>
            <?php if (!empty($status_filter) || !empty($date_filter) || !empty($service_type_filter) || !empty($search)): ?>
                <div class="search-info">
                    <?php
                    $filter_text = [];
                    if (!empty($search)) $filter_text[] = '"' . htmlspecialchars($search) . '" araması';
                    if ($status_filter === 'pending') $filter_text[] = 'Beklemede randevular';
                    if ($status_filter === 'approved') $filter_text[] = 'Onaylanmış randevular';
                    if ($status_filter === 'rejected') $filter_text[] = 'Reddedilmiş randevular';
                    if ($status_filter === 'cancelled') $filter_text[] = 'İptal edilmiş randevular';
                    if (!empty($date_filter)) $filter_text[] = $date_filter . ' tarihli randevular';
                    if (!empty($service_type_filter)) $filter_text[] = '"' . htmlspecialchars($service_type_filter) . '" hizmeti';
                    ?>
                    <?= implode(' + ', $filter_text) ?> için <?= count($appointments) ?> sonuç bulundu.
                </div>
            <?php endif; ?>
        </div>

        <!-- Başlık -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; width: 100%;">
            <h2 style="color: white; font-size: 1.8rem; font-weight: 700; margin: 0;">📋 Randevular (Toplam: <?php echo $total_appointments; ?>)</h2>
        </div>
        
        <!-- Bilgi Kutusu -->
        <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 8px; padding: 1rem; margin-bottom: 2rem; color: #3b82f6;">
            <strong>ℹ️ Sistem Bilgisi:</strong> Aynı TC kimlik numarası ile birden fazla aktif randevu alınması engellenmiştir. Sadece "Beklemede" ve "Onaylandı" durumundaki randevular kontrol edilir.
        </div>

        <?php if (empty($appointments)): ?>
        <div class="no-appointments">
            <h3>📭 Randevu bulunamadı</h3>
            <p>Seçilen kriterlere uygun randevu bulunmuyor.</p>
        </div>
        <?php else: ?>
        
        <!-- Randevu Tablosu -->
        <div class="appointments-table" style="width: 100%; overflow-x: auto;">
            <div style="width: 100%;">
                <table class="admin-table" style="width: 100%; min-width: 1200px;">
                    <thead>
                        <tr>
                            <th>Ad Soyad</th>
                            <th>TC Kimlik</th>
                            <th>İletişim</th>
                            <th>Hizmet Türü</th>
                            <th>Randevu Tarihi</th>
                            <th>Randevu Saati</th>
                            <th>Mesaj</th>
                            <th>Durum</th>
                            <th>Kayıt Tarihi</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($appointment['name']); ?></strong>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($appointment['national_id'] ?? 'Belirtilmemiş'); ?></div>
                            </td>
                            <td>
                                <div><strong>📧</strong> <?php echo htmlspecialchars($appointment['email']); ?></div>
                                <div><strong>📞</strong> <?php echo htmlspecialchars($appointment['phone']); ?></div>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($appointment['service_type']); ?></div>
                            </td>
                            <td>
                                <div><?php echo date('d.m.Y', strtotime($appointment['preferred_date'])); ?></div>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($appointment['appointment_time'] ?? 'Belirtilmemiş'); ?></div>
                            </td>
                            <td>
                                <div class="message-preview" title="<?php echo htmlspecialchars($appointment['message']); ?>">
                                    <?php
                                    $short_message = mb_substr($appointment['message'], 0, 50, 'UTF-8');
                                    $isLong = mb_strlen($appointment['message'], 'UTF-8') > 50;
                                    echo htmlspecialchars($short_message);
                                    if ($isLong) echo '...';
                                    ?>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $appointment['status'])); ?>">
                                    <?php 
                                    $status_text = [
                                        'pending' => 'Beklemede',
                                        'approved' => 'Onaylandı',
                                        'rejected' => 'Reddedildi',
                                        'cancelled' => 'İptal Edildi'
                                    ];
                                    echo htmlspecialchars($status_text[$appointment['status']] ?? $appointment['status']); 
                                    ?>
                                </span>
                            </td>
                            <td><?php echo date('d.m.Y H:i', strtotime($appointment['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="randevu-detay.php?id=<?php echo $appointment['id']; ?>" 
                                       class="action-btn btn-detail">👁️ Detay</a>
                                    
                                    <?php if ($appointment['status'] === 'pending'): ?>
                                    <button onclick="updateAppointmentStatus(<?php echo $appointment['id']; ?>, 'approved')" 
                                            class="action-btn btn-approve">✅ Onayla</button>
                                    <button onclick="updateAppointmentStatus(<?php echo $appointment['id']; ?>, 'rejected')" 
                                            class="action-btn btn-reject">❌ Reddet</button>
                                    <button onclick="updateAppointmentStatus(<?php echo $appointment['id']; ?>, 'cancelled')" 
                                            class="action-btn btn-cancel">🚫 İptal Et</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sayfalama -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination" style="width: 100%;">
            <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($status_filter); ?>&date=<?php echo urlencode($date_filter); ?>&service_type=<?php echo urlencode($service_type_filter); ?>&search=<?php echo urlencode($search); ?>" 
               class="btn btn-secondary">⬅️ Önceki</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&date=<?php echo urlencode($date_filter); ?>&service_type=<?php echo urlencode($service_type_filter); ?>&search=<?php echo urlencode($search); ?>" 
               class="btn <?php echo $i == $page ? '' : 'btn-secondary'; ?>" style="min-width: 40px;"><?php echo $i; ?></a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($status_filter); ?>&date=<?php echo urlencode($date_filter); ?>&service_type=<?php echo urlencode($service_type_filter); ?>&search=<?php echo urlencode($search); ?>" 
               class="btn btn-secondary">Sonraki ➡️</a>
            <?php endif; ?>
        </div>
        
        <div class="pagination-info" style="width: 100%; text-align: center;">
            Sayfa <?php echo $page; ?> / <?php echo $total_pages; ?> 
            (Toplam <?php echo $total_appointments; ?> randevu)
        </div>
        <?php endif; ?>
        
        <?php endif; ?>
    </div>

    <script>
        function updateAppointmentStatus(id, status) {
            if (confirm('Randevu durumunu güncellemek istediğinizden emin misiniz?')) {
                // Bekleme animasyonunu göster
                showLoadingAnimation(id, status);
                
                const formData = new FormData();
                formData.append('id', id);
                formData.append('status', status);
                formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');
                
                fetch('update_appointment_status.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Bekleme animasyonunu gizle
                    hideLoadingAnimation(id);
                    
                    if (data.success) {
                        showSuccessMessage('Randevu durumu başarıyla güncellendi ve mail gönderildi!');
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else {
                        showErrorMessage('Hata: ' + data.message);
                    }
                })
                .catch(error => {
                    // Bekleme animasyonunu gizle
                    hideLoadingAnimation(id);
                    console.error('Error:', error);
                    showErrorMessage('Bir hata oluştu.');
                });
            }
        }
        
        function showLoadingAnimation(id, status) {
            // Tüm butonları devre dışı bırak
            const buttons = document.querySelectorAll(`button[onclick*="${id}"]`);
            buttons.forEach(btn => {
                btn.disabled = true;
                btn.style.opacity = '0.5';
                btn.style.cursor = 'not-allowed';
            });
            
            // Bekleme mesajını göster
            const statusText = {
                'approved': 'Onaylanıyor',
                'rejected': 'Reddediliyor',
                'cancelled': 'İptal Ediliyor'
            };
            
            const loadingDiv = document.createElement('div');
            loadingDiv.id = `loading-${id}`;
            loadingDiv.className = 'loading-animation';
            loadingDiv.innerHTML = `
                <div class="loading-spinner"></div>
                <div class="loading-text">${statusText[status]} ve mail gönderiliyor...</div>
            `;
            
            // Loading div'ini sayfaya ekle
            document.body.appendChild(loadingDiv);
        }
        
        function hideLoadingAnimation(id) {
            // Butonları tekrar aktif et
            const buttons = document.querySelectorAll(`button[onclick*="${id}"]`);
            buttons.forEach(btn => {
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.style.cursor = 'pointer';
            });
            
            // Loading div'ini kaldır
            const loadingDiv = document.getElementById(`loading-${id}`);
            if (loadingDiv) {
                loadingDiv.remove();
            }
        }
        
        function showSuccessMessage(message) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message-popup success';
            messageDiv.innerHTML = `
                <div class="message-content">
                    <div class="message-icon">✅</div>
                    <div class="message-text">${message}</div>
                </div>
            `;
            document.body.appendChild(messageDiv);
            
            setTimeout(() => {
                messageDiv.remove();
            }, 3000);
        }
        
        function showErrorMessage(message) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message-popup error';
            messageDiv.innerHTML = `
                <div class="message-content">
                    <div class="message-icon">❌</div>
                    <div class="message-text">${message}</div>
                </div>
            `;
            document.body.appendChild(messageDiv);
            
            setTimeout(() => {
                messageDiv.remove();
            }, 4000);
        }
    </script>
</body>
</html> 