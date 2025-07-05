<?php
// Comprehensive test to find exactly where the time issue occurs
require_once('Base/Database.php');

echo "=== COMPREHENSIVE TIME DEBUGGING ===\n\n";

try {
    $db = new Database();
    $connection = $db->getConnection();
    
    echo "1. Current database state:\n";
    echo "========================\n";
    
    $result = $connection->query("SELECT id, class_name, schedule_time, created_at FROM classes ORDER BY id DESC LIMIT 3");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "ID: " . $row['id'] . " | Name: " . $row['class_name'] . " | Time: '" . $row['schedule_time'] . "'\n";
        }
    } else {
        echo "No courses found.\n";
    }
    
    echo "\n2. Testing AdminController time formatting:\n";
    echo "==========================================\n";
    
    // Replicate EXACT AdminController logic
    function formatScheduleTime($input) {
        if (!empty($input)) {
            // Accept both H:MM and HH:MM formats
            if (preg_match('/^\d{1,2}:\d{2}$/', $input)) {
                // Format to ensure HH:MM:SS format (pad hour with zero if needed and add seconds)
                $timeComponents = explode(':', $input);
                $hour = str_pad($timeComponents[0], 2, '0', STR_PAD_LEFT);
                $minute = $timeComponents[1];
                return $hour . ':' . $minute . ':00';
            } else {
                // Try to convert from other formats
                $time = date('H:i:s', strtotime($input));
                if ($time === false) {
                    return "INVALID";
                }
                return $time;
            }
        }
        return "EMPTY";
    }
    
    $testInputs = ['7:00', '07:00', '14:30', '9:15'];
    
    foreach ($testInputs as $input) {
        $formatted = formatScheduleTime($input);
        echo "Input: '$input' -> Formatted: '$formatted'\n";
    }
    
    echo "\n3. Testing actual database insertion:\n";
    echo "===================================\n";
    
    // Test with the exact formatted time
    $testTime = formatScheduleTime('7:00'); // Should be '07:00:00'
    echo "Using formatted time: '$testTime'\n";
    
    // Create a test course exactly like AdminModel does
    $sql = "INSERT INTO classes (
        class_name, class_year, class_level, subject, description,
        max_students, sessions_total, price_per_session,
        schedule_time, schedule_duration, schedule_days,
        start_date, end_date, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
    
    $stmt = $connection->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $connection->error);
    }
    
    $testData = [
        'DEBUG_TEST_' . time(),  // class_name
        2025,                    // class_year  
        'Debug',                 // class_level
        'Debug Subject',         // subject
        'Debug test',            // description
        10,                      // max_students
        20,                      // sessions_total
        50000.0,                 // price_per_session
        $testTime,               // schedule_time - THE CRITICAL VALUE
        90,                      // schedule_duration
        'T2,T4',                 // schedule_days
        '2025-07-01',            // start_date
        '2025-12-31'             // end_date
    ];
    
    $stmt->bind_param("sisssididssss", ...$testData);
    
    if ($stmt->execute()) {
        $insertId = $connection->insert_id;
        echo "✓ Test course created with ID: $insertId\n";
        
        // Immediately check what was stored
        $checkStmt = $connection->prepare("SELECT schedule_time FROM classes WHERE id = ?");
        $checkStmt->bind_param("i", $insertId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $stored = $result->fetch_assoc();
        
        echo "✓ What we sent: '$testTime'\n";
        echo "✓ What was stored: '" . $stored['schedule_time'] . "'\n";
        
        if ($stored['schedule_time'] === $testTime) {
            echo "✅ SUCCESS: Time stored correctly!\n";
        } else {
            echo "❌ PROBLEM: Time was changed during storage!\n";
            echo "   Expected: '$testTime'\n";
            echo "   Got: '" . $stored['schedule_time'] . "'\n";
        }
        
        // Test how it displays
        echo "✓ Display test: " . date('H:i', strtotime($stored['schedule_time'])) . "\n";
        
        // Clean up
        $deleteStmt = $connection->prepare("DELETE FROM classes WHERE id = ?");
        $deleteStmt->bind_param("i", $insertId);
        $deleteStmt->execute();
        echo "✓ Test data cleaned up\n";
        
    } else {
        echo "❌ Failed to insert: " . $stmt->error . "\n";
    }
    
    echo "\n4. Testing direct time values:\n";
    echo "=============================\n";
    
    // Test different time formats directly
    $directTests = ['07:00:00', '7:00', '07:00'];
    
    foreach ($directTests as $testVal) {
        echo "\nTesting direct value: '$testVal'\n";
        
        $stmt2 = $connection->prepare("INSERT INTO classes (class_name, class_year, class_level, subject, max_students, sessions_total, price_per_session, schedule_time, schedule_duration, schedule_days, start_date, end_date, status) VALUES (?, 2025, 'Test', 'Test', 10, 20, 50000, ?, 90, 'T2', '2025-07-01', '2025-12-31', 'active')");
        
        $testName = 'DIRECT_TEST_' . time() . '_' . str_replace(':', '', $testVal);
        $stmt2->bind_param("ss", $testName, $testVal);
        
        if ($stmt2->execute()) {
            $id = $connection->insert_id;
            
            $check = $connection->prepare("SELECT schedule_time FROM classes WHERE id = ?");
            $check->bind_param("i", $id);
            $check->execute();
            $result = $check->get_result();
            $row = $result->fetch_assoc();
            
            echo "  Sent: '$testVal' -> Stored: '" . $row['schedule_time'] . "'\n";
            
            // Clean up
            $del = $connection->prepare("DELETE FROM classes WHERE id = ?");
            $del->bind_param("i", $id);
            $del->execute();
        } else {
            echo "  Failed to insert '$testVal': " . $stmt2->error . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== DEBUGGING COMPLETE ===\n";
?>
