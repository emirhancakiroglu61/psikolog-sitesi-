<?php
require_once '../includes/config.php';
requireAdmin();
$pdo = getDBConnection();

// Danışan silme
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM patients WHERE id = ?")->execute([$del_id]);
    header('Location: patients-management.php?deleted=1');
    exit;
}

// Danışan güncelleme
$edit_id = isset($_GET['edit']) && is_numeric($_GET['edit']) ? (int)$_GET['edit'] : null;
$edit_patient = null;
if ($edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_patient = $stmt->fetch(PDO::FETCH_ASSOC);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_patient'])) {
    $id = (int)$_POST['id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $national_id = trim($_POST['national_id']);
    $details = trim($_POST['details']);
    $active = isset($_POST['active']) ? 1 : 0;
    $error = '';
    if (!$first_name || !$last_name || !$national_id) {
        $error = 'Ad, soyad ve TC kimlik zorunludur!';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE patients SET first_name=?, last_name=?, national_id=?, details=?, active=? WHERE id=?");
            $stmt->execute([$first_name, $last_name, $national_id, $details, $active, $id]);
            header('Location: patients-management.php?updated=1');
            exit;
        } catch (PDOException $e) {
            $error = 'Güncelleme hatası: ' . $e->getMessage();
        }
    }
}
// Danışan ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_patient'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $national_id = trim($_POST['national_id']);
    $details = trim($_POST['details']);
    $active = isset($_POST['active']) ? 1 : 0;
    $error = '';
    if (!$first_name || !$last_name || !$national_id) {
        $error = 'Ad, soyad ve TC kimlik zorunludur!';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO patients (first_name, last_name, national_id, details, active) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $national_id, $details, $active]);
            header('Location: patients-management.php?success=1');
            exit;
        } catch (PDOException $e) {
            $error = 'Ekleme hatası: ' . $e->getMessage();
        }
    }
}
// Danışanları çek
$patients = $pdo->query("SELECT * FROM patients ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Danışan Yönetimi - Admin Panel</title>
    <link rel="stylesheet" href="admin-styles.css">
    <style>
        .admin-form input[type="text"],
        .admin-form input[type="email"],
        .admin-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        .admin-form button {
            background: #4f8cff;
            color: #fff;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .admin-form button:hover {
            background: #2563eb;
        }
        .admin-form label {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-size: 1rem;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="container">
        <div class="admin-header">
            <h1>👤 Danışan Yönetimi</h1>
        </div>
        <h2>Danışanlar</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Ad</th>
                    <th>Soyad</th>
                    <th>TC Kimlik</th>
                    <th>Detaylar</th>
                    <th>Durum</th>
                    <th>Oluşturulma</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($patients as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['first_name']) ?></td>
                    <td><?= htmlspecialchars($p['last_name']) ?></td>
                    <td><?= htmlspecialchars($p['national_id']) ?></td>
                    <td><?= nl2br(htmlspecialchars($p['details'])) ?></td>
                    <td><?= $p['active'] ? 'Aktif' : 'Pasif' ?></td>
                    <td><?= $p['created_at'] ?></td>
                    <td>
                        <a href="patients-management.php?edit=<?= $p['id'] ?>">Düzenle</a> |
                        <a href="patients-management.php?delete=<?= $p['id'] ?>" onclick="return confirm('Danışan silinsin mi?')">Sil</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ($edit_patient): ?>
            <h2>Danışan Düzenle</h2>
            <?php if (!empty($error)): ?><div style="color:red;"> <?= $error ?> </div><?php endif; ?>
            <form method="post" class="admin-form">
                <input type="hidden" name="id" value="<?= $edit_patient['id'] ?>">
                <input type="text" name="first_name" placeholder="Ad" value="<?= htmlspecialchars($edit_patient['first_name']) ?>" required>
                <input type="text" name="last_name" placeholder="Soyad" value="<?= htmlspecialchars($edit_patient['last_name']) ?>" required>
                <input type="text" name="national_id" placeholder="TC Kimlik No" value="<?= htmlspecialchars($edit_patient['national_id']) ?>" required maxlength="11">
                <textarea name="details" placeholder="Hasta Detayları" rows="3"><?= htmlspecialchars($edit_patient['details']) ?></textarea>
                <label><input type="checkbox" name="active" <?= $edit_patient['active'] ? 'checked' : '' ?>> Aktif</label>
                <button type="submit" name="update_patient">Güncelle</button>
                <a href="patients-management.php">İptal</a>
            </form>
        <?php else: ?>
            <h2>Yeni Danışan Ekle</h2>
            <?php if (!empty($error)): ?><div style="color:red;"> <?= $error ?> </div><?php endif; ?>
            <form method="post" class="admin-form">
                <input type="text" name="first_name" placeholder="Ad" required>
                <input type="text" name="last_name" placeholder="Soyad" required>
                <input type="text" name="national_id" placeholder="TC Kimlik No" required maxlength="11">
                <textarea name="details" placeholder="Hasta Detayları" rows="3"></textarea>
                <label><input type="checkbox" name="active" checked> Aktif</label>
                <button type="submit" name="add_patient">Ekle</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html> 