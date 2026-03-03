<?php
require_once 'Base/Database.php';
require_once 'Model/ParentModel.php';

echo "<h1>Payment Recording Test</h1>";

// Initialize database and parent model
$database = Database::getInstance();
$db = $database->getConnection();
$parentModel = new ParentModel($db);

// Test with parent ID 1 (assuming this exists)
$parent_id = 1;

echo "<h2>1. Getting Children Bills</h2>";
$bills = $parentModel->getChildrenBills($parent_id);
echo "Found " . count($bills) . " bills<br>";
foreach ($bills as $bill) {
    echo "- " . $bill['student_name'] . " - " . $bill['class_name'] . " - $" . $bill['amount'] . " (" . $bill['status'] . ")<br>";
}

echo "<h2>2. Testing Payment Processing</h2>";
if (!empty($bills)) {
    $test_bill = $bills[0];
    $payment_id = $test_bill['id'];
    
    echo "Testing payment for: " . $test_bill['student_name'] . " - " . $test_bill['class_name'] . "<br>";
    echo "Payment ID: " . $payment_id . "<br>";
    
    // Check if payment exists in database before processing
    $sql = "SELECT * FROM payments WHERE id = ? OR (student_id = ? AND class_id = ?)";
    $stmt = $db->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sii", $payment_id, $test_bill['student_id'], $test_bill['class_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing_payment = $result->fetch_assoc();
        $stmt->close();
        
        echo "Existing payment record: " . ($existing_payment ? "Yes (ID: " . $existing_payment['id'] . ")" : "No") . "<br>";
    }
    
    // Process the payment
    $result = $parentModel->processPayment($payment_id, $parent_id);
    echo "Payment processing result: " . ($result ? "SUCCESS" : "FAILED") . "<br>";
    
    // Check if payment was recorded
    if (strpos($payment_id, 'new_') === 0) {
        // For virtual payments, check if a new payment record was created
        $sql = "SELECT * FROM payments WHERE student_id = ? AND class_id = ? AND status = 'completed' ORDER BY created_at DESC LIMIT 1";
        $stmt = $db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ii", $test_bill['student_id'], $test_bill['class_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $new_payment = $result->fetch_assoc();
            $stmt->close();
            
            if ($new_payment) {
                echo "New payment record created: ID " . $new_payment['id'] . ", Amount: $" . $new_payment['amount'] . "<br>";
            } else {
                echo "ERROR: No new payment record found!<br>";
            }
        }
    } else {
        // For existing payments, check if status was updated
        $sql = "SELECT * FROM payments WHERE id = ?";
        $stmt = $db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $payment_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $updated_payment = $result->fetch_assoc();
            $stmt->close();
            
            if ($updated_payment) {
                echo "Payment status updated to: " . $updated_payment['status'] . "<br>";
            } else {
                echo "ERROR: Payment record not found!<br>";
            }
        }
    }
}

echo "<h2>3. Recent Payments</h2>";
$recent_payments = $parentModel->getRecentPaymentsByParent($parent_id, 5);
echo "Found " . count($recent_payments) . " recent payments<br>";
foreach ($recent_payments as $payment) {
    echo "- " . $payment['student_name'] . " - " . $payment['class_name'] . " - $" . $payment['amount'] . " (" . $payment['status'] . ") - " . $payment['payment_date'] . "<br>";
}

echo "<h2>4. Updated Parent Stats</h2>";
$stats = $parentModel->getParentStats($parent_id);
echo "Total paid: $" . $stats['total_paid'] . "<br>";
echo "Pending payments: $" . $stats['pending_payments'] . "<br>";
echo "Total payments: " . $stats['total_payments'] . "<br>";
?>
