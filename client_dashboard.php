<?php 
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

require_login();
if (current_user()['role'] !== 'client') { redirect('admin_dashboard.php'); }

// Fetch user requests
$stmt = $pdo->prepare("SELECT * FROM requests WHERE client_id = ? ORDER BY id DESC");
$stmt->execute([current_user()['id']]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch cart items with status from admin
$cart_stmt = $pdo->prepare("
    SELECT c.id, s.name, s.price, c.quantity, c.status, c.admin_note, c.created_at, c.updated_at
    FROM cart_items c 
    JOIN services s ON c.service_id = s.id 
    WHERE c.client_id = ?
    ORDER BY c.created_at DESC
");
$cart_stmt->execute([current_user()['id']]);
$cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all available services
$services_stmt = $pdo->query("SELECT id, name, description, price FROM services ORDER BY id ASC");
$services = $services_stmt->fetchAll(PDO::FETCH_ASSOC);

$token = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Client Dashboard | SparkLab</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .navbar { background: #0d6efd; }
        .navbar-brand { font-weight: 600; }
        .service-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            padding: 1.5rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
        }
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
        }
        .service-icon {
            font-size: 2rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }
        .service-title {
            font-weight: 600;
            font-size: 1.1rem;
        }
        .cart-section {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .cart-item {
            border-bottom: 1px solid #e9ecef;
            padding: 0.75rem 0;
        }
        .cart-total {
            font-size: 1.2rem;
            font-weight: 700;
            text-align: right;
            margin-top: 1rem;
        }
        .status-pending { color: #ffc107; font-weight: bold; }
        .status-in-progress { color: #0d6efd; font-weight: bold; }
        .status-completed { color: #198754; font-weight: bold; }
        .status-cancelled { color: #dc3545; font-weight: bold; }
        .booking-card {
            background: #fff;
            border-radius: 8px;
            border-left: 4px solid #0d6efd;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .admin-note {
            background: #f8f9fa;
            border-left: 3px solid #6c757d;
            padding: 0.5rem;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-bolt me-2"></i>SparkLab
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="fas fa-home me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-light text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-5">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h4><i class="fas fa-user me-2"></i>Welcome, <?php echo e(current_user()['name']); ?>!</h4>
                        <p class="mb-0">Here you can book our ICT services and track your requests.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services Section -->
        <div class="row mb-5">
            <div class="col-12 mb-4">
                <h3 class="fw-bold"><i class="fas fa-concierge-bell me-2"></i>Our ICT Services</h3>
                <p class="text-muted">We offer a wide range of ICT services to meet all your technology needs.</p>
            </div>

            <?php foreach ($services as $service): ?>
                <div class="col-md-4 mb-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="fas fa-laptop-code"></i></div>
                        <h5 class="service-title"><?php echo e($service['name']); ?></h5>
                        <p class="text-muted small"><?php echo e($service['description']); ?></p>
                        <p class="fw-bold text-primary">KSh <?php echo number_format($service['price'], 2); ?></p>
                        <form method="post" action="add_to_cart.php">
                            <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
                            <input type="hidden" name="service_id" value="<?php echo e($service['id']); ?>">
                            <button class="btn btn-sm btn-primary">
                                <i class="fas fa-cart-plus me-1"></i> Add to Cart
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Cart Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="cart-section">
                    <h4><i class="fas fa-shopping-cart me-2"></i>Your Cart</h4>
                    <?php if(count($cart_items) > 0): ?>
                        <?php $total = 0; ?>
                        <?php foreach($cart_items as $item): 
                            $subtotal = $item['price'] * $item['quantity'];
                            $total += $subtotal;
                        ?>
                        <div class="cart-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo e($item['name']); ?></strong> 
                                (x<?php echo e($item['quantity']); ?>)
                                <?php if($item['status']): ?>
                                    <span class="badge ms-2 status-<?php echo str_replace(' ', '-', $item['status']); ?>">
                                        <?php echo ucfirst($item['status']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div>KSh <?php echo number_format($subtotal, 2); ?></div>
                        </div>
                        <?php endforeach; ?>
                        <div class="cart-total">Total: KSh <?php echo number_format($total, 2); ?></div>
                        <a href="payment.php" class="btn btn-success mt-3 w-100">
                            <i class="fas fa-credit-card me-2"></i> Proceed to Payment
                        </a>
                    <?php else: ?>
                        <p class="text-muted">Your cart is empty.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Cart Bookings Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-shopping-bag me-2"></i> Your Cart Bookings
                    </div>
                    <div class="card-body">
                        <?php if (count($cart_items) > 0): ?>
                            <?php foreach ($cart_items as $item): ?>
                                <div class="booking-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="fw-bold"><?php echo e($item['name']); ?></h6>
                                            <p class="mb-1">Quantity: <?php echo e($item['quantity']); ?> | 
                                                Price: KSh <?php echo number_format($item['price'], 2); ?> | 
                                                Total: KSh <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                            </p>
                                            <p class="mb-1">
                                                <small class="text-muted">
                                                    Booked on: <?php echo date('M j, Y g:i A', strtotime($item['created_at'])); ?>
                                                    <?php if ($item['updated_at'] && $item['updated_at'] != $item['created_at']): ?>
                                                        | Last updated: <?php echo date('M j, Y g:i A', strtotime($item['updated_at'])); ?>
                                                    <?php endif; ?>
                                                </small>
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <?php if($item['status']): ?>
                                                <?php 
                                                $status_class = '';
                                                switch($item['status']) {
                                                    case 'pending': $status_class = 'status-pending'; break;
                                                    case 'in progress': $status_class = 'status-in-progress'; break;
                                                    case 'completed': $status_class = 'status-completed'; break;
                                                    case 'cancelled': $status_class = 'status-cancelled'; break;
                                                    default: $status_class = 'text-muted';
                                                }
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>">
                                                    <?php echo ucfirst($item['status']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($item['admin_note'])): ?>
                                        <div class="admin-note">
                                            <strong><i class="fas fa-sticky-note me-1"></i>Admin Note:</strong>
                                            <?php echo e($item['admin_note']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No cart bookings yet. Add services to your cart to see them here.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requests Section -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-envelope me-2"></i> Your Service Requests
                    </div>
                    <div class="card-body">
                        <?php if ($rows): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Service</th>
                                            <th>Message</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rows as $row): ?>
                                            <tr>
                                                <td><?php echo e($row['service_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo e($row['message']); ?></td>
                                                <td>
                                                    <?php 
                                                    $status_class = '';
                                                    switch($row['status']) {
                                                        case 'pending': $status_class = 'status-pending'; break;
                                                        case 'in progress': $status_class = 'status-in-progress'; break;
                                                        case 'completed': $status_class = 'status-completed'; break;
                                                        case 'cancelled': $status_class = 'status-cancelled'; break;
                                                        default: $status_class = 'text-muted';
                                                    }
                                                    ?>
                                                    <span class="<?php echo $status_class; ?>">
                                                        <?php echo ucfirst($row['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No service requests submitted yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>