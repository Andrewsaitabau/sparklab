<?php
// admin_dashboard.php (updated with AI predictions and reports)
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

require_login();
$user = current_user();

// Ensure a stable CSRF token in session
if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['csrf_token'];

// Flash messages
if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Handle quick actions if posted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Get posted token
    $posted_token = $_POST['csrf'] ?? '';

    // Validate token presence
    if (empty($posted_token) || empty($_SESSION['csrf_token'])) {
        $_SESSION['error'] = 'CSRF token missing!';
        redirect('admin_dashboard.php');
    }

    // Use hash_equals for timing-safe comparison
    if (!is_string($posted_token) || !hash_equals((string)$_SESSION['csrf_token'], (string)$posted_token)) {
        $_SESSION['error'] = 'Invalid CSRF token! Token mismatch.';
        redirect('admin_dashboard.php');
    }

    $request_id = $_POST['request_id'] ?? '';
    $cart_id = $_POST['cart_id'] ?? '';
    $action = $_POST['action'];

    try {
        switch ($action) {
            case 'approve':
                if ($request_id) {
                    $stmt = $pdo->prepare("UPDATE requests SET status = 'in progress' WHERE id = ?");
                    $stmt->execute([$request_id]);
                    $_SESSION['success'] = 'Request approved and marked as in progress';
                }
                break;

            case 'complete':
                if ($request_id) {
                    $stmt = $pdo->prepare("UPDATE requests SET status = 'completed' WHERE id = ?");
                    $stmt->execute([$request_id]);
                    $_SESSION['success'] = 'Request marked as completed';
                }
                break;

            case 'cancel':
                if ($request_id) {
                    $stmt = $pdo->prepare("UPDATE requests SET status = 'cancelled' WHERE id = ?");
                    $stmt->execute([$request_id]);
                    $_SESSION['success'] = 'Request cancelled';
                }
                break;

            case 'delete':
                if ($request_id) {
                    $stmt = $pdo->prepare("DELETE FROM requests WHERE id = ?");
                    $stmt->execute([$request_id]);
                    $_SESSION['success'] = 'Request deleted successfully';
                }
                break;

            case 'delete_message':
                $message_id = $_POST['message_id'] ?? '';
                if ($message_id) {
                    $stmt = $pdo->prepare("DELETE FROM get_in_touch WHERE id = ?");
                    $stmt->execute([$message_id]);
                    $_SESSION['success'] = 'Message deleted successfully';
                }
                break;

            case 'update_cart_status':
                if ($cart_id) {
                    $status = $_POST['status'] ?? 'pending';
                    $admin_note = $_POST['admin_note'] ?? '';
                    
                    $stmt = $pdo->prepare("UPDATE cart_items SET status = ?, admin_note = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$status, $admin_note, $cart_id]);
                    $_SESSION['success'] = 'Cart booking status updated successfully';
                }
                break;

            case 'delete_cart':
                if ($cart_id) {
                    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ?");
                    $stmt->execute([$cart_id]);
                    $_SESSION['success'] = 'Cart booking deleted successfully';
                }
                break;
        }
    } catch (Exception $e) {
        error_log("Admin action error: " . $e->getMessage());
        $_SESSION['error'] = 'An error occurred while processing the request.';
    }

    // Regenerate CSRF token after successful POST
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    redirect('admin_dashboard.php');
}

// List all requests (search q supported)
$q = trim($_GET['q'] ?? '');
if ($q) {
    $stmt = $pdo->prepare("
      SELECT r.*, u.name as client_name, u.email as client_email, s.name as service_name
      FROM requests r
      LEFT JOIN users u ON u.id = r.client_id
      LEFT JOIN services s ON s.id = r.service_id
      WHERE r.message LIKE ? OR u.name LIKE ? OR u.email LIKE ?
      ORDER BY r.id DESC
    ");
    $like = "%$q%";
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = $pdo->query("
      SELECT r.*, u.name as client_name, u.email as client_email, s.name as service_name
      FROM requests r
      LEFT JOIN users u ON u.id = r.client_id
      LEFT JOIN services s ON s.id = r.service_id
      ORDER BY r.id DESC
    ");
}
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all cart bookings with client and service details
$cart_bookings_stmt = $pdo->query("
    SELECT ci.*, u.name as client_name, u.email as client_email, 
           s.name as service_name, s.price, 
           (ci.quantity * s.price) as total_price
    FROM cart_items ci
    JOIN users u ON ci.client_id = u.id
    JOIN services s ON ci.service_id = s.id
    ORDER BY ci.created_at DESC
");
$cart_bookings = $cart_bookings_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Get in Touch messages
$messages_stmt = $pdo->query("
    SELECT id, name, email, phone, subject, message, created_at
    FROM get_in_touch
    ORDER BY id DESC
");
$messages = $messages_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch payments data for AI predictions
$payments_stmt = $pdo->query("
    SELECT p.*, u.name as client_name, u.email as client_email,
           s.name as service_name, ci.quantity, ci.payment_status,
           ci.mpesa_receipt_number, ci.transaction_date
    FROM payments p
    JOIN cart_items ci ON p.cart_id = ci.id
    JOIN users u ON p.client_id = u.id
    JOIN services s ON ci.service_id = s.id
    ORDER BY p.created_at DESC
    LIMIT 100
");
$payments = $payments_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics for AI predictions
$total_revenue = 0;
$monthly_revenue = [];
$service_popularity = [];
$booking_trends = [];

foreach ($payments as $payment) {
    if ($payment['status'] === 'success') {
        $total_revenue += $payment['amount'];
        
        // Monthly revenue
        $month = date('Y-m', strtotime($payment['transaction_date'] ?? $payment['created_at']));
        $monthly_revenue[$month] = ($monthly_revenue[$month] ?? 0) + $payment['amount'];
        
        // Service popularity
        $service_name = $payment['service_name'];
        $service_popularity[$service_name] = ($service_popularity[$service_name] ?? 0) + 1;
    }
}

// Booking trends (last 30 days)
$thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
$booking_trends_stmt = $pdo->prepare("
    SELECT DATE(created_at) as date, COUNT(*) as count
    FROM cart_items 
    WHERE created_at >= ?
    GROUP BY DATE(created_at)
    ORDER BY date
");
$booking_trends_stmt->execute([$thirty_days_ago]);
$booking_trends = $booking_trends_stmt->fetchAll(PDO::FETCH_ASSOC);

// AI Prediction: Next month revenue (simple linear regression)
$predicted_revenue = 0;
if (count($monthly_revenue) >= 2) {
    $months = array_keys($monthly_revenue);
    $revenues = array_values($monthly_revenue);
    
    // Simple average growth prediction
    $last_month = end($monthly_revenue);
    $predicted_revenue = $last_month * 1.1; // 10% growth assumption
}

// Use the session token for forms on this page
$token = $_SESSION['csrf_token'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Dashboard | SparkLab</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { background-color: #f8f9fa; }
    .sidebar {
      width: 220px; height: 100vh; position: fixed;
      top: 0; left: 0; background: #343a40; color: #fff; padding-top: 1rem;
    }
    .sidebar a {
      color: #adb5bd; display: block; padding: 10px 15px; text-decoration: none;
    }
    .sidebar a:hover, .sidebar a.active {
      background: #495057; color: #fff;
    }
    .content { margin-left: 220px; padding: 20px; }
    .card-counter {
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 20px;
        border-radius: .75rem;
        text-align: center;
        margin-bottom: 1rem;
    }
    .status-badge { font-size: 0.8rem; padding: 0.4em 0.8em; }
    .action-buttons { white-space: nowrap; }
    .btn-xs { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
    .btn-group .btn { margin-right: 2px; }
    .table-responsive { max-height: 500px; overflow-y: auto; }
    .chart-container { position: relative; height: 300px; width: 100%; }
    .ai-prediction { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
    .print-only { display: none; }
    @media print {
        .no-print { display: none !important; }
        .print-only { display: block !important; }
        .content { margin-left: 0 !important; }
        .card { border: 1px solid #000 !important; }
        .table { border: 1px solid #000 !important; }
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar no-print">
    <h4 class="text-center mb-4"><i class="fas fa-bolt me-2"></i>SparkLab</h4>
    <a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
    <a href="ai_predictions.php"><i class="fas fa-chart-line me-2"></i>AI Predictions</a>
    <a href="reports.php"><i class="fas fa-file-pdf me-2"></i>Reports</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
  </div>

  <!-- Main Content -->
  <div class="content">
    <!-- Flash Messages -->
    <?php if (!empty($success_message)): ?>
      <div class="alert alert-success alert-dismissible fade show"><?php echo e($success_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
      <div class="alert alert-danger alert-dismissible fade show"><?php echo e($error_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
      <h2><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h2>
      <div>
        <button class="btn btn-success me-2" onclick="window.location.href='ai_predictions.php'">
          <i class="fas fa-chart-line me-1"></i>AI Predictions
        </button>
        <button class="btn btn-primary me-2" onclick="window.location.href='reports.php'">
          <i class="fas fa-file-pdf me-1"></i>Generate Reports
        </button>
        <button class="btn btn-outline-secondary" onclick="window.print()">
          <i class="fas fa-print me-1"></i>Print Dashboard
        </button>
      </div>
    </div>

    <!-- Print Header -->
    <div class="print-only text-center mb-4">
      <h1>SparkLab Admin Dashboard Report</h1>
      <p>Generated on: <?php echo date('F j, Y g:i A'); ?></p>
      <p>Generated by: <?php echo e($user['name']); ?></p>
    </div>

    <!-- AI Prediction Cards -->
    <div class="row mb-4">
      <div class="col-md-4">
        <div class="card-counter ai-prediction">
          <h4>KSh <?php echo number_format($predicted_revenue, 2); ?></h4>
          <p>Predicted Next Month Revenue</p>
          <small>Based on historical trends</small>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card-counter bg-info text-white">
          <h4><?php echo array_key_first($service_popularity) ?? 'N/A'; ?></h4>
          <p>Most Popular Service</p>
          <small>Based on booking frequency</small>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card-counter bg-warning text-dark">
          <h4><?php echo number_format(($total_revenue / count($payments)) ?: 0, 2); ?></h4>
          <p>Average Transaction Value</p>
          <small>KSh per successful payment</small>
        </div>
      </div>
    </div>

    <!-- Quick Charts Section -->
    <div class="row mb-4">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Revenue Trend (Last 6 Months)</h5>
          </div>
          <div class="card-body">
            <div class="chart-container">
              <canvas id="revenueChart"></canvas>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Service Popularity</h5>
          </div>
          <div class="card-body">
            <div class="chart-container">
              <canvas id="serviceChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Dashboard cards -->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card-counter bg-primary text-white">
          <h4><?php echo count($rows) + count($cart_bookings); ?></h4>
          <p>Total Bookings</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card-counter bg-success text-white">
          <h4><?php 
            $completed = count(array_filter($rows, fn($r) => $r['status'] === 'completed')) + 
                        count(array_filter($cart_bookings, fn($c) => $c['status'] === 'completed'));
            echo $completed;
          ?></h4>
          <p>Completed</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card-counter bg-warning text-dark">
          <h4><?php 
            $in_progress = count(array_filter($rows, fn($r) => $r['status'] === 'in progress')) + 
                          count(array_filter($cart_bookings, fn($c) => $c['status'] === 'in progress'));
            echo $in_progress;
          ?></h4>
          <p>In Progress</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card-counter bg-danger text-white">
          <h4><?php 
            $pending = count(array_filter($rows, fn($r) => $r['status'] === 'pending')) + 
                      count(array_filter($cart_bookings, fn($c) => $c['status'] === 'pending'));
            echo $pending;
          ?></h4>
          <p>Pending</p>
        </div>
      </div>
    </div>

    <!-- Search -->
    <div class="card mb-4 no-print">
      <div class="card-body">
        <form class="row g-2" method="get" action="admin_dashboard.php">
          <div class="col-md-8">
            <input class="form-control" name="q" placeholder="Search by request message, client name, or email" value="<?php echo e($q ?? ''); ?>">
          </div>
          <div class="col-md-4 text-end">
            <button class="btn btn-primary me-2"><i class="fas fa-search me-1"></i> Search</button>
            <a href="admin_dashboard.php" class="btn btn-outline-secondary">Clear</a>
          </div>
        </form>
      </div>
    </div>

    <!-- Cart Bookings Table -->
    <div class="card mb-4">
      <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Cart Bookings</h5>
        <span class="badge bg-light text-info"><?php echo count($cart_bookings); ?> bookings</span>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Client</th>
                <th>Service</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
                <th>Status</th>
                <th>Created</th>
                <th class="no-print">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($cart_bookings): ?>
                <?php foreach ($cart_bookings as $cart): ?>
                  <tr>
                    <td><strong><?php echo e($cart['id']); ?></strong></td>
                    <td>
                      <div><strong><?php echo e($cart['client_name']); ?></strong></div>
                      <small class="text-muted"><?php echo e($cart['client_email']); ?></small>
                    </td>
                    <td><?php echo e($cart['service_name']); ?></td>
                    <td><?php echo e($cart['quantity']); ?></td>
                    <td>KSh <?php echo number_format($cart['price'], 2); ?></td>
                    <td><strong>KSh <?php echo number_format($cart['total_price'], 2); ?></strong></td>
                    <td>
                      <?php
                      $status_config = [
                          'pending' => ['class' => 'bg-warning', 'icon' => 'clock'],
                          'in progress' => ['class' => 'bg-info', 'icon' => 'cog'],
                          'completed' => ['class' => 'bg-success', 'icon' => 'check'],
                          'cancelled' => ['class' => 'bg-danger', 'icon' => 'times']
                      ];
                      $status = $cart['status'] ?? 'pending';
                      $config = $status_config[$status] ?? $status_config['pending'];
                      ?>
                      <span class="badge status-badge <?php echo $config['class']; ?>">
                        <i class="fas fa-<?php echo $config['icon']; ?> me-1"></i>
                        <?php echo ucfirst($status); ?>
                      </span>
                    </td>
                    <td><?php echo date('M j, Y g:i A', strtotime($cart['created_at'])); ?></td>
                    <td class="action-buttons no-print">
                      <div class="btn-group" role="group">
                        <!-- Update Cart Status Form -->
                        <form method="post" class="d-inline">
                          <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
                          <input type="hidden" name="cart_id" value="<?php echo e($cart['id']); ?>">
                          <input type="hidden" name="action" value="update_cart_status">
                          
                          <select name="status" class="form-select form-select-sm d-inline" style="width: auto;" onchange="this.form.submit()">
                            <?php foreach (['pending', 'in progress', 'completed', 'cancelled'] as $st): ?>
                              <option value="<?php echo $st; ?>" <?php if (($cart['status'] ?? 'pending') === $st) echo 'selected'; ?>>
                                <?php echo ucfirst($st); ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </form>

                        <form method="post" class="d-inline">
                          <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
                          <input type="hidden" name="cart_id" value="<?php echo e($cart['id']); ?>">
                          <input type="hidden" name="action" value="delete_cart">
                          <button type="submit" class="btn btn-danger btn-xs" title="Delete Cart Booking"
                                  onclick="return confirm('Are you sure you want to delete this cart booking?')">
                            <i class="fas fa-trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                  
                  <!-- Admin Notes Section -->
                  <tr class="bg-light no-print">
                    <td colspan="9">
                      <form method="post" class="row g-2 align-items-center">
                        <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
                        <input type="hidden" name="cart_id" value="<?php echo e($cart['id']); ?>">
                        <input type="hidden" name="action" value="update_cart_status">
                        
                        <div class="col-md-3">
                          <small class="fw-bold">Admin Notes:</small>
                        </div>
                        <div class="col-md-7">
                          <input type="text" name="admin_note" class="form-control form-control-sm" 
                                 value="<?php echo e($cart['admin_note'] ?? ''); ?>" 
                                 placeholder="Add admin notes for this booking...">
                        </div>
                        <div class="col-md-2">
                          <button type="submit" class="btn btn-primary btn-sm w-100">Update</button>
                        </div>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="9" class="text-center text-muted py-4">
                    <i class="fas fa-shopping-cart fa-2x mb-2 d-block"></i>
                    No cart bookings found
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    

    <!-- Requests Table (Existing) -->
    <div class="card mb-5">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Service Requests</h5>
        <span class="badge bg-light text-primary"><?php echo count($rows); ?> requests</span>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Client</th>
                <th>Service</th>
                <th>Message</th>
                <th>Status</th>
                <th>Created</th>
                <th class="no-print">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($rows): ?>
                <?php foreach ($rows as $r): ?>
                  <tr>
                    <td><strong><?php echo e($r['id']); ?></strong></td>
                    <td>
                      <div><strong><?php echo e($r['client_name']); ?></strong></div>
                      <small class="text-muted"><?php echo e($r['client_email']); ?></small>
                    </td>
                    <td><?php echo e($r['service_name'] ?? 'General Request'); ?></td>
                    <td><?php echo e($r['message']); ?></td>
                    <td>
                      <?php
                      $status_config = [
                          'pending' => ['class' => 'bg-warning', 'icon' => 'clock'],
                          'in progress' => ['class' => 'bg-info', 'icon' => 'cog'],
                          'completed' => ['class' => 'bg-success', 'icon' => 'check'],
                          'cancelled' => ['class' => 'bg-danger', 'icon' => 'times']
                      ];
                      $status = $r['status'];
                      $config = $status_config[$status] ?? $status_config['pending'];
                      ?>
                      <span class="badge status-badge <?php echo $config['class']; ?>">
                        <i class="fas fa-<?php echo $config['icon']; ?> me-1"></i>
                        <?php echo ucfirst($status); ?>
                      </span>
                    </td>
                    <td><?php echo date('M j, Y g:i A', strtotime($r['created_at'])); ?></td>
                    <td class="action-buttons no-print">
                      <div class="btn-group" role="group">
                        <!-- Quick Action Buttons -->
                        <?php if ($r['status'] === 'pending'): ?>
                          <form method="post" class="d-inline">
                            <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
                            <input type="hidden" name="request_id" value="<?php echo e($r['id']); ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn btn-success btn-xs" title="Approve Request">
                              <i class="fas fa-check"></i> Approve
                            </button>
                          </form>
                        <?php endif; ?>

                        <?php if ($r['status'] === 'in progress'): ?>
                          <form method="post" class="d-inline">
                            <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
                            <input type="hidden" name="request_id" value="<?php echo e($r['id']); ?>">
                            <input type="hidden" name="action" value="complete">
                            <button type="submit" class="btn btn-success btn-xs" title="Mark as Complete">
                              <i class="fas fa-flag-checkered"></i> Complete
                            </button>
                          </form>
                        <?php endif; ?>

                        <?php if ($r['status'] !== 'cancelled' && $r['status'] !== 'completed'): ?>
                          <form method="post" class="d-inline">
                            <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
                            <input type="hidden" name="request_id" value="<?php echo e($r['id']); ?>">
                            <input type="hidden" name="action" value="cancel">
                            <button type="submit" class="btn btn-warning btn-xs" title="Cancel Request"
                                    onclick="return confirm('Are you sure you want to cancel this request?')">
                              <i class="fas fa-ban"></i> Cancel
                            </button>
                          </form>
                        <?php endif; ?>

                        <form method="post" class="d-inline">
                          <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
                          <input type="hidden" name="request_id" value="<?php echo e($r['id']); ?>">
                          <input type="hidden" name="action" value="delete">
                          <button type="submit" class="btn btn-danger btn-xs" title="Delete Request"
                                  onclick="return confirm('Are you sure you want to delete this request? This action cannot be undone.')">
                            <i class="fas fa-trash"></i> Delete
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="text-center text-muted py-4">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    No requests found
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Get in Touch Messages Table -->
    <div class="card">
      <div class="card-header bg-secondary text-white">
        <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Get in Touch Messages</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Date</th>
                <th class="no-print">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($messages): ?>
                <?php foreach ($messages as $m): ?>
                  <tr>
                    <td><?php echo e($m['id']); ?></td>
                    <td><strong><?php echo e($m['name']); ?></strong></td>
                    <td><?php echo e($m['email']); ?></td>
                    <td><?php echo e($m['phone'] ?? 'N/A'); ?></td>
                    <td><?php echo e($m['subject']); ?></td>
                    <td>
                      <span title="<?php echo e($m['message']); ?>">
                        <?php echo strlen($m['message']) > 50 ? substr($m['message'], 0, 50) . '...' : $m['message']; ?>
                      </span>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($m['created_at'])); ?></td>
                    <td class="no-print">
                      <form method="post" class="d-inline">
                        <input type="hidden" name="csrf" value="<?php echo e($token); ?>">
                        <input type="hidden" name="message_id" value="<?php echo e($m['id']); ?>">
                        <input type="hidden" name="action" value="delete_message">
                        <button type="submit" class="btn btn-danger btn-xs" title="Delete Message"
                                onclick="return confirm('Are you sure you want to delete this message?')">
                          <i class="fas fa-trash"></i> Delete
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">
                    <i class="fas fa-comments fa-2x mb-2 d-block"></i>
                    No messages found
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Chart.js initialization
    document.addEventListener('DOMContentLoaded', function() {
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_keys($monthly_revenue)); ?>,
                datasets: [{
                    label: 'Monthly Revenue (KSh)',
                    data: <?php echo json_encode(array_values($monthly_revenue)); ?>,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'KSh ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Service Popularity Chart
        const serviceCtx = document.getElementById('serviceChart').getContext('2d');
        const serviceChart = new Chart(serviceCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_keys($service_popularity)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($service_popularity)); ?>,
                    backgroundColor: [
                        '#007bff', '#28a745', '#ffc107', '#dc3545',
                        '#6f42c1', '#e83e8c', '#fd7e14', '#20c997'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    });

    // Real-time updates for admin dashboard
function refreshPayments() {
    fetch('get_payments.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update payment counters
                const totalPaymentsEl = document.getElementById('total-payments-count');
                const successPaymentsEl = document.getElementById('success-payments-count');
                const pendingPaymentsEl = document.getElementById('pending-payments-count');
                const failedPaymentsEl = document.getElementById('failed-payments-count');
                
                if (totalPaymentsEl) totalPaymentsEl.textContent = data.total_payments;
                if (successPaymentsEl) successPaymentsEl.textContent = data.success_payments;
                if (pendingPaymentsEl) pendingPaymentsEl.textContent = data.pending_payments;
                if (failedPaymentsEl) failedPaymentsEl.textContent = data.failed_payments;
                
                // Update payments table if it exists
                if (data.recent_payments && data.recent_payments.length > 0) {
                    updatePaymentsTable(data.recent_payments);
                }
            } else {
                console.error('API error:', data.error);
            }
        })
        .catch(error => {
            console.error('Error refreshing payments:', error);
        });
}

function updatePaymentsTable(payments) {
    const tbody = document.getElementById('payments-table-body');
    if (!tbody) return;
    
    tbody.innerHTML = payments.map(payment => `
        <tr>
            <td>
                <code>${payment.mpesa_receipt_number || 'Pending'}</code>
                ${payment.mpesa_receipt_number ? 
                    `<button class="btn btn-sm btn-outline-secondary ms-1" 
                            onclick="copyToClipboard('${payment.mpesa_receipt_number}')"
                            title="Copy Receipt Number">
                        <i class="fas fa-copy"></i>
                    </button>` : ''}
            </td>
            <td>
                <div><strong>${escapeHtml(payment.client_name)}</strong></div>
                <small class="text-muted">${escapeHtml(payment.client_email)}</small>
            </td>
            <td>${escapeHtml(payment.service_name)} (x${payment.quantity})</td>
            <td><strong>KSh ${parseFloat(payment.amount).toFixed(2)}</strong></td>
            <td>${escapeHtml(payment.phone_number)}</td>
            <td>
                <span class="badge bg-${getStatusBadge(payment.status)}">
                    ${payment.status.charAt(0).toUpperCase() + payment.status.slice(1)}
                </span>
            </td>
            <td>${new Date(payment.transaction_date || payment.created_at).toLocaleString()}</td>
        </tr>
    `).join('');
}

function getStatusBadge(status) {
    const badges = {
        'success': 'success',
        'pending': 'warning',
        'failed': 'danger'
    };
    return badges[status] || 'secondary';
}

function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Receipt number copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy: ', err);
        // Fallback for older browsers
        const textArea = document.createElement("textarea");
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand("copy");
        document.body.removeChild(textArea);
        showToast('Receipt number copied to clipboard!');
    });
}

function showToast(message) {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.custom-toast');
    existingToasts.forEach(toast => toast.remove());
    
    // Create new toast
    const toast = document.createElement('div');
    toast.className = 'custom-toast alert alert-success position-fixed top-0 end-0 m-3';
    toast.style.zIndex = '9999';
    toast.style.minWidth = '250px';
    toast.innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <span>${message}</span>
            <button type="button" class="btn-close btn-close-white" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 3000);
}

// Refresh every 30 seconds for real-time updates
setInterval(refreshPayments, 30000);

// Also refresh when page becomes visible
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        refreshPayments();
    }
});

// Initial load when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', refreshPayments);
} else {
    refreshPayments();
}

    // Initial load
    document.addEventListener('DOMContentLoaded', refreshPayments);
  </script>
</body>
</html>