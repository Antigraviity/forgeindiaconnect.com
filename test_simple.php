<?php
// Ultra-simple test - no dependencies
header('Content-Type: text/plain');

echo "Test 1: PHP is executing\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Script path: " . __DIR__ . "\n\n";

echo "Test 2: Checking vendor/autoload.php\n";
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    echo "✓ Found: $autoloadPath\n";
} else {
    echo "✗ NOT FOUND: $autoloadPath\n";
    die();
}

echo "\nTest 3: Loading autoload.php\n";
try {
    require $autoloadPath;
    echo "✓ Loaded successfully\n";
} catch (Exception $e) {
    echo "✗ Error loading: " . $e->getMessage() . "\n";
    die();
}

echo "\nTest 4: Checking Razorpay class\n";
if (class_exists('Razorpay\Api\Api')) {
    echo "✓ Razorpay\Api\Api class exists\n";
} else {
    echo "✗ Razorpay\Api\Api class NOT FOUND\n";
    die();
}

echo "\nTest 5: Initializing Razorpay\n";
try {
    $api = new Razorpay\Api\Api("rzp_live_RjEDvWCouoVF7d", "Sbq0maWAxWgEXcbMO8xZwkQU");
    echo "✓ Razorpay API initialized\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    die();
}

echo "\nTest 6: Creating test order\n";
try {
    $order = $api->order->create([
        'receipt' => 'TEST-' . time(),
        'amount' => 150000,
        'currency' => 'INR',
        'payment_capture' => 1
    ]);
    echo "✓ SUCCESS! Order created:\n";
    echo "Order ID: " . $order['id'] . "\n";
    echo "Amount: " . $order['amount'] . "\n";
} catch (Exception $e) {
    echo "✗ Error creating order: " . $e->getMessage() . "\n";
    echo "Full error: " . print_r($e, true) . "\n";
}

echo "\n=== ALL TESTS COMPLETE ===\n";
