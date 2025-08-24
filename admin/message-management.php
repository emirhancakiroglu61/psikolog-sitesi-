<?php
session_start();
require_once '../includes/config.php';
require_once 'csrf_middleware.php';

// Admin girişi kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$pdo = getDBConnection();

// Sayfalama
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Filtreleme
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Toplam mesaj sayısı
$count_sql = "SELECT COUNT(*) FROM messages";
$count_params = [];

if ($status_filter) {
    $count_sql .= " WHERE status = ?";
    $count_params[] = $status_filter;
}

if ($search) {
    $count_sql .= $status_filter ? " AND" : " WHERE";
    $count_sql .= " (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $search_param = "%$search%";
    $count_params = array_merge($count_params, [$search_param, $search_param, $search_param, $search_param]);
}

$stmt = $pdo->prepare($count_sql);
$stmt->execute($count_params);
$total_messages = $stmt->fetchColumn();
$total_pages = ceil($total_messages / $per_page);

// Mesajları getir
$sql = "SELECT * FROM messages";
$params = [];

if ($status_filter || $search) {
    $sql .= " WHERE";
    $conditions = [];
    
    if ($status_filter) {
        $conditions[] = "status = ?";
        $params[] = $status_filter;
    }
    
    if ($search) {
        $conditions[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    }
    
    $sql .= " " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

$stmt = $pdo->prepare($sql);

// Parametreleri doğru tiplerle bağla
$paramIndex = 1;
foreach ($params as $param) {
    if (is_int($param)) {
        $stmt->bindValue($paramIndex, $param, PDO::PARAM_INT);
    } else {
        $stmt->bindValue($paramIndex, $param, PDO::PARAM_STR);
    }
    $paramIndex++;
}

$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Durum güncelleme
if (isset($_POST['update_status']) && validateCSRFToken($_POST['csrf_token'])) {
    $message_id = (int)$_POST['message_id'];
    $new_status = $_POST['new_status'];
    
    $stmt = $pdo->prepare("UPDATE messages SET status = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $message_id])) {
        // Filtreleme parametrelerini koru
        $redirect_url = 'message-management.php?success=1';
        if (!empty($status_filter)) {
            $redirect_url .= '&status=' . urlencode($status_filter);
        }
        if (!empty($search)) {
            $redirect_url .= '&search=' . urlencode($search);
        }
        header('Location: ' . $redirect_url);
        exit;
    } else {
        $error = 'Mesaj durumu güncellenirken bir hata oluştu.';
    }
}

// Mesaj silme
if (isset($_POST['delete_message']) && validateCSRFToken($_POST['csrf_token'])) {
    $message_id = (int)$_POST['message_id'];
    
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    if ($stmt->execute([$message_id])) {
        // Filtreleme parametrelerini koru
        $redirect_url = 'message-management.php?deleted=1';
        if (!empty($status_filter)) {
            $redirect_url .= '&status=' . urlencode($status_filter);
        }
        if (!empty($search)) {
            $redirect_url .= '&search=' . urlencode($search);
        }
        header('Location: ' . $redirect_url);
        exit;
    } else {
        $error = 'Mesaj silinirken bir hata oluştu.';
    }
}

// Cevapla formu submit edildiğinde mail gönder
if (isset($_POST['reply_message']) && validateCSRFToken($_POST['csrf_token'])) {
    $reply_to = trim($_POST['reply_to'] ?? '');
    $reply_subject = trim($_POST['reply_subject'] ?? '');
    $reply_body = trim($_POST['reply_body'] ?? '');
    $reply_error = '';
    $reply_success = '';
    if (!filter_var($reply_to, FILTER_VALIDATE_EMAIL)) {
        $reply_error = 'Geçersiz e-posta adresi.';
    } elseif (empty($reply_subject) || empty($reply_body)) {
        $reply_error = 'Konu ve mesaj alanı boş olamaz.';
    } else {
        $mailResult = sendMailSMTP($reply_to, $reply_subject, $reply_body);
        if ($mailResult === true) {
            $reply_success = 'Cevabınız başarıyla gönderildi!';
            // Mesajı replied olarak işaretle
            if (isset($_POST['reply_to']) && isset($_POST['reply_subject'])) {
                $stmt = $pdo->prepare("UPDATE messages SET status = 'replied' WHERE email = ? AND subject = ?");
                $stmt->execute([$reply_to, $reply_subject]);
            }
        } else {
            $reply_error = 'Mail gönderilemedi: ' . htmlspecialchars($mailResult);
        }
    }
}

// İstatistikler
$stmt = $pdo->query("SELECT COUNT(*) FROM messages WHERE status = 'unread'");
$unread_count = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM messages WHERE status = 'read'");
$read_count = $stmt->fetchColumn();

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesaj Yönetimi - Admin Panel</title>
    <link rel="stylesheet" href="admin-styles.css">
    <style>
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
            min-width: 250px;
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

        /* Tablo küçük ekranlarda yatay kaydırılabilir olsun */
        .admin-table {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-xl);
            table-layout: fixed;
        }
        .admin-table table {
            min-width: 900px; /* Kolonlar daralmadan kaydırma sağla */
            width: 100%;
            border-collapse: collapse;
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
        .scroll-hint {
            display: none;
            color: var(--gray-300);
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
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
        
        @media (max-width: 992px) {
            .scroll-hint { display: block; }
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
        
        @media (max-width: 768px) {
            .admin-table th,
            .admin-table td {
                padding: 0.4rem 0.3rem;
                font-size: 0.75rem;
            }
            .admin-table th {
                vertical-align: top;
                white-space: nowrap;
                font-weight: 600;
            }
        }
        
        @media (max-width: 576px) {
            .admin-table th,
            .admin-table td {
                padding: 0.3rem 0.2rem;
                font-size: 0.7rem;
            }
            .admin-table th {
                vertical-align: top;
                white-space: nowrap;
                font-weight: 600;
            }
        }
        
        @media (max-width: 480px) {
            .admin-table th,
            .admin-table td {
                padding: 0.25rem 0.15rem;
                font-size: 0.65rem;
            }
            .admin-table th {
                vertical-align: top;
                white-space: nowrap;
                font-weight: 600;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="container">
            <!-- Admin Header -->
            <div class="admin-header">
                <div class="admin-header-content">
                    <h1>💬 Mesaj Yönetimi</h1>
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

            <?php if (isset($_GET['success'])): ?>
            <div class="message success">
                ✅ Mesaj durumu başarıyla güncellendi!
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['deleted'])): ?>
            <div class="message success">
                ✅ Mesaj başarıyla silindi!
            </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
            <div class="message error">
                ❌ <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($reply_success)): ?>
            <div class="message success">✅ <?php echo $reply_success; ?></div>
            <?php endif; ?>
            <?php if (!empty($reply_error)): ?>
            <div class="message error">❌ <?php echo $reply_error; ?></div>
            <?php endif; ?>

            <!-- İstatistikler -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>📨 Toplam Mesaj</h3>
                    <p><?php echo $total_messages; ?></p>
                </div>
                <div class="stat-card">
                    <h3>📭 Okunmamış</h3>
                    <p><?php echo $unread_count; ?></p>
                </div>
                <div class="stat-card">
                    <h3>📖 Okunmuş</h3>
                    <p><?php echo $read_count; ?></p>
                </div>
            </div>

            <!-- Arama ve Filtreleme Çubuğu -->
            <div class="search-container">
                <form method="GET" class="search-form">
                    <input type="text" name="search" class="search-input" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="İsim, email, konu veya mesaj içinde ara...">
                    
                    <select name="status" class="status-filter">
                        <option value="">Tüm Durumlar</option>
                        <option value="unread" <?php echo $status_filter === 'unread' ? 'selected' : ''; ?>>Okunmamış</option>
                        <option value="read" <?php echo $status_filter === 'read' ? 'selected' : ''; ?>>Okunmuş</option>
                    </select>
                    
                    <button type="submit" class="search-btn">🔍 Filtrele</button>
                    <?php if (!empty($search) || !empty($status_filter)): ?>
                        <a href="message-management.php" class="clear-btn">❌ Temizle</a>
                    <?php endif; ?>
                </form>
                <?php if (!empty($search) || !empty($status_filter)): ?>
                    <div class="search-info">
                        <?php
                        $filter_text = [];
                        if (!empty($search)) $filter_text[] = '"' . htmlspecialchars($search) . '" araması';
                        if ($status_filter === 'unread') $filter_text[] = 'Okunmamış mesajlar';
                        if ($status_filter === 'read') $filter_text[] = 'Okunmuş mesajlar';
                        ?>
                        <?= implode(' + ', $filter_text) ?> için <?= count($messages) ?> sonuç bulundu.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Mesaj Listesi -->
            <div class="card">
                <h2 style="color: var(--white); font-size: 1.8rem; font-weight: 700; margin-bottom: 2rem;">📋 Mesajlar</h2>

                <?php if (empty($messages)): ?>
                <div style="text-align: center; padding: 3rem 2rem; color: var(--gray-300);">
                    <h3 style="color: var(--white); font-size: 1.5rem; margin-bottom: 1rem;">📭 Mesaj bulunamadı</h3>
                    <p>Seçilen kriterlere uygun mesaj bulunmuyor.</p>
                </div>
                <?php else: ?>
                <div class="scroll-hint">👉 Tabloda tüm sütunları görmek için sağa/sola kaydırın.</div>
                <div class="admin-table">
                    <table>
                        <thead>
                            <tr>
                                <th>👤 Gönderen</th>
                                <th>📧 Email</th>
                                <th>📝 Konu</th>
                                <th>📅 Tarih</th>
                                <th>📊 Durum</th>
                                <th>🔧 İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $message): ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--white);"><?php echo htmlspecialchars($message['name']); ?></strong>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($message['email']); ?>
                                </td>
                                <td>
                                    <strong style="color: var(--white);"><?php echo htmlspecialchars($message['subject']); ?></strong>
                                    <br>
                                    <small style="color: var(--gray-400);">
                                        <?php echo substr(strip_tags($message['message']), 0, 100) . '...'; ?>
                                    </small>
                                </td>
                                <td>
                                    <?php echo date('d.m.Y H:i', strtotime($message['created_at'])); ?>
                                </td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    $status_text = '';
                                    switch ($message['status']) {
                                        case 'unread':
                                            $status_class = 'status-pending';
                                            $status_text = '📭 Okunmamış';
                                            break;
                                        case 'read':
                                            $status_class = 'status-approved';
                                            $status_text = '📖 Okunmuş';
                                            break;
                                        case 'replied':
                                            $status_class = 'status-success';
                                            $status_text = '✅ Cevaplandı';
                                            break;
                                        default:
                                            $status_class = 'status-cancelled';
                                            $status_text = '❓ Bilinmiyor';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button onclick="viewMessage(<?php echo $message['id']; ?>)" 
                                                class="action-btn btn-detail">👁️ Görüntüle</button>
                                        
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                            <select name="new_status" onchange="this.form.submit()" style="margin: 0 0.5rem; padding: 0.25rem; border-radius: 4px; background: var(--black-soft); color: var(--white); border: 1px solid var(--gray-600);">
                                                <option value="unread" <?php echo $message['status'] === 'unread' ? 'selected' : ''; ?>>📭 Okunmamış</option>
                                                <option value="read" <?php echo $message['status'] === 'read' ? 'selected' : ''; ?>>📖 Okunmuş</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                        
                                        <?php if ($message['status'] === 'read'): ?>
                                        <button type="button" class="action-btn btn-approve" onclick="openReplyModal('<?php echo htmlspecialchars($message['email']); ?>', '<?php echo htmlspecialchars($message['subject']); ?>')">✉️ Cevapla</button>
                                        <?php endif; ?>
                                        
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Bu mesajı silmek istediğinizden emin misiniz?')">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                            <button type="submit" name="delete_message" class="action-btn btn-reject">🗑️ Sil</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Sayfalama -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>">⬅️ Önceki</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>" 
                       style="<?php echo $i == $page ? 'background: var(--primary-purple); color: var(--white);' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>">Sonraki ➡️</a>
                    <?php endif; ?>
                </div>
                
                <div class="pagination-info">
                    Sayfa <?php echo $page; ?> / <?php echo $total_pages; ?> 
                    (Toplam <?php echo $total_messages; ?> mesaj)
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Mesaj Detay Modal -->
    <div id="messageModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; backdrop-filter: blur(5px);">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: var(--black-soft); border-radius: var(--border-radius-lg); padding: 2rem; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; border: 1px solid var(--gray-600);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="color: var(--white); margin: 0;">💬 Mesaj Detayı</h3>
                <button onclick="closeMessageModal()" style="background: none; border: none; color: var(--gray-400); font-size: 1.5rem; cursor: pointer;">✕</button>
            </div>
            <div id="messageContent"></div>
        </div>
    </div>

    <!-- Cevapla Modal -->
    <div id="replyModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:2000;">
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:var(--black-soft); border-radius:var(--border-radius-lg); padding:2rem; max-width:500px; width:90%; border:1px solid var(--gray-600);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                <h3 style="color:var(--white); margin:0;">✉️ Mesajı Cevapla</h3>
                <button onclick="closeReplyModal()" style="background:none; border:none; color:var(--gray-400); font-size:1.5rem; cursor:pointer;">✕</button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" id="reply_to" name="reply_to" value="">
                <div class="form-group">
                    <label for="reply_subject">Konu</label>
                    <input type="text" id="reply_subject" name="reply_subject" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="reply_body">Mesajınız</label>
                    <textarea id="reply_body" name="reply_body" class="form-control" rows="5" required></textarea>
                </div>
                <div class="form-group" style="text-align:right;">
                    <button type="submit" name="reply_message" class="btn btn-approve">Gönder</button>
                    <button type="button" class="btn btn-secondary" onclick="closeReplyModal()">İptal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function viewMessage(messageId) {
            // Mesaj detayını AJAX ile getir
            fetch(`get_message.php?id=${messageId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const message = data.message;
                        document.getElementById('messageContent').innerHTML = `
                            <div style="margin-bottom: 1rem;">
                                <strong style="color: var(--primary-purple-light);">👤 Gönderen:</strong> 
                                <span style="color: var(--gray-300);">${message.name}</span>
                            </div>
                            <div style="margin-bottom: 1rem;">
                                <strong style="color: var(--primary-purple-light);">📧 Email:</strong> 
                                <span style="color: var(--gray-300);">${message.email}</span>
                            </div>
                            <div style="margin-bottom: 1rem;">
                                <strong style="color: var(--primary-purple-light);">📝 Konu:</strong> 
                                <span style="color: var(--gray-300);">${message.subject}</span>
                            </div>
                            <div style="margin-bottom: 1rem;">
                                <strong style="color: var(--primary-purple-light);">📅 Tarih:</strong> 
                                <span style="color: var(--gray-300);">${message.created_at}</span>
                            </div>
                            <div style="margin-bottom: 1rem;">
                                <strong style="color: var(--primary-purple-light);">💬 Mesaj:</strong>
                            </div>
                            <div style="background: rgba(255,255,255,0.05); padding: 1rem; border-radius: var(--border-radius); color: var(--gray-300); line-height: 1.6;">
                                ${message.message.replace(/\n/g, '<br>')}
                            </div>
                        `;
                        document.getElementById('messageModal').style.display = 'block';
                    } else {
                        alert('Mesaj yüklenirken bir hata oluştu.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Mesaj yüklenirken bir hata oluştu.');
                });
        }

        function closeMessageModal() {
            document.getElementById('messageModal').style.display = 'none';
        }

        // Modal dışına tıklandığında kapat
        document.getElementById('messageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeMessageModal();
            }
        });

        function openReplyModal(email, subject) {
            document.getElementById('replyModal').style.display = 'block';
            document.getElementById('reply_to').value = email;
            document.getElementById('reply_subject').value = 'Re: ' + subject;
            document.getElementById('reply_body').value = '';
        }
        function closeReplyModal() {
            document.getElementById('replyModal').style.display = 'none';
        }

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
        
        // Admin güvenlik sistemini başlat
        let adminSecurity;
        document.addEventListener('DOMContentLoaded', () => {
            adminSecurity = new AdminSecurity();
        });
    </script>
</body>
</html> 