<?php
session_start();
require_once '../includes/config.php';
require_once 'csrf_middleware.php';

// Admin girişi kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// JSON response header
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

$message_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$message_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid message ID']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
    $stmt->execute([$message_id]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$message) {
        echo json_encode(['success' => false, 'error' => 'Message not found']);
        exit;
    }
    
    // Mesajı okundu olarak işaretle
    if ($message['status'] === 'unread') {
        $stmt = $pdo->prepare("UPDATE messages SET status = 'read' WHERE id = ?");
        $stmt->execute([$message_id]);
        $message['status'] = 'read';
    }
    
    // Tarihi formatla
    $message['created_at'] = date('d.m.Y H:i', strtotime($message['created_at']));
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error'
    ]);
}
?> 