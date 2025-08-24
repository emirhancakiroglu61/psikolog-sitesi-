<?php
require_once '../includes/config.php';
requireAdmin();

$pdo = getDBConnection();
$appointment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$appointment = null;

// Silme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_appointment']) && isset($_POST['csrf_token']) && validateCSRFToken($_POST['csrf_token'])) {
    $delete_id = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;
    if ($delete_id) {
        $stmt = $pdo->prepare('DELETE FROM appointments WHERE id = ?');
        $stmt->execute([$delete_id]);
        header('Location: randevu-yonetimi.php?deleted=1');
        exit;
    }
}

if ($appointment_id) {
    $stmt = $pdo->prepare('SELECT * FROM appointments WHERE id = ?');
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
}

$status_text = [
    'pending' => 'Beklemede',
    'approved' => 'Onaylandı',
    'rejected' => 'Reddedildi',
    'cancelled' => 'İptal Edildi'
];
$status_badge = [
    'pending' => 'status-pending',
    'approved' => 'status-approved',
    'rejected' => 'status-rejected',
    'cancelled' => 'status-cancelled'
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Randevu Detayı - Admin Panel</title>
    <link rel="stylesheet" href="admin-styles.css">
    <style>
        .status-badge {
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
            text-align: center;
            display: inline-block;
            min-width: 100px;
        }
        .status-pending { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }
        .status-approved { background: rgba(34, 197, 94, 0.2); color: #22c55e; }
        .status-rejected { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
        .status-cancelled { background: rgba(107, 114, 128, 0.2); color: #6b7280; }
        
        .btn-reject {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .btn-reject:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="container" style="max-width: 600px; margin: 0 auto;">
            <div class="admin-header" style="margin-bottom: 2rem;">
                <div class="admin-header-content">
                    <h1>👁️ Randevu Detayı</h1>
                    <div class="admin-user-info">
                    </div>
                </div>
            </div>
            <div class="card" style="padding: 2.5rem 2rem;">
                <?php if ($appointment): ?>
                    <h2 style="color: var(--primary-purple-light); font-size: 1.5rem; font-weight: 700; margin-bottom: 2rem; text-align:center;">#<?php echo $appointment['id']; ?> - <?php echo htmlspecialchars($appointment['name']); ?></h2>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem; margin-bottom: 1.5rem;">
                        <div>
                            <strong style="color: var(--primary-purple-light);">Ad Soyad:</strong><br>
                            <span style="color: var(--gray-300);"><?php echo htmlspecialchars($appointment['name']); ?></span>
                        </div>
                        <div>
                            <strong style="color: var(--primary-purple-light);">TC Kimlik No:</strong><br>
                            <span style="color: var(--gray-300);">
                                <?php echo $appointment['national_id'] ? htmlspecialchars($appointment['national_id']) : '<span style=\'color:var(--gray-500);\'>Belirtilmemiş</span>'; ?>
                            </span>
                        </div>
                        <div>
                            <strong style="color: var(--primary-purple-light);">Telefon:</strong><br>
                            <span style="color: var(--gray-300);"><?php echo htmlspecialchars($appointment['phone']); ?></span>
                        </div>
                        <div>
                            <strong style="color: var(--primary-purple-light);">E-posta:</strong><br>
                            <span style="color: var(--gray-300);">
                                <?php echo $appointment['email'] ? htmlspecialchars($appointment['email']) : '<span style=\'color:var(--gray-500);\'>-</span>'; ?>
                            </span>
                        </div>
                        <div>
                            <strong style="color: var(--primary-purple-light);">Hizmet Türü:</strong><br>
                            <span style="color: var(--gray-300);"><?php echo htmlspecialchars($appointment['service_type']); ?></span>
                        </div>
                        <div>
                            <strong style="color: var(--primary-purple-light);">Tercih Edilen Tarih:</strong><br>
                            <span style="color: var(--gray-300);"><?php echo date('d.m.Y', strtotime($appointment['preferred_date'])); ?></span>
                        </div>
                        <div>
                            <strong style="color: var(--primary-purple-light);">Randevu Saati:</strong><br>
                            <span style="color: var(--gray-300);">
                                <?php echo $appointment['appointment_time'] ? htmlspecialchars($appointment['appointment_time']) : '<span style=\'color:var(--gray-500);\'>Belirtilmemiş</span>'; ?>
                            </span>
                        </div>
                        <div>
                            <strong style="color: var(--primary-purple-light);">Durum:</strong><br>
                            <span class="status-badge <?php echo $status_badge[$appointment['status']] ?? ''; ?>">
                                <?php echo $status_text[$appointment['status']] ?? $appointment['status']; ?>
                            </span>
                        </div>
                        <div>
                            <strong style="color: var(--primary-purple-light);">Kayıt Tarihi:</strong><br>
                            <span style="color: var(--gray-300);"><?php echo date('d.m.Y H:i', strtotime($appointment['created_at'])); ?></span>
                        </div>
                        <div>
                            <strong style="color: var(--primary-purple-light);">Son Güncelleme:</strong><br>
                            <span style="color: var(--gray-300);">
                                <?php echo $appointment['updated_at'] ? date('d.m.Y H:i', strtotime($appointment['updated_at'])) : date('d.m.Y H:i', strtotime($appointment['created_at'])); ?>
                            </span>
                        </div>
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <strong style="color: var(--primary-purple-light);">Mesaj / Not:</strong>
                        <div style="background: rgba(255,255,255,0.05); padding: 1rem; border-radius: var(--border-radius); color: var(--gray-300); margin-top: 0.5rem; min-height: 60px;">
                            <?php echo $appointment['message'] ? nl2br(htmlspecialchars($appointment['message'])) : '<span style=\'color:var(--gray-500);\'>-</span>'; ?>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; gap: 1rem; flex-wrap: wrap;">
                        <a href="randevu-yonetimi.php" class="btn btn-secondary">← Tüm Randevulara Dön</a>
                        <form method="POST" onsubmit="return confirm('Bu randevuyu silmek istediğinizden emin misiniz?');" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                            <button type="submit" name="delete_appointment" class="btn btn-reject">🗑️ Randevuyu Sil</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="message error">Randevu bulunamadı.</div>
                    <div style="text-align: center; margin-top: 2rem;">
                        <a href="randevu-yonetimi.php" class="btn btn-secondary">← Tüm Randevulara Dön</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 