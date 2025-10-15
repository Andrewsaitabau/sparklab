<?php
// mpesa_callback.php
require_once __DIR__ . '/config.php';

// Log the raw callback for debugging
file_put_contents('mpesa_callback.log', date('Y-m-d H:i:s') . " - Callback: " . file_get_contents('php://input') . "\n", FILE_APPEND);

$callback_data = file_get_contents('php://input');
$data = json_decode($callback_data, true);

if (isset($data['Body']['stkCallback'])) {
    $callback     = $data['Body']['stkCallback'];
    $result_code  = $callback['ResultCode'];
    $checkout_id  = $callback['CheckoutRequestID'] ?? null; // we use this to track payment

    if ($result_code == 0) {
        // Successful payment
        $metadata = $callback['CallbackMetadata']['Item'] ?? [];
        $payment_data = [];

        foreach ($metadata as $item) {
            $payment_data[$item['Name']] = $item['Value'] ?? '';
        }

        $amount        = $payment_data['Amount'] ?? 0;
        $mpesa_receipt = $payment_data['MpesaReceiptNumber'] ?? '';
        $phone         = $payment_data['PhoneNumber'] ?? '';

        try {
            // Update the payments table
            $stmt = $pdo->prepare("
                UPDATE payments
                SET status = 'success',
                    amount = ?,
                    mpesa_receipt_number = ?,
                    phone_number = ?,
                    transaction_date = NOW()
                WHERE checkout_request_id = ?
            ");
            $stmt->execute([$amount, $mpesa_receipt, $phone, $checkout_id]);

            file_put_contents('mpesa_success.log', date('Y-m-d H:i:s') . " - Payment success: $mpesa_receipt | $amount | $phone\n", FILE_APPEND);

        } catch (Exception $e) {
            file_put_contents('mpesa_error.log', date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    } else {
        // Failed transaction
        try {
            $stmt = $pdo->prepare("
                UPDATE payments
                SET status = 'failed'
                WHERE checkout_request_id = ?
            ");
            $stmt->execute([$checkout_id]);

            file_put_contents('mpesa_failed.log', date('Y-m-d H:i:s') . " - Payment failed for CheckoutID: $checkout_id\n", FILE_APPEND);

        } catch (Exception $e) {
            file_put_contents('mpesa_error.log', date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }
}

// Always return success to M-Pesa
header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Success']);
