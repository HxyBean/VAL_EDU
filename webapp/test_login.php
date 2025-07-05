<?php
session_start();

// Simulate student login
$_SESSION['user_id'] = 3; // Assuming student1 has ID 3
$_SESSION['user_role'] = 'student';
$_SESSION['user_name'] = 'Alice Johnson';
$_SESSION['username'] = 'student1';

// Redirect to student dashboard
header('Location: /webapp/student');
exit();
?>
