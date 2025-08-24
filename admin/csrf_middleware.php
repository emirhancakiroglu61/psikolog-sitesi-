<?php
// CSRF koruması middleware
function checkCSRFToken() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $csrf_token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
            http_response_code(403);
            if (isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Invalid CSRF token']);
            } else {
                echo '<h1>403 Forbidden</h1><p>Invalid CSRF token</p>';
            }
            exit;
        }
    }
}

function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

// generateCSRFToken fonksiyonu config.php'de zaten tanımlı

function getCSRFTokenInput() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}
?> 