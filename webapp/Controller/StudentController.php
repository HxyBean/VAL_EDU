<?php
require_once(__DIR__ . '/../Base/BaseController.php');
require_once(__DIR__ . '/../Model/StudentModel.php');

class StudentController extends BaseController {
    private $studentModel;
    
    public function __construct() {
        parent::__construct();
        $this->studentModel = new StudentModel();
    }

    public function dashboard() {
        // Check if user is logged in and is a student
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
            header('Location: /webapp/login');
            exit();
        }

        // Get student data
        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['user_name'] ?? '';
        $username = $_SESSION['username'] ?? '';
        
        // Get student-specific data from database
        $studentData = null;
        $courses = [];
        $attendance = [];
        $stats = [];
        $payments = [];
        
        try {
            // Get student profile information
            $studentData = $this->studentModel->getStudentById($user_id);
            
            // Get student's enrolled courses
            $courses = $this->studentModel->getStudentCourses($user_id);
            
            // Get student attendance records
            $attendance = $this->studentModel->getStudentAttendance($user_id);
            
            // Get student statistics
            $stats = $this->studentModel->getStudentStats($user_id);
            
            // Get student payment records
            $payments = $this->studentModel->getStudentPayments($user_id);
            
        } catch (Exception $e) {
            error_log("Error getting student data: " . $e->getMessage());
        }
        
        $data = [
            'page_title' => 'Student Dashboard - VAL Edu',
            'user_logged_in' => true,
            'user_name' => $user_name,
            'username' => $username,
            'user_role' => 'student',
            'student_data' => $studentData,
            'courses' => $courses,
            'attendance' => $attendance,
            'stats' => $stats,
            'payments' => $payments
        ];
        
        // Render the Student view
        $this->renderView('Student/Student', $data);
    }
    
    // API endpoint for updating student profile
    public function updateProfile() {
        // Set content type first
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        error_log("Update profile API called");
        error_log("POST data: " . json_encode($_POST));
        error_log("Session user_id: " . $_SESSION['user_id']);
        
        $user_id = $_SESSION['user_id'];
        $data = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? '')
        ];
        
        // Validation
        if (empty($data['full_name']) || empty($data['email'])) {
            echo json_encode(['success' => false, 'message' => 'Tên và email không được để trống']);
            exit();
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Email không hợp lệ']);
            exit();
        }
        
        try {
            $result = $this->studentModel->updateStudentProfile($user_id, $data);
            if ($result) {
                // Update session data
                $_SESSION['user_name'] = $data['full_name'];
                $_SESSION['user_email'] = $data['email'];
                
                echo json_encode(['success' => true, 'message' => 'Cập nhật thông tin thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại - Không có dữ liệu nào được thay đổi']);
            }
        } catch (Exception $e) {
            error_log("Error updating student profile: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
        exit();
    }
    
    // API endpoint for changing password
    public function changePassword() {
        // Set content type first
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        error_log("Change password API called");
        error_log("POST data keys: " . implode(', ', array_keys($_POST)));
        error_log("Session user_id: " . $_SESSION['user_id']);
        
        $user_id = $_SESSION['user_id'];
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
            exit();
        }
        
        if ($new_password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu xác nhận không khớp']);
            exit();
        }
        
        if (strlen($new_password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự']);
            exit();
        }
        
        try {
            // Verify current password
            $student = $this->studentModel->getStudentById($user_id);
            if (!$student) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin học sinh']);
                exit();
            }
            
            if (!password_verify($current_password, $student['password'])) {
                echo json_encode(['success' => false, 'message' => 'Mật khẩu hiện tại không đúng']);
                exit();
            }
            
            // Change password
            $result = $this->studentModel->changePassword($user_id, $new_password);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Đổi mật khẩu thất bại - Không có dữ liệu nào được thay đổi']);
            }
        } catch (Exception $e) {
            error_log("Error changing password: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
        exit();
    }
    
    public function sendParentConnection() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        // Check if user is logged in and is a student
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }
        
        try {
            $student_id = $_SESSION['user_id'];
            $parent_email = trim($_POST['parent_email'] ?? '');
            $parent_phone = trim($_POST['parent_phone'] ?? '');
            $parent_name = trim($_POST['parent_name'] ?? '');
            $message = trim($_POST['message'] ?? '');
            
            // Validate required fields
            if (empty($parent_email)) {
                echo json_encode(['success' => false, 'message' => 'Email phụ huynh là bắt buộc']);
                return;
            }
            
            // Validate email format
            if (!filter_var($parent_email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Email không hợp lệ']);
                return;
            }
            
            // Use the studentModel's database connection
            $db = $this->studentModel->getConnection();
            
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
                    return;
                }
            }
            
            // Log the connection request for now
            error_log("Parent connection request: Student ID $student_id wants to connect with Parent ID $parent_id (Email: $parent_email)");
            
            echo json_encode([
                'success' => true, 
                'message' => 'Đã gửi yêu cầu kết nối thành công! Phụ huynh sẽ nhận được thông báo qua email.'
            ]);
            
        } catch (Exception $e) {
            error_log("Send parent connection error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }
}
?>
    private function getDbConnection() {
        if (!$this->db) {
            // If db is not set, create a new connection
            require_once(__DIR__ . '/../config/database.php');
            $database = new Database();
            $this->db = $database->getConnection();
        }
        return $this->db;
    }
}
?>
