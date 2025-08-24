<?php
require_once '../includes/config.php';
requireAdmin();
$pdo = getDBConnection();

$success = '';
$error = '';
$showMessage = false;

// Arama ve filtreleme fonksiyonu
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where_conditions = [];
$params = [];

// Arama filtresi
if (!empty($search)) {
    $where_conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR national_id LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

// Durum filtresi
if ($status_filter !== '') {
    $where_conditions[] = "active = ?";
    $params[] = $status_filter;
}

// WHERE clause oluştur
$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// Danışan silme
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM patients WHERE id = ?")->execute([$del_id]);
    $success = 'Danışan başarıyla silindi.';
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
    if (!$first_name || !$last_name || !$national_id) {
        $error = 'Ad, soyad ve TC kimlik zorunludur!';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE patients SET first_name=?, last_name=?, national_id=?, details=?, active=? WHERE id=?");
            $stmt->execute([$first_name, $last_name, $national_id, $details, $active, $id]);
            $success = 'Danışan başarıyla güncellendi.';
            $edit_patient = null; // Formu sıfırla
        } catch (PDOException $e) {
            $error = 'Güncelleme hatası: ' . $e->getMessage();
        }
    }
    $showMessage = true;
}


// Danışanları çek (arama ile)
$query = "SELECT * FROM patients " . $where_clause . " ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danışan Yönetimi - Admin Panel</title>
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
        
        .admin-form {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius-lg);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-xl);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .admin-form h2 {
            color: var(--primary-purple-light);
            margin-bottom: 25px;
            font-size: 1.5rem;
            font-weight: 600;
        }
        .admin-form input[type="text"],
        .admin-form textarea {
            width: 100%;
            padding: 12px 16px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
            background: rgba(255, 255, 255, 0.05);
            color: var(--white);
            transition: all 0.3s ease;
        }
        .admin-form input[type="text"]:focus,
        .admin-form textarea:focus {
            outline: none;
            border-color: var(--primary-purple-light);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }
        .admin-form input[type="text"]::placeholder,
        .admin-form textarea::placeholder {
            color: var(--gray-400);
        }
        .admin-form button {
            background: var(--primary-purple-light);
            color: var(--white);
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-right: 10px;
            box-shadow: 0 2px 8px rgba(124, 58, 237, 0.2);
        }
        .admin-form button:hover {
            background: var(--primary-purple-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
        }
        .admin-form .cancel-btn {
            background: var(--gray-600);
            color: var(--white);
            text-decoration: none;
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(75, 85, 99, 0.2);
        }
        .admin-form .cancel-btn:hover {
            background: var(--gray-700);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(75, 85, 99, 0.3);
        }
        .admin-form label {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            font-size: 1rem;
            color: var(--white);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .admin-form label:hover {
            color: var(--primary-purple-light);
        }
        .admin-form input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            accent-color: var(--primary-purple-light);
            cursor: pointer;
        }
        .success-message {
            background: #e6ffed;
            color: #15803d;
            border: 1px solid #bbf7d0;
            padding: 10px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
        }
        .error-message {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            padding: 10px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
        }
        .action-btn {
            display: inline-block;
            padding: 5px 8px;
            border-radius: 6px;
            font-size: 0.65rem;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: hidden;
            white-space: nowrap;
            min-width: 55px;
            max-width: 70px;
            text-align: center;
            line-height: 1.2;
            flex-shrink: 0;
        }
        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        .action-btn:hover::before {
            left: 100%;
        }
        .btn-edit {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #fff;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }
        .btn-edit:hover {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.4);
        }
        .btn-delete {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .btn-delete:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }
        .btn-detail {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }
        .btn-detail:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
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
        /* Modal */
        .modal-bg {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(24, 16, 48, 0.55);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            animation: fadeInBg 0.3s;
        }
        .modal-bg.active { display: flex; }
        @keyframes fadeInBg {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .modal-content {
            background: rgba(31, 24, 54, 0.92);
            border-radius: 20px;
            padding: 38px 32px 28px 32px;
            min-width: 340px;
            max-width: 95vw;
            box-shadow: 0 12px 48px 0 rgba(91,33,182,0.25), 0 2px 8px 0 rgba(0,0,0,0.10);
            position: relative;
            border: 2px solid;
            border-image: linear-gradient(135deg, var(--primary-purple-light), var(--primary-purple-dark)) 1;
            backdrop-filter: blur(18px) saturate(1.2);
            animation: fadeInModal 0.35s cubic-bezier(.4,0,.2,1);
        }
        @keyframes fadeInModal {
            from { opacity: 0; transform: translateY(40px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .modal-content h3 {
            margin-top: 0;
            margin-bottom: 22px;
            font-size: 1.45rem;
            font-weight: 700;
            color: var(--primary-purple-light);
            letter-spacing: 0.5px;
            text-shadow: 0 2px 16px rgba(124,58,237,0.12);
        }
        .modal-close {
            position: absolute;
            top: 14px;
            right: 22px;
            font-size: 2.1rem;
            color: var(--primary-purple-light);
            background: none;
            border: none;
            cursor: pointer;
            transition: color 0.2s;
            z-index: 2;
        }
        .modal-close:hover {
            color: var(--accent-purple);
        }
        .modal-content table {
            width: 100%;
            border-collapse: collapse;
        }
        .modal-content td {
            padding: 8px 0 8px 0;
            vertical-align: top;
            font-size: 1.08rem;
        }
        .modal-content tr td:first-child {
            font-weight: 600;
            color: var(--primary-purple-light);
            width: 130px;
            letter-spacing: 0.2px;
        }
        .modal-content tr td:last-child {
            color: var(--white);
            word-break: break-word;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-xl);
            table-layout: fixed;
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
        .admin-table th:nth-child(1), .admin-table td:nth-child(1) { width: 10%; } /* Ad */
        .admin-table th:nth-child(2), .admin-table td:nth-child(2) { width: 10%; } /* Soyad */
        .admin-table th:nth-child(3), .admin-table td:nth-child(3) { width: 10%; } /* TC Kimlik */
        .admin-table th:nth-child(4), .admin-table td:nth-child(4) { width: 18%; } /* Detaylar */
        .admin-table th:nth-child(5), .admin-table td:nth-child(5) { width: 7%; }  /* Durum */
        .admin-table th:nth-child(6), .admin-table td:nth-child(6) { width: 10%; } /* Oluşturulma */
        .admin-table th:nth-child(7), .admin-table td:nth-child(7) { width: 10%; } /* Son Güncelleme */
        .admin-table th:nth-child(8), .admin-table td:nth-child(8) { width: 15%; } /* İşlem */
        /* Detaylar hücresi için özel stil */
        .admin-table td.details-cell {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            word-break: break-word;
        }
        .action-buttons {
            display: flex;
            gap: 0.4rem;
            flex-wrap: nowrap;
            justify-content: center;
            align-items: center;
            padding: 0.25rem;
            min-height: auto;
            width: 100%;
        }
        
        /* Responsive button adjustments */
        @media (max-width: 768px) {
            .action-buttons {
                gap: 0.2rem;
                padding: 0.2rem;
            }
            .action-btn {
                padding: 4px 6px;
                font-size: 0.6rem;
                min-width: 45px;
                max-width: 60px;
            }
        }
        
        @media (max-width: 480px) {
            .action-buttons {
                flex-direction: row;
                gap: 0.2rem;
                flex-wrap: wrap;
            }
            .action-btn {
                padding: 3px 5px;
                font-size: 0.55rem;
                min-width: 40px;
                max-width: 55px;
            }
        }

        /* Responsive Design for Patient Management */
        @media (max-width: 1200px) {
            .admin-table {
                font-size: 0.9rem;
            }
            .admin-table th,
            .admin-table td {
                padding: 0.75rem 0.5rem;
            }
            .admin-table th {
                vertical-align: top;
                white-space: nowrap;
            }
        }

        @media (max-width: 992px) {
            .admin-container {
                padding: 15px;
            }
            
            .admin-header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .admin-header h1 {
                font-size: 1.5rem;
            }
            
            .admin-user-info {
                flex-direction: column;
                gap: 10px;
            }
            
            .admin-nav {
                flex-direction: column;
                gap: 10px;
            }
            
            .admin-nav a {
                padding: 12px 16px;
                font-size: 0.9rem;
            }
            
            .search-container {
                padding: 15px;
            }
            
            .search-form {
                flex-direction: column;
                gap: 10px;
            }
            
            .search-input,
            .status-filter {
                min-width: auto;
                width: 100%;
            }
            
            .search-btn,
            .clear-btn {
                width: 100%;
                padding: 12px;
            }
            
            .admin-form {
                padding: 20px;
            }
            
            .admin-form h2 {
                font-size: 1.3rem;
            }

            /* Table responsive adjustments */
            .admin-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
                font-size: 0.85rem;
            }
            
            .admin-table th,
            .admin-table td {
                padding: 0.5rem 0.4rem;
                min-width: 80px;
            }
            
            .admin-table th {
                vertical-align: top;
                white-space: nowrap;
                font-weight: 600;
            }
            
            .admin-table th:nth-child(4),
            .admin-table td:nth-child(4) {
                min-width: 120px;
            }
            
            .admin-table th:nth-child(8),
            .admin-table td:nth-child(8) {
                min-width: 150px;
            }
        }

        /* Samsung Galaxy S20 Ultra ve benzeri telefonlar için özel optimizasyon */
        @media (max-width: 412px) and (min-height: 800px) {
            .admin-container {
                padding: 20px;
                max-width: 100%;
            }
            
            .admin-header-content {
                flex-direction: row;
                gap: 20px;
                text-align: left;
                align-items: center;
            }
            
            .admin-header h1 {
                font-size: 1.8rem;
                margin: 0;
            }
            
            .admin-user-info {
                flex-direction: row;
                gap: 15px;
                align-items: center;
            }
            
            .admin-nav {
                display: flex;
                flex-direction: column;
                gap: 10px;
                margin: 20px 0;
            }
            
            .admin-nav a {
                padding: 15px 20px;
                font-size: 1rem;
                text-align: center;
                border-radius: 12px;
            }
            
            .search-container {
                padding: 20px;
                margin: 20px 0;
            }
            
            .search-form {
                flex-direction: row;
                gap: 15px;
                flex-wrap: wrap;
                align-items: center;
            }
            
            .search-input {
                flex: 2;
                min-width: 200px;
            }
            
            .status-filter {
                flex: 1;
                min-width: 150px;
            }
            
            .search-btn,
            .clear-btn {
                flex: 0 0 auto;
                padding: 12px 20px;
                white-space: nowrap;
            }
            
            .admin-form {
                padding: 25px;
                margin: 20px 0;
            }
            
            .admin-form h2 {
                font-size: 1.5rem;
                margin-bottom: 20px;
            }
            
            .admin-form input[type="text"],
            .admin-form textarea {
                padding: 15px;
                font-size: 1rem;
                margin-bottom: 20px;
            }
            
            .admin-form button,
            .admin-form .cancel-btn {
                padding: 15px 25px;
                font-size: 1rem;
                margin-right: 15px;
            }

            /* Table optimizations for Samsung phones */
            .admin-table {
                font-size: 0.9rem;
                border-radius: 15px;
                overflow: hidden;
            }
            
            .admin-table th,
            .admin-table td {
                padding: 12px 8px;
                min-width: 90px;
                font-size: 0.85rem;
            }
            
            .admin-table th:nth-child(4),
            .admin-table td:nth-child(4) {
                min-width: 140px;
            }
            
            .admin-table th:nth-child(8),
            .admin-table td:nth-child(8) {
                min-width: 180px;
            }
            
            .action-buttons {
                gap: 8px;
                padding: 8px;
                justify-content: center;
            }
            
            .action-btn {
                padding: 8px 12px;
                font-size: 0.75rem;
                min-width: 70px;
                max-width: 90px;
                border-radius: 8px;
            }
            

        }

        @media (max-width: 768px) {
            .admin-container {
                padding: 10px;
            }
            
            .admin-header h1 {
                font-size: 1.3rem;
            }
            
            .search-container {
                padding: 10px;
            }
            
            .admin-form {
                padding: 15px;
            }
            
            .admin-table {
                font-size: 0.8rem;
            }
            
            .admin-table th,
            .admin-table td {
                padding: 0.4rem 0.3rem;
                min-width: 70px;
            }
            
            .admin-table th {
                vertical-align: top;
                white-space: nowrap;
                font-weight: 600;
            }
            
            .action-buttons {
                gap: 0.15rem;
                padding: 0.15rem;
            }
            
            .action-btn {
                padding: 4px 6px;
                font-size: 0.6rem;
                min-width: 45px;
                max-width: 60px;
            }
        }

        @media (max-width: 576px) {
            .admin-container {
                padding: 8px;
            }
            
            .admin-header h1 {
                font-size: 1.2rem;
            }
            
            .search-container {
                padding: 8px;
            }
            
            .search-form {
                gap: 8px;
            }
            
            .search-input,
            .status-filter,
            .search-btn,
            .clear-btn {
                padding: 10px;
                font-size: 0.9rem;
            }
            
            .admin-form {
                padding: 12px;
            }
            
            .admin-form input[type="text"],
            .admin-form textarea {
                padding: 10px 12px;
                font-size: 0.9rem;
            }
            
            .admin-table {
                font-size: 0.75rem;
            }
            
            .admin-table th,
            .admin-table td {
                padding: 0.3rem 0.2rem;
                min-width: 60px;
            }
            
            .admin-table th {
                vertical-align: top;
                white-space: nowrap;
                font-weight: 600;
            }
            
            .admin-table th:nth-child(4),
            .admin-table td:nth-child(4) {
                min-width: 100px;
            }
            
            .admin-table th:nth-child(8),
            .admin-table td:nth-child(8) {
                min-width: 120px;
            }
            
            .action-buttons {
                gap: 0.1rem;
                padding: 0.1rem;
            }
            
            .action-btn {
                padding: 3px 5px;
                font-size: 0.55rem;
                min-width: 40px;
                max-width: 55px;
            }
        }

        @media (max-width: 480px) {
            .admin-container {
                padding: 5px;
            }
            
            .admin-header h1 {
                font-size: 1.1rem;
            }
            
            .search-container {
                padding: 5px;
            }
            
            .search-form {
                gap: 5px;
            }
            
            .search-input,
            .status-filter,
            .search-btn,
            .clear-btn {
                padding: 8px;
                font-size: 0.85rem;
            }
            
            .admin-form {
                padding: 10px;
            }
            
            .admin-form h2 {
                font-size: 1.1rem;
            }
            
            .admin-form input[type="text"],
            .admin-form textarea {
                padding: 8px 10px;
                font-size: 0.85rem;
            }
            
            .admin-table {
                font-size: 0.7rem;
            }
            
            .admin-table th,
            .admin-table td {
                padding: 0.25rem 0.15rem;
                min-width: 50px;
            }
            
            .admin-table th {
                vertical-align: top;
                white-space: nowrap;
                font-weight: 600;
            }
            
            .admin-table th:nth-child(4),
            .admin-table td:nth-child(4) {
                min-width: 80px;
            }
            
            .admin-table th:nth-child(8),
            .admin-table td:nth-child(8) {
                min-width: 100px;
            }
            
            .action-buttons {
                gap: 0.05rem;
                padding: 0.05rem;
            }
            
            .action-btn {
                padding: 2px 4px;
                font-size: 0.5rem;
                min-width: 35px;
                max-width: 45px;
            }
        }



        /* Samsung ve benzeri büyük telefonlar için ek optimizasyonlar */
        @media (max-width: 450px) and (min-height: 700px) {
            .admin-container {
                padding: 15px;
            }
            
            .admin-header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .admin-header h1 {
                font-size: 1.6rem;
            }
            
            .admin-user-info {
                flex-direction: column;
                gap: 10px;
            }
            
            .admin-nav {
                display: flex;
                flex-direction: column;
                gap: 8px;
                margin: 15px 0;
            }
            
            .admin-nav a {
                padding: 12px 15px;
                font-size: 0.9rem;
            }
            
            .search-container {
                padding: 15px;
                margin: 15px 0;
            }
            
            .search-form {
                flex-direction: column;
                gap: 10px;
            }
            
            .search-input,
            .status-filter {
                width: 100%;
                padding: 12px;
            }
            
            .search-btn,
            .clear-btn {
                width: 100%;
                padding: 12px;
            }
            
            .admin-form {
                padding: 20px;
                margin: 15px 0;
            }
            
            .admin-form h2 {
                font-size: 1.4rem;
            }
            
            .admin-form input[type="text"],
            .admin-form textarea {
                padding: 12px;
                font-size: 0.95rem;
            }
            
            .admin-form button,
            .admin-form .cancel-btn {
                padding: 12px 20px;
                font-size: 0.95rem;
            }

            /* Table optimizations */
            .admin-table {
                font-size: 0.85rem;
            }
            
            .admin-table th,
            .admin-table td {
                padding: 10px 6px;
                min-width: 80px;
                font-size: 0.8rem;
            }
            
            .admin-table th:nth-child(4),
            .admin-table td:nth-child(4) {
                min-width: 120px;
            }
            
            .admin-table th:nth-child(8),
            .admin-table td:nth-child(8) {
                min-width: 160px;
            }
            
            .action-buttons {
                gap: 6px;
                padding: 6px;
            }
            
            .action-btn {
                padding: 6px 10px;
                font-size: 0.7rem;
                min-width: 60px;
                max-width: 80px;
            }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="container">
        <div class="admin-header">
            <div class="admin-header-content">
                <h1>👤 Danışan Yönetimi</h1>
                <div class="admin-user-info">
                    <span><?php echo htmlspecialchars($_SESSION['admin_first_name'] . ' ' . $_SESSION['admin_last_name']); ?></span>
                    <a href="logout.php" class="admin-logout-btn">🚪 Çıkış Yap</a>
                </div>
            </div>
        </div>
        <div class="admin-nav">
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="blog-yonetimi.php">📝 Blog Yönetimi</a>
            <a href="beslenme-yonetimi.php">🥗 Beslenme Yönetimi</a>
            <a href="randevu-yonetimi.php">📅 Randevu Yönetimi</a>
            <a href="message-management.php">💬 Mesaj Yönetimi</a>
            <a href="patients-management.php">👤 Danışan Yönetimi</a>
            <a href="profile-settings.php">⚙️ Profil Ayarları</a>
        </div>
        <?php if ($showMessage && $success): ?><div class="success-message"><?= $success ?></div><?php endif; ?>
        <?php if ($showMessage && $error): ?><div class="error-message"><?= $error ?></div><?php endif; ?>
        
        <!-- Arama ve Filtreleme Çubuğu -->
        <div class="search-container">
            <form method="get" class="search-form">
                <input type="text" name="search" class="search-input" placeholder="Ad, soyad veya TC kimlik ile arama yapın..." value="<?= htmlspecialchars($search) ?>">
                
                <select name="status" class="status-filter">
                    <option value="">Tüm Durumlar</option>
                    <option value="1" <?= $status_filter === '1' ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= $status_filter === '0' ? 'selected' : '' ?>>Pasif</option>
                </select>
                
                <button type="submit" class="search-btn">🔍 Ara</button>
                <?php if (!empty($search) || $status_filter !== ''): ?>
                    <a href="patients-management.php" class="clear-btn">❌ Temizle</a>
                <?php endif; ?>
            </form>
            <?php if (!empty($search) || $status_filter !== ''): ?>
                <div class="search-info">
                    <?php
                    $filter_text = [];
                    if (!empty($search)) $filter_text[] = '"' . htmlspecialchars($search) . '" araması';
                    if ($status_filter === '1') $filter_text[] = 'Aktif danışanlar';
                    if ($status_filter === '0') $filter_text[] = 'Pasif danışanlar';
                    ?>
                    <?= implode(' + ', $filter_text) ?> için <?= count($patients) ?> sonuç bulundu.
                </div>
            <?php endif; ?>
        </div>

        <?php if ($edit_patient): ?>
            <h2>Danışan Düzenle</h2>
            <form method="post" class="admin-form">
                <input type="hidden" name="id" value="<?= $edit_patient['id'] ?>">
                <input type="text" name="first_name" placeholder="Ad" value="<?= htmlspecialchars($edit_patient['first_name']) ?>" required>
                <input type="text" name="last_name" placeholder="Soyad" value="<?= htmlspecialchars($edit_patient['last_name']) ?>" required>
                <input type="text" name="national_id" placeholder="TC Kimlik No" value="<?= htmlspecialchars($edit_patient['national_id']) ?>" required maxlength="11">
                <textarea name="details" placeholder="Hasta Detayları" rows="3"><?= htmlspecialchars($edit_patient['details']) ?></textarea>
                <label><input type="checkbox" name="active" <?= $edit_patient['active'] ? 'checked' : '' ?>> Aktif</label>
                <button type="submit" name="update_patient">✅ Güncelle</button>
                <a href="patients-management.php<?= !empty($search) ? '?search=' . urlencode($search) : '' ?><?= $status_filter !== '' ? (empty($search) ? '?' : '&') . 'status=' . urlencode($status_filter) : '' ?>" class="cancel-btn">❌ İptal</a>
            </form>
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Danışan Listesi</h2>
            <a href="patient-add.php" class="btn" style="background: var(--primary-purple-light); color: var(--white); padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 500;">➕ Yeni Danışan Ekle</a>
        </div>
        <?php if (empty($patients)): ?>
            <div style="text-align: center; padding: 40px; color: var(--gray-400);">
                <?php if (!empty($search)): ?>
                    "<?= htmlspecialchars($search) ?>" için sonuç bulunamadı.
                <?php else: ?>
                    Henüz danışan kaydı bulunmuyor.
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <table class="admin-table">
                <thead>
                    <tr>
                        <th>Ad</th>
                        <th>Soyad</th>
                        <th>TC Kimlik</th>
                        <th>Detaylar</th>
                        <th>Durum</th>
                        <th>Oluşturulma</th>
                        <th>Son Güncelleme</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($patients as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['first_name']) ?></td>
                        <td><?= htmlspecialchars($p['last_name']) ?></td>
                        <td><?= htmlspecialchars($p['national_id']) ?></td>
                        <td class="details-cell" title="<?= htmlspecialchars($p['details']) ?>">
                            <?php
                            $short = mb_substr($p['details'], 0, 50, 'UTF-8');
                            $isLong = mb_strlen($p['details'], 'UTF-8') > 50;
                            echo htmlspecialchars($short);
                            if ($isLong) echo '...';
                            ?>
                        </td>
                        <td><?= $p['active'] ? 'Aktif' : 'Pasif' ?></td>
                        <td><?= $p['created_at'] ?></td>
                        <td><?= $p['updated_at'] ?: $p['created_at'] ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="patients-management.php?edit=<?= $p['id'] ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= $status_filter !== '' ? '&status=' . urlencode($status_filter) : '' ?>" class="action-btn btn-edit" title="Danışanı Düzenle">✏️ Düzenle</a>
                                <a href="patients-management.php?delete=<?= $p['id'] ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= $status_filter !== '' ? '&status=' . urlencode($status_filter) : '' ?>" class="action-btn btn-delete" onclick="return confirm('Danışan silinsin mi?')" title="Danışanı Sil">🗑️ Sil</a>
                                <button type="button" class="action-btn btn-detail" onclick='showPatientDetail(<?= json_encode($p, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>)' title="Danışan Detayları">👁️ Detay</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>

        <!-- Hasta Detay Modalı -->
        <div class="modal-bg" id="patientDetailModal">
            <div class="modal-content">
                <button class="modal-close" onclick="closePatientDetail()">&times;</button>
                <h3>Danışan Detayları</h3>
                <table>
                    <tr><td>Ad:</td><td id="modal_first_name"></td></tr>
                    <tr><td>Soyad:</td><td id="modal_last_name"></td></tr>
                    <tr><td>TC Kimlik:</td><td id="modal_national_id"></td></tr>
                    <tr><td>Durum:</td><td id="modal_active"></td></tr>
                    <tr><td>Oluşturulma:</td><td id="modal_created_at"></td></tr>
                    <tr><td>Son Güncelleme:</td><td id="modal_updated_at"></td></tr>
                </table>
                <div style="margin-top: 20px;">
                    <h4 style="color: var(--primary-purple-light); margin-bottom: 10px;">📝 Danışan Detayları</h4>
                    <div id="modal_details" style="background: rgba(255, 255, 255, 0.05); padding: 15px; border-radius: 8px; white-space: pre-line; max-height: 400px; overflow-y: auto; color: var(--gray-300); font-family: 'Courier New', monospace; font-size: 0.9rem; line-height: 1.5;"></div>
                </div>
            </div>
        </div>
        <script>
        function showPatientDetail(patient) {
            document.getElementById('modal_first_name').textContent = patient.first_name;
            document.getElementById('modal_last_name').textContent = patient.last_name;
            document.getElementById('modal_national_id').textContent = patient.national_id;
            document.getElementById('modal_active').textContent = patient.active == 1 ? 'Aktif' : 'Pasif';
            document.getElementById('modal_created_at').textContent = patient.created_at;
            document.getElementById('modal_updated_at').textContent = patient.updated_at || patient.created_at;
            
            // Detayları formatla
            let details = patient.details || 'Detay bulunmuyor.';
            document.getElementById('modal_details').textContent = details;
            
            document.getElementById('patientDetailModal').classList.add('active');
        }
        function closePatientDetail() {
            document.getElementById('patientDetailModal').classList.remove('active');
        }
        // Modalı ESC ile kapat
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closePatientDetail();
        });
        </script>
    </div>
</div>
</body>
</html> 