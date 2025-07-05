<?php
// Simple test to verify the time formatting
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

require_once(__DIR__ . '/Model/AdminModel.php');

echo "=== Simple Time Test ===\n";

$adminModel = new AdminModel();

// Create course with 8:30 time
$testData = [
    'class_name' => 'Time Test',
    'class_year' => 2025,
    'class_level' => 'Sơ cấp',
    'subject' => 'IELTS Speaking',
    'description' => 'Test',
    'max_students' => 15,
    'sessions_total' => 30,
    'price_per_session' => 300000,
    'schedule_time' => '08:30:00',
    'schedule_duration' => 120,
    'schedule_days' => 'T2,T4,T6',
    'start_date' => '2025-01-15',
    'end_date' => '2025-06-15',
    'tutor_id' => null
];

$courseId = $adminModel->createCourse($testData);
echo "Course created: $courseId\n";

// Check initial time
$db = $adminModel->getConnection();
$result = $db->query("SELECT schedule_time FROM classes WHERE id = $courseId");
$row = $result->fetch_assoc();
echo "Initial time: " . $row['schedule_time'] . "\n";

// Test updating with '7:00' format
$updateData = [
    'class_name' => 'Time Test Updated',
    'class_year' => 2025,
    'class_level' => 'Sơ cấp',
    'subject' => 'IELTS Speaking',
    'description' => 'Test updated',
    'max_students' => 15,
    'sessions_total' => 30,
    'price_per_session' => 300000,
    'schedule_time' => '7:00', // This should become 07:00:00
    'schedule_duration' => 120,
    'schedule_days' => 'T2,T4,T6',
    'start_date' => '2025-01-15',
    'end_date' => '2025-06-15',
    'tutor_id' => null
];

$result = $adminModel->updateCourse($courseId, $updateData);
echo "Update result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";

// Check final time
$result = $db->query("SELECT schedule_time FROM classes WHERE id = $courseId");
$row = $result->fetch_assoc();
echo "Final time: " . $row['schedule_time'] . "\n";

if ($row['schedule_time'] === '07:00:00') {
    echo "✅ SUCCESS: Time correctly formatted\n";
} else {
    echo "❌ ERROR: Time not formatted correctly\n";
}

// Cleanup
$db->query("DELETE FROM classes WHERE class_name LIKE 'Time Test%'");
echo "Cleanup done\n";
