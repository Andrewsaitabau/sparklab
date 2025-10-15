<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_role('admin');
verify_csrf();

$rate = (float)($_POST['usd_to_kes'] ?? 0);
if ($rate < 50) $rate = 50;

$pdo->prepare("INSERT INTO settings (id, usd_to_kes) VALUES (1, ?) ON DUPLICATE KEY UPDATE usd_to_kes = VALUES(usd_to_kes)")
    ->execute([$rate]);

header('Location: admin_dashboard.php');
