<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

require_login();
if (current_user()['role'] !== 'client') {
    redirect('admin_dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $message = trim($_POST['message'] ?? '');
    if ($message) {
        $stmt = $pdo->prepare("INSERT INTO requests (client_id, message, status) VALUES (?, ?, 'Pending')");
        $stmt->execute([current_user()['id'], $message]);
    }
}
redirect('client_dashboard.php');
