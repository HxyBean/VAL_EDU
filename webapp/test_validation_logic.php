<?php
// Simple test to verify the AdminController time validation logic
echo "Testing AdminController Time Validation Logic\n";
echo "=============================================\n";

// Simulate the exact validation logic from AdminController
function validateScheduleTime($inputTime) {
    $data = ['schedule_time' => $inputTime];
    
    echo "Input: '$inputTime'\n";
    
    // Validate and format schedule time (copied from AdminController)
    if (!empty($data['schedule_time'])) {
        // Accept both H:MM and HH:MM formats
        if (preg_match('/^\d{1,2}:\d{2}$/', $data['schedule_time'])) {
            // Format to ensure HH:MM format (pad hour with zero if needed)
            $timeComponents = explode(':', $data['schedule_time']);
            $hour = str_pad($timeComponents[0], 2, '0', STR_PAD_LEFT);
            $minute = $timeComponents[1];
            $data['schedule_time'] = $hour . ':' . $minute;
            echo "✓ Formatted to: '{$data['schedule_time']}'\n";
        } else {
            // Try to convert from other formats
            $time = date('H:i', strtotime($data['schedule_time']));
            if ($time === false) {
                throw new Exception('Invalid time format');
            }
            $data['schedule_time'] = $time;
            echo "✓ Converted to: '{$data['schedule_time']}'\n";
        }
        
        // Additional validation for reasonable time range
        $timeComponents = explode(':', $data['schedule_time']);
        $hour = intval($timeComponents[0]);
        $minute = intval($timeComponents[1]);
        
        if ($hour < 6 || $hour > 22) {
            throw new Exception('Hour must be between 6 and 22');
        }
        
        if ($minute < 0 || $minute > 59) {
            throw new Exception('Invalid minute');
        }
    }
    
    return $data['schedule_time'];
}

// Test cases
$testCases = ['7:00', '07:00', '14:30', '9:15', '23:45'];

foreach ($testCases as $test) {
    try {
        $result = validateScheduleTime($test);
        echo "Result: '$result' (Database would store: '$result:00')\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "---\n";
}

// The issue might be that the form is NOT going through AdminController
// Let's check if there are other ways courses are created
echo "\nPossible Issues:\n";
echo "1. Form might be directly calling AdminModel instead of AdminController\n";
echo "2. JavaScript might be interfering with form submission\n";
echo "3. There might be multiple course creation endpoints\n";

?>
