<?php
echo "=== TIME DEBUGGING TEST ===\n";

// Test database connection using the proper method
try {
    require_once('Base/Database.php');
    echo "✓ Database class loaded\n";
    
    $db = Database::getInstance();
    echo "✓ Database instance obtained\n";
    
    $connection = $db->getConnection();
    echo "✓ Database connection established\n";
    
    // Check current time values in database
    echo "\n1. Current schedule_time values:\n";
    $result = $connection->query("SELECT id, class_name, schedule_time FROM classes ORDER BY id DESC LIMIT 3");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "   ID: " . $row['id'] . " | Name: " . $row['class_name'] . " | Time: '" . $row['schedule_time'] . "'\n";
        }
    } else {
        echo "   No courses found\n";
    }
    
    // Test the time formatting
    echo "\n2. Testing time formatting:\n";
    
    function formatTimeCorrectly($input) {
        if (!empty($input)) {
            if (preg_match('/^\d{1,2}:\d{2}$/', $input)) {
                $timeComponents = explode(':', $input);
                $hour = str_pad($timeComponents[0], 2, '0', STR_PAD_LEFT);
                $minute = $timeComponents[1];
                return $hour . ':' . $minute . ':00';
            } else {
                return date('H:i:s', strtotime($input));
            }
        }
        return null;
    }
    
    $testInputs = ['7:00', '07:00', '14:30'];
    foreach ($testInputs as $input) {
        $formatted = formatTimeCorrectly($input);
        echo "   '$input' -> '$formatted'\n";
    }
    
    // Test actual database insertion
    echo "\n3. Testing database insertion:\n";
    
    $testTime = formatTimeCorrectly('7:00'); // Should be '07:00:00'
    echo "   Using time: '$testTime'\n";
    
    $stmt = $connection->prepare("INSERT INTO classes (class_name, class_year, class_level, subject, max_students, sessions_total, price_per_session, schedule_time, schedule_duration, schedule_days, start_date, end_date, status) VALUES (?, 2025, 'Test', 'Debug Test', 10, 20, 50000, ?, 90, 'T2', '2025-07-01', '2025-12-31', 'active')");
    
    if ($stmt) {
        $testName = 'DEBUG_' . time();
        $stmt->bind_param("ss", $testName, $testTime);
        
        if ($stmt->execute()) {
            $insertId = $connection->insert_id;
            echo "   ✓ Test course created with ID: $insertId\n";
            
            // Check what was actually stored
            $checkStmt = $connection->prepare("SELECT schedule_time FROM classes WHERE id = ?");
            $checkStmt->bind_param("i", $insertId);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            $stored = $result->fetch_assoc();
            
            echo "   ✓ Sent to DB: '$testTime'\n";
            echo "   ✓ Stored in DB: '" . $stored['schedule_time'] . "'\n";
            
            if ($stored['schedule_time'] === $testTime) {
                echo "   ✅ SUCCESS: Time stored correctly!\n";
            } else {
                echo "   ❌ PROBLEM: Time was changed!\n";
                echo "      Expected: '$testTime'\n";
                echo "      Got: '" . $stored['schedule_time'] . "'\n";
            }
            
            // Clean up
            $deleteStmt = $connection->prepare("DELETE FROM classes WHERE id = ?");
            $deleteStmt->bind_param("i", $insertId);
            $deleteStmt->execute();
            echo "   ✓ Test data cleaned up\n";
            
        } else {
            echo "   ❌ Failed to insert: " . $stmt->error . "\n";
        }
    } else {
        echo "   ❌ Failed to prepare statement: " . $connection->error . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
