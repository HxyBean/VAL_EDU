<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/Model/AdminModel.php');

try {
    $adminModel = new AdminModel();
    
    // Test data
    $testData = [
        'class_name' => 'Test Course',
        'class_year' => 2025,
        'class_level' => 'Sơ cấp',
        'subject' => 'Test Subject',
        'description' => 'Test description',
        'max_students' => 15,
        'sessions_total' => 30,
        'price_per_session' => 300000,
        'schedule_time' => '09:00',
        'schedule_duration' => 120,
        'schedule_days' => 'T2,T4,T6',
        'start_date' => '2025-01-15',
        'end_date' => '2025-06-15',
        'tutor_id' => null
    ];
    
    echo "Testing updateCourse with course ID 2...\n";
    $result = $adminModel->updateCourse(2, $testData);
    
    if ($result === true) {
        echo "SUCCESS: updateCourse completed successfully\n";
    } else {
        echo "FAILURE: updateCourse returned false\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
