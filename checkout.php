<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

require_login();

if (current_user()['role'] !== 'client') {
    redirect('admin_dashboard.php');
}

$client_id = current_user()['id'];

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ Manual CSRF check instead of blind verify_csrf()
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
        $_SESSION['error'] = "Invalid CSRF token!";
        header("Location: client_dashboard.php");
        exit;
    }

    // Fetch cart items
    $cart_stmt = $pdo->prepare("
        SELECT c.id, s.id AS service_id, s.name, s.price, c.quantity
        FROM cart_items c
        JOIN services s ON c.service_id = s.id
        WHERE c.client_id = ?
    ");
    $cart_stmt->execute([$client_id]);
    $cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$cart_items) {
        $_SESSION['error'] = "Your cart is empty.";
        header("Location: client_dashboard.php");
        exit;
    }

    // Insert each cart item as a request
    $total = 0;
    foreach ($cart_items as $item) {
        $stmt = $pdo->prepare("
            INSERT INTO requests (client_id, service_id, request_text, status, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $request_text = "Booking for {$item['quantity']} x {$item['name']}";
        $stmt->execute([$client_id, $item['service_id'], $request_text, 'pending']);

        $total += $item['price'] * $item['quantity'];
    }

    // Clear the cart
    $pdo->prepare("DELETE FROM cart_items WHERE client_id = ?")->execute([$client_id]);

    // Save total in session (so payment.php can use it)
    $_SESSION['checkout_total'] = $total;

    // ✅ Redirect to payment page
    header("Location: payment.php");
    exit;
}

// If accessed directly without POST
header("Location: client_dashboard.php");
exit;
