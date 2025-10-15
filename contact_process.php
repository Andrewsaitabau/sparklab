<?php
session_start();
require_once __DIR__ . '/config.php'; // DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!empty($name) && !empty($email) && !empty($message)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO get_in_touch (name, email, phone, subject, message, created_at) 
                VALUES (:name, :email, :phone, :subject, :message, NOW())
            ");
            $stmt->execute([
                ':name'    => $name,
                ':email'   => $email,
                ':phone'   => $phone,
                ':subject' => $subject,
                ':message' => $message
            ]);

            $_SESSION['success'] = "✅ Your message has been sent successfully!";
        } catch (PDOException $e) {
            $_SESSION['error'] = "❌ Database error: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "⚠️ Please fill in all required fields (Name, Email, Message).";
    }

    header("Location: contact.php");
    exit;
} else {
    header("Location: contact.php");
    exit;
}
