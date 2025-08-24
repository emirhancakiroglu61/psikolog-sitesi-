<?php
require_once '../includes/config.php';
requireAdmin();
$pdo = getDBConnection();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id) {
    $stmt = $pdo->prepare('DELETE FROM blog WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: blog-yonetimi.php?deleted=1');
    exit;
}
header('Location: blog-yonetimi.php');
exit;
?> 