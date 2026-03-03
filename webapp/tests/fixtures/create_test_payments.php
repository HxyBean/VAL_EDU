<?php
// Script to create test payment data for demonstration
require_once('Base/Database.php');

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "Creating test payment data...\n";
    
    // First, let's see what parent-student relationships exist
    $sql = "SELECT ps.parent_id, ps.student_id, u.full_name as student_name, p.full_name as parent_name
            FROM parent_student ps
            INNER JOIN users u ON ps.student_id = u.id
            INNER JOIN users p ON ps.parent_id = p.id
            WHERE u.role = 'student' AND p.role = 'parent'";
    
    $result = $conn->query($sql);
    $relationships = $result->fetch_all(MYSQLI_ASSOC);
    
    echo "Found " . count($relationships) . " parent-student relationships:\n";
    foreach ($relationships as $rel) {
        echo "- Parent: {$rel['parent_name']} (ID: {$rel['parent_id']}) -> Student: {$rel['student_name']} (ID: {$rel['student_id']})\n";
    }
    
    if (count($relationships) === 0) {
        echo "No parent-student relationships found. Please create some first.\n";
        exit;
    }
    
    // Get some classes that students are enrolled in
    $sql = "SELECT e.student_id, e.class_id, c.class_name, c.price_per_session, u.full_name as student_name
            FROM enrollments e
            INNER JOIN classes c ON e.class_id = c.id
            INNER JOIN users u ON e.student_id = u.id
            WHERE e.status = 'active' AND c.status = 'active'
            LIMIT 5";
    
    $result = $conn->query($sql);
    $enrollments = $result->fetch_all(MYSQLI_ASSOC);
    
    echo "\nFound " . count($enrollments) . " active enrollments:\n";
    foreach ($enrollments as $enr) {
        echo "- Student: {$enr['student_name']} (ID: {$enr['student_id']}) -> Class: {$enr['class_name']} (ID: {$enr['class_id']}) - Price: {$enr['price_per_session']}\n";
    }
    
    if (count($enrollments) === 0) {
        echo "No active enrollments found. Please create some first.\n";
        exit;
    }
    
    // Create some pending payments
    $paymentsCreated = 0;
    foreach ($enrollments as $enrollment) {
        // Check if payment already exists
        $check_sql = "SELECT id FROM payments WHERE student_id = ? AND class_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $enrollment['student_id'], $enrollment['class_id']);
        $check_stmt->execute();
        $existing = $check_stmt->get_result();
        
        if ($existing->num_rows === 0) {
            // Find parent for this student
            $parent_sql = "SELECT parent_id FROM parent_student WHERE student_id = ? LIMIT 1";
            $parent_stmt = $conn->prepare($parent_sql);
            $parent_stmt->bind_param("i", $enrollment['student_id']);
            $parent_stmt->execute();
            $parent_result = $parent_stmt->get_result();
            $parent_data = $parent_result->fetch_assoc();
            
            if ($parent_data) {
                // Create a pending payment
                $amount = $enrollment['price_per_session'] * 4; // 4 sessions worth
                $final_amount = $amount;
                
                $insert_sql = "INSERT INTO payments (student_id, payer_id, class_id, amount, final_amount, payment_date, payment_method, status, created_at) 
                              VALUES (?, ?, ?, ?, ?, CURDATE(), 'bank_transfer', 'pending', NOW())";
                
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("iiidd", 
                    $enrollment['student_id'], 
                    $parent_data['parent_id'], 
                    $enrollment['class_id'], 
                    $amount, 
                    $final_amount
                );
                
                if ($insert_stmt->execute()) {
                    echo "Created pending payment: Student {$enrollment['student_name']} -> Class {$enrollment['class_name']} -> Amount: " . number_format($amount) . " VND\n";
                    $paymentsCreated++;
                } else {
                    echo "Failed to create payment: " . $insert_stmt->error . "\n";
                }
                $insert_stmt->close();
            }
            $parent_stmt->close();
        } else {
            echo "Payment already exists for Student {$enrollment['student_name']} -> Class {$enrollment['class_name']}\n";
        }
        $check_stmt->close();
    }
    
    echo "\n=== SUMMARY ===\n";
    echo "Created $paymentsCreated new pending payments.\n";
    echo "You can now test the payment system in the parent dashboard!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
