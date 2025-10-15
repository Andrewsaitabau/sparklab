<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

require_login();
if (current_user()['role'] !== 'client') {
    redirect('admin_dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = intval($_POST['service_id'] ?? 0);
    $client_id = current_user()['id'];

    if ($service_id > 0) {
        // Check if item already in cart
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE client_id = ? AND service_id = ?");
        $stmt->execute([$client_id, $service_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Update quantity
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE id = ?");
            $stmt->execute([$existing['id']]);
        } else {
            // Insert new
            $stmt = $pdo->prepare("INSERT INTO cart_items (client_id, service_id, quantity) VALUES (?, ?, 1)");
            $stmt->execute([$client_id, $service_id]);
        }
    }
}

// Redirect back to dashboard
redirect('client_dashboard.php');
