<?php
// reports.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

require_login();
$user = current_user();

// Handle report generation
$report_type = $_GET['report'] ?? 'overview';
$start_date  = $_GET['start_date'] ?? date('Y-m-01');
$end_date    = $_GET['end_date'] ?? date('Y-m-d');

// Build query depending on report type
switch ($report_type) {
    case 'financial':
        $stmt = $pdo->prepare("
            SELECT p.*, u.name AS client_name, s.name AS service_name, ci.quantity
            FROM payments p
            JOIN cart_items ci ON p.cart_item_id = ci.id
            JOIN users u ON p.client_id = u.id
            JOIN services s ON ci.service_id = s.id
            WHERE DATE(p.created_at) BETWEEN ? AND ?
              AND p.status = 'success'
            ORDER BY p.created_at DESC
        ");
        $params = [$start_date, $end_date];
        break;

    case 'bookings':
        $stmt = $pdo->prepare("
            SELECT ci.*, u.name AS client_name, s.name AS service_name, s.price,
                   (ci.quantity * s.price) AS total_price
            FROM cart_items ci
            JOIN users u ON ci.client_id = u.id
            JOIN services s ON ci.service_id = s.id
            WHERE DATE(ci.created_at) BETWEEN ? AND ?
            ORDER BY ci.created_at DESC
        ");
        $params = [$start_date, $end_date];
        break;

    case 'services':
        $stmt = $pdo->prepare("
            SELECT s.name,
                   COUNT(ci.id) AS booking_count,
                   SUM(ci.quantity * s.price) AS total_revenue,
                   AVG(ci.quantity * s.price) AS avg_revenue
            FROM services s
            LEFT JOIN cart_items ci
              ON s.id = ci.service_id
             AND DATE(ci.created_at) BETWEEN ? AND ?
            GROUP BY s.id, s.name
            ORDER BY total_revenue DESC
        ");
        $params = [$start_date, $end_date];
        break;

    default: // overview
        $stmt = $pdo->prepare("
            SELECT 
                (SELECT COUNT(*) FROM cart_items 
                 WHERE DATE(created_at) BETWEEN ? AND ?) AS total_bookings,
                (SELECT COUNT(*) FROM payments 
                 WHERE status = 'success' AND DATE(created_at) BETWEEN ? AND ?) AS successful_payments,
                (SELECT SUM(amount) FROM payments 
                 WHERE status = 'success' AND DATE(created_at) BETWEEN ? AND ?) AS total_revenue,
                (SELECT COUNT(*) FROM get_in_touch 
                 WHERE DATE(created_at) BETWEEN ? AND ?) AS total_messages
        ");
        $params = [$start_date, $end_date, $start_date, $end_date, $start_date, $end_date, $start_date, $end_date];
}

$stmt->execute($params);
$report_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals safely
$totals = [
    'revenue'  => 0,
    'bookings' => 0,
    'services' => 0
];

if ($report_type === 'financial') {
    $totals['revenue']  = array_sum(array_column($report_data, 'amount'));
    $totals['bookings'] = count($report_data);
    $totals['services'] = count(array_unique(array_column($report_data, 'service_name')));
} elseif ($report_type === 'bookings') {
    $totals['revenue']  = array_sum(array_column($report_data, 'total_price'));
    $totals['bookings'] = count($report_data);
    $totals['services'] = count(array_unique(array_column($report_data, 'service_name')));
} elseif ($report_type === 'services') {
    $totals['revenue']  = array_sum(array_column($report_data, 'total_revenue'));
    $totals['bookings'] = array_sum(array_column($report_data, 'booking_count'));
    $totals['services'] = count($report_data);
} elseif ($report_type === 'overview') {
    $totals = $report_data[0] ?? $totals;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Reports | SparkLab</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
    .report-card { background: white; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    @media print {
        .no-print { display: none !important; }
        .content { margin-left: 0 !important; }
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar no-print">
    <h4 class="text-center mb-4"><i class="fas fa-bolt me-2"></i>SparkLab</h4>
    <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
    <a href="ai_predictions.php"><i class="fas fa-chart-line me-2"></i>AI Predictions</a>
    <a href="reports.php" class="active"><i class="fas fa-file-pdf me-2"></i>Reports</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
  </div>

  <!-- Main Content -->
  <div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2><i class="fas fa-file-pdf me-2"></i>Reports & Analytics</h2>
      <div>
        <button class="btn btn-success me-2" onclick="generatePDF()">
          <i class="fas fa-download me-1"></i>Download PDF
        </button>
        <button class="btn btn-primary" onclick="window.print()">
          <i class="fas fa-print me-1"></i>Print Report
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4 no-print">
      <div class="card-body">
        <form method="get" class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Report Type</label>
            <select name="report" class="form-select" onchange="this.form.submit()">
              <option value="overview"  <?= $report_type==='overview' ? 'selected':'' ?>>Overview Report</option>
              <option value="financial" <?= $report_type==='financial' ? 'selected':'' ?>>Financial Report</option>
              <option value="bookings"  <?= $report_type==='bookings' ? 'selected':'' ?>>Bookings Report</option>
              <option value="services"  <?= $report_type==='services' ? 'selected':'' ?>>Services Report</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">&nbsp;</label>
            <button type="submit" class="btn btn-primary w-100">Generate</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Report Header -->
    <div class="report-card text-center mb-4">
      <h1>SparkLab <?= ucfirst($report_type) ?> Report</h1>
      <p class="lead">Period: <?= date('F j, Y', strtotime($start_date)) ?> to <?= date('F j, Y', strtotime($end_date)) ?></p>
      <p class="text-muted">Generated on: <?= date('F j, Y g:i A') ?> by <?= e($user['name']) ?></p>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
      <div class="col-md-4">
        <div class="report-card bg-primary text-white text-center">
          <h3>KSh <?= number_format($totals['revenue'] ?? 0, 2) ?></h3>
          <p>Total Revenue</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="report-card bg-success text-white text-center">
          <h3><?= $totals['bookings'] ?? 0 ?></h3>
          <p>Total Bookings</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="report-card bg-info text-white text-center">
          <h3><?= $totals['services'] ?? 0 ?></h3>
          <p>Services Offered</p>
        </div>
      </div>
    </div>

    <!-- Report Body -->
    <div class="report-card">
      <?php if ($report_type === 'overview'): ?>
        <!-- Overview table -->
        <h4 class="mb-4">Business Overview</h4>
        <table class="table table-bordered">
          <thead class="table-dark"><tr><th>Metric</th><th>Value</th><th>Description</th></tr></thead>
          <tbody>
            <tr><td>Total Bookings</td><td><strong><?= $totals['total_bookings'] ?? 0 ?></strong></td><td>Number of bookings</td></tr>
            <tr><td>Successful Payments</td><td><strong><?= $totals['successful_payments'] ?? 0 ?></strong></td><td>Completed payments</td></tr>
            <tr><td>Total Revenue</td><td><strong>KSh <?= number_format($totals['total_revenue'] ?? 0, 2) ?></strong></td><td>Revenue earned</td></tr>
            <tr><td>Customer Messages</td><td><strong><?= $totals['total_messages'] ?? 0 ?></strong></td><td>Get-in-touch inquiries</td></tr>
          </tbody>
        </table>

      <?php elseif ($report_type === 'financial'): ?>
        <!-- Financial table -->
        <h4 class="mb-4">Financial Report</h4>
        <table class="table table-bordered table-striped">
          <thead class="table-dark"><tr><th>Date</th><th>Client</th><th>Service</th><th>Qty</th><th>Amount</th><th>Receipt</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach ($report_data as $row): ?>
            <tr>
              <td><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
              <td><?= e($row['client_name']) ?></td>
              <td><?= e($row['service_name']) ?></td>
              <td><?= e($row['quantity']) ?></td>
              <td>KSh <?= number_format($row['amount'], 2) ?></td>
              <td><code><?= e($row['mpesa_receipt_number'] ?? 'N/A') ?></code></td>
              <td><span class="badge bg-success">Success</span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>

      <?php elseif ($report_type === 'bookings'): ?>
        <!-- Bookings table -->
        <h4 class="mb-4">Bookings Report</h4>
        <table class="table table-bordered table-striped">
          <thead class="table-dark"><tr><th>Date</th><th>Client</th><th>Service</th><th>Qty</th><th>Unit Price</th><th>Total</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach ($report_data as $row): ?>
            <tr>
              <td><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
              <td><?= e($row['client_name']) ?></td>
              <td><?= e($row['service_name']) ?></td>
              <td><?= e($row['quantity']) ?></td>
              <td>KSh <?= number_format($row['price'], 2) ?></td>
              <td>KSh <?= number_format($row['total_price'], 2) ?></td>
              <td><span class="badge bg-secondary"><?= ucfirst($row['status'] ?? 'N/A') ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>

      <?php elseif ($report_type === 'services'): ?>
        <!-- Services table -->
        <h4 class="mb-4">Services Performance</h4>
        <table class="table table-bordered table-striped">
          <thead class="table-dark"><tr><th>Service</th><th>Bookings</th><th>Total Revenue</th><th>Average</th><th>Performance</th></tr></thead>
          <tbody>
          <?php foreach ($report_data as $row): ?>
            <tr>
              <td><?= e($row['name']) ?></td>
              <td><?= $row['booking_count'] ?></td>
              <td>KSh <?= number_format($row['total_revenue'] ?? 0, 2) ?></td>
              <td>KSh <?= number_format($row['avg_revenue'] ?? 0, 2) ?></td>
              <td>
                <?php if ($row['booking_count'] > 5): ?>
                  <span class="badge bg-success">High</span>
                <?php elseif ($row['booking_count'] > 2): ?>
                  <span class="badge bg-warning">Medium</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Low</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

<script>
function generatePDF(){ window.print(); }
setInterval(()=>{ if(!document.hidden) location.reload(); },300000);
</script>
</body>
</html>
