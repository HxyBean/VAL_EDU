<?php
session_start();

// Set student session data
$_SESSION['user_id'] = 3;
$_SESSION['user_role'] = 'student';
$_SESSION['user_name'] = 'Alice Johnson';
$_SESSION['username'] = 'student1';

echo "<h2>Session Set Successfully</h2>";
echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
echo "<p>Role: " . $_SESSION['user_role'] . "</p>";
echo "<p>Name: " . $_SESSION['user_name'] . "</p>";

echo "<p><a href='/webapp/student' target='_blank'>Go to Student Dashboard</a></p>";
?>
