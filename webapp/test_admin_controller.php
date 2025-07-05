<?php
// Test the AdminController updateCourse API endpoint

session_start();

// Set up session as admin
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

// Simulate POST data
$_POST = [
    'course_id' => '2',
    'class_name' => 'Updated Test Course',
    'class_year' => '2025',
    'class_level' => 'Sơ cấp',
    'subject' => 'Updated Subject',
    'description' => 'Updated description',
    'max_students' => '20',
    'sessions_total' => '25',
    'price_per_session' => '350000',
    'schedule_time' => '10:00',
    'schedule_duration' => '90',
    'schedule_days' => 'T3,T5,T7',
    'start_date' => '2025-02-01',
    'end_date' => '2025-07-01',
    'tutor_id' => ''
];

$_SERVER['REQUEST_METHOD'] = 'POST';

echo "Testing AdminController updateCourse API...\n";

require_once(__DIR__ . '/Controller/AdminController.php');

try {
    $adminController = new AdminController();
    $adminController->updateCourse();
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
