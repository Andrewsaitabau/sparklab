<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

require_login();
verify_csrf();

$request_id = $_GET['request_id'] ?? null;
if (!$request_id) die('Invalid request.');

$stmt = $pdo->prepare("SELECT * FROM requests WHERE id = ? AND client_id = ?");
$stmt->execute([$request_id, current_user()['id']]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$request) die('Request not found.');

// For demo: simulate PayPal payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE requests SET payment_status = 'Paid', payment_ref = ? WHERE id = ?");
    $stmt->execute(['PAYPAL12345', $request_id]);
    redirect('client_dashboard.php');
}
?>
<!doctype html>
<html>
<head>
  <title>Pay with PayPal</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">
  <h3>Pay for "<?php echo e($request['title']); ?>" via PayPal</h3>
  <p>Amount: <?php echo $request['quote_usd'] !== null ? '$'.number_format($request['quote_usd'],2) : 'N/A'; ?></p>
  <form method="post">
    <input type="hidden" name="csrf" value="<?php echo e(csrf_token()); ?>">
    <button class="btn btn-primary">Simulate PayPal Payment</button>
    <a href="client_dashboard.php" class="btn btn-secondary">Cancel</a>
  </form>
</body>
</html>
