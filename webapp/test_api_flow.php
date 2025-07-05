<?php
// Test the full API flow including AdminController
session_start();

// Set up session as admin
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

// Test different time formats through the API
$testCases = [
    ['time' => '7:00', 'description' => 'API Test: 7:00 (single digit hour)'],
    ['time' => '08:30', 'description' => 'API Test: 08:30 (padded hour)'],
    ['time' => '15:45', 'description' => 'API Test: 15:45 (afternoon)'],
];

require_once(__DIR__ . '/Model/AdminModel.php');

echo "=== Testing Full API Flow ===\n";

try {
    $adminModel = new AdminModel();
    
    foreach ($testCases as $index => $testCase) {
        echo "\n--- " . $testCase['description'] . " ---\n";
        
        // Create a test course first
        $createData = [
            'class_name' => 'API Test Course ' . ($index + 1),
            'class_year' => 2025,
            'class_level' => 'Sơ cấp',
            'subject' => 'API Test Subject',
            'description' => 'API test description',
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
        
        $courseId = $adminModel->createCourse($createData);
        echo "Created course ID: $courseId\n";
        
        // Simulate POST data
        $_POST = [
            'course_id' => (string)$courseId,
            'class_name' => 'API Test Course Updated',
            'class_year' => '2025',
            'class_level' => 'Sơ cấp',
            'subject' => 'API Test Subject Updated',
            'description' => 'API test description updated',
            'max_students' => '20',
            'sessions_total' => '25',
            'price_per_session' => '350000',
            'schedule_time' => $testCase['time'],
            'schedule_duration' => '90',
            'schedule_days' => 'T3,T5,T7',
            'start_date' => '2025-02-01',
            'end_date' => '2025-07-01',
            'tutor_id' => ''
        ];
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        echo "Input time: " . $testCase['time'] . "\n";
        
        // Capture output from AdminController
        ob_start();
        
        require_once(__DIR__ . '/Controller/AdminController.php');
        $adminController = new AdminController();
        $adminController->updateCourse();
        
        $output = ob_get_clean();
        
        echo "API Response: " . trim($output) . "\n";
        
        // Check what was actually stored in the database
        $db = $adminModel->getConnection();
        $result = $db->query("SELECT schedule_time FROM classes WHERE id = $courseId");
        
        if ($result && $row = $result->fetch_assoc()) {
            echo "Stored time in database: " . $row['schedule_time'] . "\n";
            
            $expectedTime = str_pad(explode(':', $testCase['time'])[0], 2, '0', STR_PAD_LEFT) . ':' . explode(':', $testCase['time'])[1] . ':00';
            
            if ($row['schedule_time'] !== $expectedTime) {
                echo "❌ ERROR: Expected $expectedTime but got " . $row['schedule_time'] . "\n";
            } else {
                echo "✅ SUCCESS: Time stored correctly via API\n";
            }
        }
        
        // Parse API response
        $response = json_decode($output, true);
        if ($response && isset($response['success'])) {
            if ($response['success']) {
                echo "✅ API Success: " . $response['message'] . "\n";
            } else {
                echo "❌ API Error: " . $response['message'] . "\n";
            }
        } else {
            echo "⚠️  API Response format issue\n";
        }
    }
    
    // Clean up test data
    echo "\n--- Cleaning up test data ---\n";
    $db = $adminModel->getConnection();
    $result = $db->query("DELETE FROM classes WHERE class_name LIKE 'API Test Course%'");
    echo "Cleanup completed\n";
    
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
