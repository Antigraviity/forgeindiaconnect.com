<?php
// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

require 'vendor/autoload.php';
use Razorpay\Api\Api;

header('Content-Type: application/json');

try {
    // Log the start of order creation
    error_log("[create_order.php] Starting order creation at " . date('Y-m-d H:i:s'));

    // IMPORTANT: Replace with your actual Razorpay API credentials
    $keyId = "rzp_live_RjEDvWCouoVF7d";
    $keySecret = "Sbq0maWAxWgEXcbMO8xZwkQU";
    
    // Verify credentials are set
    if (empty($keyId) || empty($keySecret)) {
        throw new Exception("Razorpay API credentials are not configured");
    }
    
    // Initialize Razorpay API
    $api = new Api($keyId, $keySecret);
    
    error_log("[create_order.php] Razorpay API initialized successfully");

    // Create Razorpay Order
    $orderData = [
        'receipt' => 'ENROLL-' . time(),
        'amount' => 150000,          // 1500 INR → in paise
        'currency' => 'INR',
        'payment_capture' => 1       // Auto-capture
    ];
    
    error_log("[create_order.php] Creating order with data: " . json_encode($orderData));
    
    $order = $api->order->create($orderData);
    
    error_log("[create_order.php] Order created successfully: " . json_encode($order));

    // Return success response
    echo json_encode([
        "order_id" => $order['id'],
        "amount"   => $order['amount'],
        "currency" => $order['currency']
    ]);

} catch (Exception $e) {
    // Log the error
    error_log("[create_order.php] ERROR: " . $e->getMessage());
    error_log("[create_order.php] Stack trace: " . $e->getTraceAsString());
    
    // Return error response with proper HTTP code
    http_response_code(500);
    echo json_encode([
        "error"   => true,
        "message" => $e->getMessage()
    ]);
}
