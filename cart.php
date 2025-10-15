<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_login();

$client_id = current_user()['id'];

// Fixed SQL query with proper JOIN to get service name
$stmt = $pdo->prepare("
    SELECT c.*, s.name as service_name, s.price 
    FROM cart_items c 
    JOIN services s ON c.service_id = s.id 
    WHERE c.client_id = ?
");
$stmt->execute([$client_id]);
$cart = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = array_sum(array_map(fn($c) => $c['price'] * $c['quantity'], $cart));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Your Cart | SparkLab</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .container { max-width: 800px; }
        .table { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Your Cart</h2>
        <a href="client_dashboard.php" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>
    
    <?php if ($cart): ?>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th>Service</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cart as $item): ?>
                        <tr>
                            <td><?= e($item['service_name']); ?></td>
                            <td>KSh <?= number_format($item['price'], 2); ?></td>
                            <td><?= $item['quantity']; ?></td>
                            <td>KSh <?= number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mt-4 p-3 bg-light rounded">
            <h4 class="mb-0">Total: <span class="text-primary">KSh <?= number_format($total, 2); ?></span></h4>
            <a href="payment.php" class="btn btn-success btn-lg">
                <i class="fas fa-credit-card me-2"></i> Proceed to Payment
            </a>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">Your cart is empty</h4>
            <p class="text-muted">Add some services to your cart to get started.</p>
            <a href="client_dashboard.php" class="btn btn-primary">
                <i class="fas fa-concierge-bell me-1"></i> Browse Services
            </a>
        </div>
    <?php endif; ?>
</body>
</html>