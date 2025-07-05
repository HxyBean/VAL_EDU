<?php
// Debug the parameter types
echo "Parameter analysis for createCourse:\n";
echo "===================================\n";

$data = [
    'class_name' => 'Test Course',        // string
    'class_year' => 2025,                 // integer  
    'class_level' => 'Level',             // string
    'subject' => 'Subject',               // string
    'description' => 'Description',       // string
    'max_students' => 20,                 // integer
    'sessions_total' => 30,               // integer
    'price_per_session' => 100000.0,     // decimal/double
    'schedule_time' => '08:00:00',        // string
    'schedule_duration' => 90,            // integer
    'schedule_days' => 'T2,T4',           // string
    'start_date' => '2025-01-01',         // string
    'end_date' => '2025-12-31'            // string
];

$expected_types = ['s', 'i', 's', 's', 's', 'i', 'i', 'd', 's', 'i', 's', 's', 's'];

echo "Order | Parameter        | Value      | Type | Expected\n";
echo "------|------------------|------------|------|----------\n";

$i = 1;
foreach ($data as $key => $value) {
    $type = is_int($value) ? 'i' : (is_float($value) ? 'd' : 's');
    $expected = $expected_types[$i-1];
    $match = ($type === $expected) ? '✓' : '✗';
    
    echo sprintf("%5d | %-16s | %-10s | %4s | %8s %s\n", 
        $i, $key, 
        (is_string($value) ? substr($value, 0, 10) : $value), 
        $type, $expected, $match
    );
    $i++;
}

echo "\nCorrect type string: " . implode('', $expected_types) . "\n";
echo "Length: " . count($expected_types) . "\n";
?>
