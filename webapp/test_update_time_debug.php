<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/Model/AdminModel.php');

echo "=== Testing Update Course Time Issue ===\n";

try {
    $adminModel = new AdminModel();
    
    // First create a test course to update
    $testData = [
        'class_name' => 'Update Test Course',
        'class_year' => 2025,
        'class_level' => 'Sơ cấp',
        'subject' => 'Update Test Subject',
        'description' => 'Update test description',
        'max_students' => 15,
        'sessions_total' => 30,
        'price_per_session' => 300000,
        'schedule_time' => '10:00:00',
        'schedule_duration' => 120,
        'schedule_days' => 'T2,T4,T6',
        'start_date' => '2025-01-15',
        'end_date' => '2025-06-15',
        'tutor_id' => null
    ];
    
    $courseId = $adminModel->createCourse($testData);
    echo "Created test course with ID: $courseId\n";
    
    // Test different time formats for update
    $updateCases = [
        ['time' => '7:00', 'description' => 'Update to 7:00 (single digit hour)'],
        ['time' => '09:30', 'description' => 'Update to 09:30 (padded hour)'],
        ['time' => '16:45', 'description' => 'Update to 16:45 (afternoon)'],
    ];
    
    foreach ($updateCases as $index => $testCase) {
        echo "\n--- Update Test " . ($index + 1) . ": " . $testCase['description'] . " ---\n";
        
        $updateData = [
            'class_name' => 'Update Test Course - Modified',
            'class_year' => 2025,
            'class_level' => 'Sơ cấp',
            'subject' => 'Update Test Subject',
            'description' => 'Update test description',
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
        
        // Update course
        try {
            $result = $adminModel->updateCourse($courseId, $updateData);
            echo "Course updated successfully: " . ($result ? 'true' : 'false') . "\n";
            
            // Query the database directly to see what was stored
            $db = $adminModel->getConnection();
            $queryResult = $db->query("SELECT schedule_time FROM classes WHERE id = $courseId");
            
            if ($queryResult && $row = $queryResult->fetch_assoc()) {
                echo "Stored time in database: " . $row['schedule_time'] . "\n";
                
                $expectedTime = str_pad(explode(':', $testCase['time'])[0], 2, '0', STR_PAD_LEFT) . ':' . explode(':', $testCase['time'])[1] . ':00';
                
                if ($row['schedule_time'] !== $expectedTime) {
                    echo "❌ ERROR: Expected $expectedTime but got " . $row['schedule_time'] . "\n";
                } else {
                    echo "✅ SUCCESS: Time stored correctly\n";
                }
            }
            
        } catch (Exception $e) {
            echo "❌ ERROR updating course: " . $e->getMessage() . "\n";
        }
    }
    
    // Clean up test data
    echo "\n--- Cleaning up test data ---\n";
    $db = $adminModel->getConnection();
    $result = $db->query("DELETE FROM classes WHERE class_name LIKE 'Update Test Course%'");
    echo "Cleanup completed\n";
    
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
