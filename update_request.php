<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

require_login();   // Ensure only logged-in users (admins) can access

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
        $_SESSION['error'] = "Invalid CSRF token!";
        header("Location: admin_dashboard.php");
        exit;
    }

    $id         = (int) ($_POST['id'] ?? 0);
    $status     = trim($_POST['status'] ?? '');
    $admin_note = trim($_POST['admin_note'] ?? '');

    // Validate
    $valid_statuses = ['pending', 'in_progress', 'completed'];
    if (!$id || !in_array($status, $valid_statuses)) {
        $_SESSION['error'] = "Invalid input data!";
        header("Location: admin_dashboard.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE requests SET status = ?, admin_note = ? WHERE id = ?");
        $stmt->execute([$status, $admin_note, $id]);

        $_SESSION['success'] = "Request #$id updated successfully.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Error updating request: " . $e->getMessage();
    }
}

header("Location: admin_dashboard.php");
exit;
