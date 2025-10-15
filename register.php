<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php'; // make sure e() function is loaded


$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $csrf  = $_POST['csrf'] ?? '';

    if ($csrf !== ($_SESSION['csrf'] ?? '')) $errors[] = 'Invalid CSRF token.';
    if ($name === '' || $email === '' || $pass === '') $errors[] = 'All fields are required.';

    if (!$errors) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors[] = 'Email is already in use.';
    }

    if (!$errors) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?, 'client')");
        $stmt->execute([$name, $email, $hash]);
        header('Location: login.php?registered=1');
        exit;
    }
}
$token = csrf_token();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Register | SparkLab</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f8f9fa;
      color: #000; /* make all text black */
    }
    .card {
      max-width: 500px;
      margin: auto;
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      background: #fff;
    }
    h2 {
      font-weight: bold;
      text-align: center;
    }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
  <div class="card">
    <h2>Client Registration</h2>
    <?php if ($errors): ?>
      <div class="alert alert-danger mt-3"><?php echo e(implode('<br>', $errors)); ?></div>
    <?php endif; ?>
    <form method="post" class="mt-4">
      <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input name="name" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input name="password" type="password" class="form-control" required>
      </div>
      <div class="d-grid">
        <button class="btn btn-primary">Create Account</button>
      </div>
      <p class="text-center mt-3">
        Already have an account? <a href="login.php">Login here</a>
      </p>
    </form>
  </div>
</body>
</html>
