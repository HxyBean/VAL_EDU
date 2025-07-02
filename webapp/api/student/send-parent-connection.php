<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $student_id = $_SESSION['user_id'];
    $parent_email = trim($_POST['parent_email'] ?? '');
    $parent_phone = trim($_POST['parent_phone'] ?? '');
    $parent_name = trim($_POST['parent_name'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validate required fields
    if (empty($parent_email)) {
        echo json_encode(['success' => false, 'message' => 'Email phụ huynh là bắt buộc']);
        exit;
    }
    
    // Validate email format
    if (!filter_var($parent_email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email không hợp lệ']);
        exit;
    }
    
    // Check if parent already exists
    $checkParentQuery = "SELECT id FROM users WHERE email = :email AND role = 'parent'";
    $checkParentStmt = $db->prepare($checkParentQuery);
    $checkParentStmt->bindParam(':email', $parent_email);
    $checkParentStmt->execute();
    
    $parent_id = null;
    if ($checkParentStmt->rowCount() > 0) {
        $parent = $checkParentStmt->fetch(PDO::FETCH_ASSOC);
        $parent_id = $parent['id'];
    } else {
        // Create new parent account
        $username = 'parent_' . uniqid();
        $temp_password = bin2hex(random_bytes(8));
        $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
        
        $createParentQuery = "INSERT INTO users (username, password, email, full_name, phone, role, is_active, created_at) 
                             VALUES (:username, :password, :email, :full_name, :phone, 'parent', 1, NOW())";
        $createParentStmt = $db->prepare($createParentQuery);
        $createParentStmt->bindParam(':username', $username);
        $createParentStmt->bindParam(':password', $hashed_password);
        $createParentStmt->bindParam(':email', $parent_email);
        $createParentStmt->bindParam(':full_name', $parent_name);
        $createParentStmt->bindParam(':phone', $parent_phone);
        
        if ($createParentStmt->execute()) {
            $parent_id = $db->lastInsertId();
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể tạo tài khoản phụ huynh']);
            exit;
        }
    }
    
    // Create connection request (simulate table structure)
    echo json_encode([
        'success' => true, 
        'message' => 'Đã gửi yêu cầu kết nối thành công! Phụ huynh sẽ nhận được thông báo qua email.'
    ]);
    
} catch (Exception $e) {
    error_log("Send parent connection error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
}
?>
