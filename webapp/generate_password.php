<?php
// Generate correct password hashes for your test users

$passwords = [
    'admin123' => 'admin',
    'tutor123' => 'tutor1', 
    'student123' => 'student1',
    'parent123' => 'parent1'
];

echo "<h2>Generate Password Hashes</h2>";
echo "<p>Copy and paste these SQL commands into phpMyAdmin:</p>";
echo "<textarea rows='15' cols='100'>";

foreach ($passwords as $plainPassword => $username) {
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
    echo "UPDATE users SET password = '{$hashedPassword}' WHERE username = '{$username}';\n";
}

echo "</textarea>";

echo "<h3>Test Password Verification:</h3>";
foreach ($passwords as $plainPassword => $username) {
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
    $isValid = password_verify($plainPassword, $hashedPassword);
    echo "<p><strong>{$username}:</strong> Password '{$plainPassword}' → " . ($isValid ? "✅ Valid" : "❌ Invalid") . "</p>";
}
?>