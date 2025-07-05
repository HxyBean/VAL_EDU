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
    
    // API endpoint for getting student schedule
    public function getStudentSchedule() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $user_id = $_SESSION['user_id'];
        $start_date = $_GET['start_date'] ?? null;
        $end_date = $_GET['end_date'] ?? null;
        
        try {
            $schedule = $this->studentModel->getStudentSchedule($user_id, $start_date, $end_date);
            echo json_encode(['success' => true, 'schedule' => $schedule]);
        } catch (Exception $e) {
            error_log("Error getting student schedule: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error loading schedule']);
        }
        exit();
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
        
        error_log("Update student profile API called");
        error_log("POST data: " . json_encode($_POST));
        error_log("Session user_id: " . $_SESSION['user_id']);
        
        $user_id = $_SESSION['user_id'];
        
        // Validate input data
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        // Server-side validation
        if (empty($full_name) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Tên và email không được để trống']);
            exit();
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Email không hợp lệ']);
            exit();
        }
        
        // Phone validation (optional but if provided, must be valid)
        if (!empty($phone) && !preg_match('/^[0-9\s\-\+\(\)]{10,15}$/', $phone)) {
            echo json_encode(['success' => false, 'message' => 'Số điện thoại không hợp lệ']);
            exit();
        }
        
        $data = [
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone
        ];
        
        try {
            $result = $this->studentModel->updateStudentProfile($user_id, $data);
            
            if ($result === true) {
                // Update session data only if update was successful
                $_SESSION['user_name'] = $data['full_name'];
                $_SESSION['user_email'] = $data['email'];
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Cập nhật thông tin thành công',
                    'data' => [
                        'full_name' => $data['full_name'],
                        'email' => $data['email'],
                        'phone' => $data['phone']
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Không thể cập nhật thông tin. Vui lòng kiểm tra lại dữ liệu.'
                ]);
            }
        } catch (Exception $e) {
            error_log("Error updating student profile: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Lỗi hệ thống. Vui lòng thử lại sau.'
            ]);
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
        
        error_log("Change student password API called");
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
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin học viên']);
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
                echo json_encode(['success' => false, 'message' => 'Đổi mật khẩu thất bại']);
            }
        } catch (Exception $e) {
            error_log("Error changing student password: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
        exit();
    }
    
    public function sendParentConnection() {
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
        
        try {
            $user_id = $_SESSION['user_id'];
            $parent_email = trim($_POST['parent_email'] ?? '');
            $message = trim($_POST['message'] ?? '');
            
            // Validation
            if (empty($parent_email)) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng nhập email phụ huynh']);
                exit();
            }
            
            if (!filter_var($parent_email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Email không hợp lệ']);
                exit();
            }
            
            // Send parent connection request
            $result = $this->studentModel->sendParentConnectionRequest($user_id, $parent_email, $message);
            
            if ($result) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Đã gửi yêu cầu kết nối tới phụ huynh thành công!'
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Không thể gửi yêu cầu kết nối. Vui lòng thử lại.'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Error sending parent connection: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ]);
        }
        exit();
    }
}
?>
