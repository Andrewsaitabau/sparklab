<?php
// forgot_password.php
// Full corrected version using PHPMailer (SMTP) so it works locally (XAMPP) or on production.
// Requirements:
//  - Add PHPMailer via Composer: `composer require phpmailer/phpmailer`
//  - Ensure your `users` table has columns: reset_code (VARCHAR), reset_expires (DATETIME)
//  - Configure SMTP credentials below (or via environment variables)

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php'; // optional; contains e() and require_login etc.

// Ensure helper e() exists
if (!function_exists('e')) {
    function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
}

// Try to load Composer autoload (PHPMailer). If missing, show friendly message in UI.
$phpmailer_available = false;
$composer_autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($composer_autoload)) {
    require_once $composer_autoload;
    $phpmailer_available = class_exists(\PHPMailer\PHPMailer\PHPMailer::class);
}

// SMTP settings — **replace** with your credentials or set environment variables.
$smtpHost   = getenv('SMTP_HOST')   ?: 'smtp.gmail.com';
$smtpPort   = getenv('SMTP_PORT')   ?: 587;
$smtpUser   = getenv('SMTP_USER')   ?: 'your-email@gmail.com';       // <-- change
$smtpPass   = getenv('SMTP_PASS')   ?: 'your-app-password-or-smtp-password'; // <-- change
$smtpSecure = getenv('SMTP_SECURE') ?: 'tls'; // 'tls' or 'ssl'

// UI variables
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw_email = trim($_POST['email'] ?? '');
    $email = filter_var($raw_email, FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        try {
            // Look up user by email (limit 1)
            $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Generic user-facing message to avoid account enumeration
            $generic_message = 'If an account with that email exists, a reset code has been sent.';

            if (!$user) {
                // Still respond with success message (do not reveal absence)
                $success = $generic_message;
            } else {
                // Generate secure 6-digit code
                $reset_code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $expires_at = date('Y-m-d H:i:s', time() + 15 * 60); // 15 minutes

                // Hash code before storing
                $hashed = password_hash($reset_code, PASSWORD_DEFAULT);

                // Store hashed code & expiry
                $upd = $pdo->prepare("UPDATE users SET reset_code = ?, reset_expires = ? WHERE id = ?");
                $upd->execute([$hashed, $expires_at, $user['id']]);

                // Prepare email content
                $subject = 'SparkLab Password Reset Code';
                $body  = "Hello " . ($user['name'] ?? '') . ",\n\n";
                $body .= "You requested a password reset for your SparkLab account.\n\n";
                $body .= "Your password reset code is: {$reset_code}\n\n";
                $body .= "This code will expire at: {$expires_at} (server time).\n\n";
                $body .= "If you did not request this, please ignore this email.\n\n";
                $body .= "Regards,\nSparkLab Team\n";

                // If PHPMailer is unavailable, fallback to mail() but warn developer
                if (!$phpmailer_available) {
                    // Try native mail() — will likely fail on XAMPP without proper sendmail config
                    $headers = "From: SparkLab <noreply@sparklab.com>\r\n";
                    $headers .= "Reply-To: noreply@sparklab.com\r\n";
                    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";

                    $sent = false;
                    try {
                        $sent = mail($email, $subject, $body, $headers);
                    } catch (Throwable $t) {
                        $sent = false;
                        error_log("mail() threw exception: " . $t->getMessage());
                    }

                    if ($sent) {
                        $success = $generic_message;
                    } else {
                        // Still inform generically, but log instructions for developer
                        $success = $generic_message . ' (Email not confirmed; install PHPMailer & configure SMTP for reliable delivery.)';
                        error_log("Forgot password: mail() failed for {$email}. Consider installing PHPMailer and configuring SMTP.");
                    }
                } else {
                    // Use PHPMailer SMTP
                    try {
                        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                        // Server settings
                        $mail->isSMTP();
                        $mail->Host = $smtpHost;
                        $mail->SMTPAuth = true;
                        $mail->Username = $smtpUser;
                        $mail->Password = $smtpPass;
                        $mail->SMTPSecure = $smtpSecure; // 'tls' or 'ssl'
                        $mail->Port = (int)$smtpPort;
                        // Recipients
                        $mail->setFrom($smtpUser, 'SparkLab');
                        $mail->addAddress($email, $user['name'] ?? '');
                        // Content
                        $mail->isHTML(false);
                        $mail->Subject = $subject;
                        $mail->Body    = $body;
                        // Send
                        $mail->send();
                        $success = $generic_message;
                    } catch (\PHPMailer\PHPMailer\Exception $ex) {
                        error_log("PHPMailer error sending reset to {$email}: " . $ex->getMessage());
                        // still show generic message to user
                        $success = $generic_message . ' (Email sending failed; check server logs.)';
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Forgot password error: " . $e->getMessage());
            $errors[] = 'An unexpected error occurred. Please try again later.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Forgot Password | SparkLab</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h4 class="card-title mb-3">Forgot Password</h4>

            <?php if ($errors): ?>
              <div class="alert alert-danger">
                <?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?>
              </div>
            <?php endif; ?>

            <?php if ($success): ?>
              <div class="alert alert-success">
                <?php echo e($success); ?>
              </div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="post" novalidate>
              <div class="mb-3">
                <label class="form-label">Enter your account email</label>
                <input type="email" name="email" class="form-control" required value="<?php echo e($_POST['email'] ?? ''); ?>">
              </div>

              <div class="d-grid gap-2">
                <button class="btn btn-primary" type="submit">Send Reset Code</button>
                <a href="login.php" class="btn btn-light">Back to Login</a>
              </div>
            </form>
            <?php else: ?>
              <div class="mt-3">
                <a href="login.php" class="btn btn-sm btn-outline-primary">Back to Login</a>
              </div>
            <?php endif; ?>

            <hr>
            <small class="text-muted">
              A 6-digit code will be sent to your email. It expires in 15 minutes.
              For local development, install PHPMailer via Composer and configure SMTP (Gmail app password or other SMTP).
            </small>

            <?php if (!$phpmailer_available): ?>
              <div class="mt-3 alert alert-warning">
                PHPMailer not found. Install it with: <code>composer require phpmailer/phpmailer</code>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
