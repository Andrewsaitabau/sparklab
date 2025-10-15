<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

require_login(); // Only logged-in admin can send

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin_dashboard.php');
}

// Verify CSRF token
verify_csrf();

// Collect and sanitize inputs
$to         = trim($_POST['to_email'] ?? '');
$subject    = trim($_POST['subject'] ?? '');
$message    = trim($_POST['message'] ?? '');
$request_id = intval($_POST['request_id'] ?? 0);

// Validate required fields
if (empty($to) || empty($subject) || empty($message)) {
    flash_message('error', 'All fields are required.');
    redirect('admin_dashboard.php');
}

// Validate email format
if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
    flash_message('error', 'Invalid email address.');
    redirect('admin_dashboard.php');
}

// Email headers
$headers = "From: SparkLab <no-reply@sparklab.com>\r\n";
$headers .= "Reply-To: support@sparklab.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

// Build HTML email body
$emailBody = "
<html>
<head>
  <title>" . htmlspecialchars($subject) . "</title>
</head>
<body style='font-family: Arial, sans-serif;'>
  <h3>SparkLab Notification</h3>
  <p>" . nl2br(htmlspecialchars($message)) . "</p>
  <hr>
  <small>This is an automated email from SparkLab system. Do not reply directly.</small>
</body>
</html>
";

// Send email
$sent = mail($to, $subject, $emailBody, $headers);

// Save admin reply in DB (if related to a request)
if ($sent && $request_id > 0) {
    try {
        $stmt = $pdo->prepare("UPDATE requests SET admin_reply = ? WHERE id = ?");
        $stmt->execute([$message, $request_id]);
    } catch (Exception $e) {
        flash_message('error', 'Email sent but failed to update request: ' . $e->getMessage());
        redirect('admin_dashboard.php');
    }
}

// Redirect back with status
if ($sent) {
    flash_message('success', 'Email sent successfully.');
} else {
    flash_message('error', 'Failed to send email.');
}

redirect('admin_dashboard.php');
