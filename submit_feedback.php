<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

require_login();
verify_csrf();

$request_id = $_POST['request_id'] ?? null;
$feedback   = trim($_POST['feedback'] ?? '');
if (!$request_id || !$feedback) die('Invalid request.');

$stmt = $pdo->prepare("UPDATE requests SET feedback = ? WHERE id = ? AND client_id = ?");
$stmt->execute([$feedback, $request_id, current_user()['id']]);

redirect('client_dashboard.php');
