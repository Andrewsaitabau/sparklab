<?php
// payment.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

require_login();
if (current_user()['role'] !== 'client') { 
    redirect('admin_dashboard.php'); 
}

$client_id = current_user()['id'];
$token = csrf_token();

// Fetch cart items and calculate total
try {
    // Check if payment_status column exists
    $check_column = $pdo->query("SHOW COLUMNS FROM cart_items LIKE 'payment_status'");
    $column_exists = $check_column->fetch();
    
    if (!$column_exists) {
        $cart_stmt = $pdo->prepare("
            SELECT c.id, s.name, s.price, c.quantity, 
                   (c.quantity * s.price) as subtotal
            FROM cart_items c 
            JOIN services s ON c.service_id = s.id 
            WHERE c.client_id = ?
        ");
    } else {
        $cart_stmt = $pdo->prepare("
            SELECT c.id, s.name, s.price, c.quantity, 
                   (c.quantity * s.price) as subtotal
            FROM cart_items c 
            JOIN services s ON c.service_id = s.id 
            WHERE c.client_id = ? AND (c.payment_status = 'pending' OR c.payment_status IS NULL)
        ");
    }
    
    $cart_stmt->execute([$client_id]);
    $cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Fallback query
    $cart_stmt = $pdo->prepare("
        SELECT c.id, s.name, s.price, c.quantity, 
               (c.quantity * s.price) as subtotal
        FROM cart_items c 
        JOIN services s ON c.service_id = s.id 
        WHERE c.client_id = ?
    ");
    $cart_stmt->execute([$client_id]);
    $cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Calculate total from cart items
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['subtotal'];
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $amount = $total; // Use calculated total from cart
    $phone = $_POST['phone']; // Phone number paying
    
    // Validate CSRF token
    $posted_token = $_POST['csrf'] ?? '';
    if (!hash_equals($token, $posted_token)) {
        $_SESSION['error'] = 'Invalid CSRF token';
        redirect('payment.php');
    }
    
    if (empty($phone) || !preg_match('/^0[0-9]{9}$/', $phone)) {
        $_SESSION['error'] = 'Please enter a valid phone number in format 07XXXXXXXX';
        redirect('payment.php');
    }
    
    if ($total <= 0) {
        $_SESSION['error'] = 'No items found for payment';
        redirect('payment.php');
    }
    
    // Remove leading '0' and add '254' - YOUR WORKING CODE
    $phone = '254' . ltrim($phone, '0');
    
    // YOUR WORKING M-PESA CODE STARTS HERE
    // Requesting Access Token
    $ch = curl_init('https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
    $consumer_key = 'GswwLfq13u0yTxbeWtl8hFl9CPWJ6leSnCazjMQ5CbfroHH8';
    $consumer_secret = 'mxSdtSKGnXC7G31nbfZ8FCuWg58dqOu6of2xWjZcG4HIATifT1sfy2L14UJrGUYG';
    $credentials = base64_encode($consumer_key . ':' . $consumer_secret);
    $headers = array(
        'Authorization: Basic ' . $credentials,
        'Content-Type: application/json'
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Added SSL verify peer false
    $response = curl_exec($ch);
    curl_close($ch);

    // Decoding JSON response to get access token
    $response_data = json_decode($response, true);
    $access_token = $response_data['access_token'];

    // Password Calculation
    $business_short_code = '174379';
    $passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
    $timestamp = date('YmdHis');

    // Concatenate Business Short Code, PassKey, and Timestamp
    $concatenated = $business_short_code . $passkey . $timestamp;

    // Base64 encode the concatenated string
    $password = base64_encode($concatenated);

    // STK Push Request - USING YOUR WORKING PARAMETERS
    $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    $data = array(
        'BusinessShortCode' => $business_short_code,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $amount,
        'PartyA' => $phone,
        'PartyB' => $business_short_code,
        'PhoneNumber' => $phone,
        'CallBackURL' => 'https://yourdomain.com/mpesa_callback.php', // UPDATE THIS
        'AccountReference' => 'SparkLab Services', // Changed to SparkLab
        'TransactionDesc' => 'Payment for ICT services' // Changed description
    );
    $payload = json_encode($data);

    $headers = array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    );

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Added SSL verify peer false
    $resp = curl_exec($curl);
    curl_close($curl);

    $msg_resp = json_decode($resp);

    if(isset($msg_resp->ResponseCode) && $msg_resp->ResponseCode == "0") {
        // Update database only if payment was successful
        try {
            $check_column = $pdo->query("SHOW COLUMNS FROM cart_items LIKE 'payment_status'");
            $column_exists = $check_column->fetch();
            
            if ($column_exists) {
                foreach ($cart_items as $item) {
                    $stmt = $pdo->prepare("
                        UPDATE cart_items 
                        SET payment_status = 'pending', 
                            phone_number = ?,
                            amount_paid = ?,
                            updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$phone, $amount, $item['id']]);
                    
                    // Check if payments table exists
                    $check_payments_table = $pdo->query("SHOW TABLES LIKE 'payments'");
                    $payments_table_exists = $check_payments_table->fetch();
                    
                    if ($payments_table_exists) {
                        $payment_stmt = $pdo->prepare("
                            INSERT INTO payments (cart_id, client_id, amount, phone_number, status, created_at)
                            VALUES (?, ?, ?, ?, 'pending', NOW())
                        ");
                        $payment_stmt->execute([$item['id'], $client_id, $item['subtotal'], $phone]);
                    }
                }
            }
            
            $message =  "<i class='fas fa-check-circle'></i> Payment request sent! Please check your phone to complete the M-Pesa transaction.";
            $_SESSION['payment_initiated'] = true;
            
        } catch (Exception $e) {
            // Even if database update fails, payment was initiated
            $message =  "<i class='fas fa-check-circle'></i> Payment request sent! Please check your phone. (Database update failed)";
            $_SESSION['payment_initiated'] = true;
        }
    } else {
        if(isset($msg_resp->errorMessage)) {
            $message  = "<i class='fas fa-times-circle'></i> Transaction Failed: " . $msg_resp->errorMessage;
        } else {
           $message =  "<i class='fas fa-times-circle'></i> Transaction Failed. Please try again.";
        }
        $_SESSION['error'] = $message;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lipa na M-Pesa | SPARKLAB</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), #0a58ca);
            padding: 1rem 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .payment-container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 2rem;
        }
        
        .payment-card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: none;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .payment-header {
            background: linear-gradient(135deg, var(--primary-color), #0a58ca);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        
        .payment-body {
            padding: 2rem;
        }
        
        .btn-pay {
            background: linear-gradient(135deg, var(--primary-color), #0a58ca);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            width: 100%;
            margin-top: 1rem;
        }
        
        .order-summary {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .message {
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: 500;
        }
        
        .success-message {
            background-color: rgba(25, 135, 84, 0.15);
            border-left: 5px solid var(--success-color);
            color: var(--success-color);
        }
        
        .error-message {
            background-color: rgba(220, 53, 69, 0.15);
            border-left: 5px solid var(--danger-color);
            color: var(--danger-color);
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), #0a58ca);
            color: white;
            padding: 3rem 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="client_dashboard.php">
                <i class="fas fa-bolt me-2"></i>SPARKLAB
            </a>
        </div>
    </nav>

    <div class="container payment-container">
        <!-- Back Button -->
        <a href="client_dashboard.php" class="btn btn-outline-secondary mb-4">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <!-- Message Display -->
        <?php if (!empty($message)): ?>
            <div class="message <?php echo (strpos($message, 'Failed') === false) ? 'success-message' : 'error-message'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <!-- Hero Section -->
                <div class="hero-section">
                    <h1 class="hero-title">Lipa na M-Pesa</h1>
                    <p class="hero-subtitle">Fast, secure and convenient mobile payments</p>
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/15/M-PESA_LOGO-01.svg/1200px-M-PESA_LOGO-01.svg.png" alt="M-Pesa Logo" style="max-width: 120px; margin-bottom: 1rem;">
                </div>

                <!-- Order Summary -->
                <div class="card payment-card">
                    <div class="payment-header">
                        <h3><i class="fas fa-receipt me-2"></i> Order Summary</h3>
                    </div>
                    <div class="payment-body">
                        <?php if (count($cart_items) > 0): ?>
                            <?php foreach ($cart_items as $item): ?>
                                <div class="d-flex justify-content-between py-2 border-bottom">
                                    <div>
                                        <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                        <br><small class="text-muted">Quantity: <?php echo $item['quantity']; ?></small>
                                    </div>
                                    <div>KSh <?php echo number_format($item['subtotal'], 2); ?></div>
                                </div>
                            <?php endforeach; ?>
                            <div class="d-flex justify-content-between py-2 mt-3 fs-5">
                                <strong>Total Amount:</strong>
                                <strong class="text-primary">KSh <?php echo number_format($total, 2); ?></strong>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No items found for payment.</p>
                            <a href="client_dashboard.php" class="btn btn-primary">Browse Services</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Payment Steps -->
                <div class="card payment-card">
                    <div class="payment-header">
                        <h3><i class="fas fa-info-circle me-2"></i> How to Pay</h3>
                    </div>
                    <div class="payment-body">
                        <div class="step mb-3">
                            <h5>1. Enter Your Phone Number</h5>
                            <p class="text-muted">Your M-Pesa registered phone number</p>
                        </div>
                        <div class="step mb-3">
                            <h5>2. Click PAY NOW Button</h5>
                            <p class="text-muted">Submit your payment request</p>
                        </div>
                        <div class="step mb-3">
                            <h5>3. Check Your Phone</h5>
                            <p class="text-muted">Enter your M-Pesa PIN when prompted</p>
                        </div>
                        <div class="step">
                            <h5>4. Receive Confirmation</h5>
                            <p class="text-muted">You'll receive an SMS confirmation</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <!-- Payment Form -->
                <div class="card payment-card">
                    <div class="payment-header">
                        <h3><i class="fas fa-mobile-alt me-2"></i> M-Pesa Payment</h3>
                    </div>
                    <div class="payment-body">
                        <?php if (count($cart_items) > 0): ?>
                            <form action="" method="POST">
                                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($token); ?>">
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="e.g. 0712345678" required>
                                    <small class="text-muted">Enter your M-Pesa registered phone number</small>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    You will receive a prompt on your phone to complete payment of 
                                    <strong>KSh <?php echo number_format($total, 2); ?></strong>
                                </div>
                                
                                <button type="submit" class="btn btn-pay" name="submit" value="submit">
                                    <i class="fas fa-paper-plane me-2"></i> PAY KSh <?php echo number_format($total, 2); ?>
                                </button>
                            </form>
                            
                            <div class="mt-4 pt-3 border-top text-center">
                                <p class="text-muted">
                                    <i class="fas fa-lock"></i> Your payment is secure and encrypted
                                </p>
                                <div class="d-flex justify-content-center mt-2">
                                    <img src="https://www.safaricom.co.ke/images/M-PESA-API-Logo.png" alt="M-Pesa Secure" height="40" class="me-3">
                                    <img src="https://www.safaricom.co.ke/images/logo.png" alt="Safaricom" height="40">
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Your cart is empty</p>
                                <a href="client_dashboard.php" class="btn btn-primary">Add Services</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> SparkLab. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script>
        // Format phone number input
        document.getElementById('phone').addEventListener('input', function (e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
        
        <?php if (isset($_SESSION['payment_initiated'])): ?>
            // Refresh page after 15 seconds if payment was initiated
            setTimeout(() => {
                window.location.reload();
            }, 15000);
            <?php unset($_SESSION['payment_initiated']); ?>
        <?php endif; ?>
    </script>
</body>
</html>