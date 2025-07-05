<?php
// Create sample payment data for testing
require_once('Base/Database.php');

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get some existing students and classes
    $sql = "SELECT u.id as student_id, c.id as class_id, c.price_per_session 
            FROM users u 
            INNER JOIN enrollments e ON u.id = e.student_id 
            INNER JOIN classes c ON e.class_id = c.id 
            WHERE u.role = 'student' AND e.status = 'active' AND c.status = 'active'
            LIMIT 3";
    
    $result = $conn->query($sql);
    $enrollments = $result->fetch_all(MYSQLI_ASSOC);
    
    echo "Found " . count($enrollments) . " enrollments\n";
    
    // Create some pending payments
    foreach ($enrollments as $enrollment) {
        // Check if payment already exists
        $check_sql = "SELECT id FROM payments WHERE student_id = ? AND class_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $enrollment['student_id'], $enrollment['class_id']);
        $check_stmt->execute();
        $existing = $check_stmt->get_result();
        
        if ($existing->num_rows === 0) {
            // Create a pending payment
            $amount = $enrollment['price_per_session'] * 4; // 4 sessions worth
            $final_amount = $amount;
            
            $insert_sql = "INSERT INTO payments (student_id, payer_id, class_id, amount, final_amount, payment_date, payment_method, status, created_at) 
                          VALUES (?, ?, ?, ?, ?, CURDATE(), 'bank_transfer', 'pending', NOW())";
            
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iiidd", 
                $enrollment['student_id'], 
                $enrollment['student_id'], 
                $enrollment['class_id'], 
                $amount, 
                $final_amount
            );
            
            if ($insert_stmt->execute()) {
                echo "Created pending payment for student {$enrollment['student_id']}, class {$enrollment['class_id']}, amount: {$amount}\n";
            } else {
                echo "Failed to create payment: " . $insert_stmt->error . "\n";
            }
            $insert_stmt->close();
        } else {
            echo "Payment already exists for student {$enrollment['student_id']}, class {$enrollment['class_id']}\n";
        }
        $check_stmt->close();
    }
    
    echo "Sample payment data created successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
