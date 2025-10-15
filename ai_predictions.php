<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php'; // or auth.php, depending on your project
require_login();


// Get selected report type
$report_type = $_GET['report_type'] ?? 'overview';

// Initialize totals safely
$totals = [
    'revenue' => 0,
    'bookings' => 0,
    'services' => 0,
    'successful_payments' => 0,
    'total_messages' => 0
];

// Fetch totals from database
try {
    // Revenue
    $stmt = $pdo->query("SELECT SUM(amount) as revenue FROM payments WHERE status='success'");
    $totals['revenue'] = (float)($stmt->fetch()['revenue'] ?? 0);

    // Bookings
    $stmt = $pdo->query("SELECT COUNT(*) as bookings FROM bookings");
    $totals['bookings'] = (int)($stmt->fetch()['bookings'] ?? 0);

    // Services
    $stmt = $pdo->query("SELECT COUNT(*) as services FROM services");
    $totals['services'] = (int)($stmt->fetch()['services'] ?? 0);

    // Successful Payments
    $stmt = $pdo->query("SELECT COUNT(*) as successful_payments FROM payments WHERE status='success'");
    $totals['successful_payments'] = (int)($stmt->fetch()['successful_payments'] ?? 0);

    // Customer Messages
    $stmt = $pdo->query("SELECT COUNT(*) as total_messages FROM messages");
    $totals['total_messages'] = (int)($stmt->fetch()['total_messages'] ?? 0);

} catch (Exception $e) {
    error_log("Error fetching totals: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>AI Predictions & Reports</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body { background: #f4f6f9; }
    .report-card {
      border-radius: 10px;
      padding: 20px;
      color: white;
      font-weight: bold;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .report-card h3 { margin: 0; font-size: 1.8rem; }
    .report-card p { margin: 5px 0 0; font-size: 1rem; }
  </style>
</head>
<body>
<div class="container py-4">
  <h2 class="mb-4">📊 AI Predictions & Reports</h2>

  <!-- Summary Cards -->
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="report-card bg-primary text-center">
        <h3>KSh <?php echo number_format($totals['revenue'], 2); ?></h3>
        <p>Total Revenue</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="report-card bg-success text-center">
        <h3><?php echo $totals['bookings']; ?></h3>
        <p>Total Bookings</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="report-card bg-info text-center">
        <h3><?php echo $totals['services']; ?></h3>
        <p>Services Offered</p>
      </div>
    </div>

    <?php if ($report_type === 'overview'): ?>
      <div class="col-md-6 mt-3">
        <div class="report-card bg-warning text-center">
          <h3><?php echo $totals['successful_payments']; ?></h3>
          <p>Successful Payments</p>
        </div>
      </div>
      <div class="col-md-6 mt-3">
        <div class="report-card bg-dark text-center">
          <h3><?php echo $totals['total_messages']; ?></h3>
          <p>Customer Messages</p>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Report Type Filter -->
  <form method="get" class="mb-4">
    <div class="row g-2">
      <div class="col-md-6">
        <select name="report_type" class="form-select" onchange="this.form.submit()">
          <option value="overview" <?php if($report_type==='overview') echo 'selected'; ?>>Overview</option>
          <option value="financial" <?php if($report_type==='financial') echo 'selected'; ?>>Financial</option>
          <option value="bookings" <?php if($report_type==='bookings') echo 'selected'; ?>>Bookings</option>
          <option value="services" <?php if($report_type==='services') echo 'selected'; ?>>Services</option>
        </select>
      </div>
    </div>
  </form>

  <!-- Charts -->
  <div class="row">
    <div class="col-md-12">
      <canvas id="reportChart"></canvas>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('reportChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Revenue', 'Bookings', 'Services', 'Payments', 'Messages'],
        datasets: [{
            label: 'Totals',
            data: [
              <?php echo $totals['revenue']; ?>,
              <?php echo $totals['bookings']; ?>,
              <?php echo $totals['services']; ?>,
              <?php echo $totals['successful_payments']; ?>,
              <?php echo $totals['total_messages']; ?>
            ],
            backgroundColor: ['#0d6efd','#198754','#0dcaf0','#ffc107','#212529']
        }]
    }
});
</script>
</body>
</html>
