<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/Model/AdminModel.php');

echo "=== Testing Time Issue Debug ===\n";

try {
    $adminModel = new AdminModel();
    
    // Test course creation with different time formats
    $testCases = [
        ['time' => '7:00', 'description' => 'Time format 7:00 (single digit hour)'],
        ['time' => '08:00', 'description' => 'Time format 08:00 (padded hour)'],
        ['time' => '15:30', 'description' => 'Time format 15:30 (afternoon)'],
    ];
    
    foreach ($testCases as $index => $testCase) {
        echo "\n--- Test Case " . ($index + 1) . ": " . $testCase['description'] . " ---\n";
        
        $testData = [
            'class_name' => 'Debug Test Course ' . ($index + 1),
            'class_year' => 2025,
            'class_level' => 'Sơ cấp',
            'subject' => 'Debug Subject',
            'description' => 'Debug description',
            'max_students' => 15,
            'sessions_total' => 30,
            'price_per_session' => 300000,
            'schedule_time' => $testCase['time'],
            'schedule_duration' => 120,
            'schedule_days' => 'T2,T4,T6',
            'start_date' => '2025-01-15',
            'end_date' => '2025-06-15',
            'tutor_id' => null
        ];
        
        echo "Input time: " . $testCase['time'] . "\n";
        
        // Create course
        try {
            $courseId = $adminModel->createCourse($testData);
            echo "Course created with ID: $courseId\n";
            
            // Query the database directly to see what was stored
            $db = $adminModel->getConnection();
            $result = $db->query("SELECT schedule_time FROM classes WHERE id = $courseId");
            
            if ($result && $row = $result->fetch_assoc()) {
                echo "Stored time in database: " . $row['schedule_time'] . "\n";
                
                if ($row['schedule_time'] !== $testCase['time'] . ':00' && 
                    $row['schedule_time'] !== str_pad(explode(':', $testCase['time'])[0], 2, '0', STR_PAD_LEFT) . ':' . explode(':', $testCase['time'])[1] . ':00') {
                    echo "❌ ERROR: Expected " . str_pad(explode(':', $testCase['time'])[0], 2, '0', STR_PAD_LEFT) . ":" . explode(':', $testCase['time'])[1] . ":00 but got " . $row['schedule_time'] . "\n";
                } else {
                    echo "✅ SUCCESS: Time stored correctly\n";
                }
            }
            
        } catch (Exception $e) {
            echo "❌ ERROR creating course: " . $e->getMessage() . "\n";
        }
    }
    
    // Clean up test data
    echo "\n--- Cleaning up test data ---\n";
    $db = $adminModel->getConnection();
    $result = $db->query("DELETE FROM classes WHERE class_name LIKE 'Debug Test Course%'");
    echo "Cleanup completed\n";
    
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
