<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

require_role('client');
verify_csrf();

$title = trim($_POST['title'] ?? '');
$type  = $_POST['type'] ?? '';
$desc  = trim($_POST['description'] ?? '');

// Updated valid service types to match the expanded options
$valid_types = ['website', 'system', 'hardware', 'software', 'graphics', 'mobile', 'repair', 'planning', 'other'];

if ($title === '' || !in_array($type, $valid_types, true) || $desc === '') {
    die('Missing fields. Please make sure all fields are filled correctly.');
}

// Use $pdo from config.php
$stmt = $pdo->prepare("INSERT INTO requests (client_id,title,type,description,exchange_rate) VALUES (?,?,?,?,?)");
$stmt->execute([ current_user()['id'], $title, $type, $desc, get_setting_rate($pdo) ]);

header('Location: client_dashboard.php');
exit;