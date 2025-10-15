<?php
// get_payments.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

// Check if user is logged in (admin only)
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    // Get payment statistics
    $stats_stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_payments,
            SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_payments,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_payments,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_payments
        FROM payments
    ");
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

    // Get recent payments
    $payments_stmt = $pdo->query("
        SELECT p.*, u.name as client_name, u.email as client_email,
               s.name as service_name, ci.quantity, ci.payment_status,
               ci.mpesa_receipt_number, ci.transaction_date
        FROM payments p
        JOIN cart_items ci ON p.cart_id = ci.id
        JOIN users u ON p.client_id = u.id
        JOIN services s ON ci.service_id = s.id
        ORDER BY p.created_at DESC
        LIMIT 10
    ");
    $recent_payments = $payments_stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'total_payments' => $stats['total_payments'] ?? 0,
        'success_payments' => $stats['success_payments'] ?? 0,
        'pending_payments' => $stats['pending_payments'] ?? 0,
        'failed_payments' => $stats['failed_payments'] ?? 0,
        'recent_payments' => $recent_payments
    ]);
} catch (Exception $e) {
    error_log("get_payments.php error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch payment data',
        'total_payments' => 0,
        'success_payments' => 0,
        'pending_payments' => 0,
        'failed_payments' => 0,
        'recent_payments' => []
    ]);
}
?>