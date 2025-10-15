<?php
require_once __DIR__ . '/config.php';

$name = "Admin User";
$email = "superadmin@sparklab.com"; // change to any email you like
$password = "12345678";             // admin password
$role = "admin";

// Check if email already exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    die("Admin with this email already exists.");
}

// Hash the password
$hash = password_hash($password, PASSWORD_DEFAULT);

// Insert admin
$stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
$stmt->execute([$name, $email, $hash, $role]);

echo "Admin created successfully! Email: $email Password: $password";
