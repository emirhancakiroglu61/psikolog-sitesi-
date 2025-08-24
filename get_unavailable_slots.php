<?php
require_once 'includes/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $date = isset($_GET['date']) ? trim($_GET['date']) : '';
    if (empty($date)) {
        echo json_encode(['success' => true, 'unavailable' => []]);
        exit;
    }

    // Basit tarih format kontrolü (YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        echo json_encode(['success' => true, 'unavailable' => []]);
        exit;
    }

    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT appointment_time FROM appointments WHERE preferred_date = ? AND status IN ('pending','approved')");
    $stmt->execute([$date]);
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    // Normalize saat formatı (HH:MM)
    $unavailable = array_values(array_unique(array_map(function($t){
        return substr($t, 0, 5);
    }, $rows)));

    echo json_encode(['success' => true, 'unavailable' => $unavailable]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Sunucu hatası']);
}
?>



