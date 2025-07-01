<?php
// filepath: d:\SCHOOL\PTWeb\XAMPP\htdocs\webapp\Controller\AdminController.php
require_once(__DIR__ . '/../Base/BaseController.php');
require_once(__DIR__ . '/../Model/AdminModel.php');

class AdminController extends BaseController {
    private $adminModel;
    
    public function __construct() {
        parent::__construct();
        $this->adminModel = new AdminModel();
    }
    
    public function dashboard() {
        // Check if user is logged in and has admin role
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /webapp/login');
            exit();
        }
        
        // Get admin data
        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['user_name'] ?? $_SESSION['full_name'] ?? '';
        $username = $_SESSION['username'] ?? '';
        
        // Debug logging
        error_log("Admin Dashboard - User ID: " . $user_id);
        error_log("Admin Dashboard - User Name: " . $user_name);
        error_log("Admin Dashboard - Username: " . $username);
        error_log("Admin Dashboard - Session data: " . json_encode($_SESSION));
        
        // Get admin-specific data from database
        $adminData = null;
        $stats = [];
        $students = [];
        $tutors = [];
        $classes = [];
        $activities = [];
        
        try {
            // Get admin profile information
            $adminData = $this->adminModel->getAdminById($user_id);
            
            // If we have admin data from DB and no user_name in session, use it
            if ($adminData && empty($user_name)) {
                $user_name = $adminData['full_name'];
                // Update session for future use
                $_SESSION['user_name'] = $user_name;
            }
            
            // Get system statistics
            $stats = $this->adminModel->getSystemStats();
            
            // Get recent data for overview
            $students = $this->adminModel->getAllStudents(10, 0);
            $tutors = $this->adminModel->getAllTutors(10, 0);
            $classes = $this->adminModel->getAllClasses(10, 0);
            $activities = $this->adminModel->getRecentActivities(10);
            
            // Get chart data
            $studentRegistrationTrend = $this->adminModel->getStudentRegistrationTrend();
            $classLevelDistribution = $this->adminModel->getClassLevelDistribution();
            $enrollmentTrend = $this->adminModel->getEnrollmentTrend();
            $currentMonthStats = $this->adminModel->getCurrentMonthStats();
            
        } catch (Exception $e) {
            error_log("Error getting admin data: " . $e->getMessage());
            
            // Set default values for charts
            $studentRegistrationTrend = [];
            $classLevelDistribution = [];
            $enrollmentTrend = [];
            $currentMonthStats = [
                'new_students_this_month' => 0,
                'new_enrollments_this_month' => 0,
                'new_classes_this_month' => 0
            ];
        }
        
        $data = [
            'page_title' => 'Admin Dashboard - VAL Edu',
            'user_logged_in' => true,
            'user_name' => $user_name,
            'username' => $username,
            'user_role' => 'admin',
            'admin_data' => $adminData,
            'stats' => $stats,
            'students' => $students,
            'tutors' => $tutors,
            'classes' => $classes,
            'activities' => $activities,
            // Add chart data
            'student_registration_trend' => $studentRegistrationTrend,
            'class_level_distribution' => $classLevelDistribution,
            'enrollment_trend' => $enrollmentTrend,
            'current_month_stats' => $currentMonthStats
        ];
        
        // Debug logging
        error_log("Data passed to admin view - user_name: " . ($data['user_name'] ?? 'NULL'));
        error_log("Stats found: " . json_encode($stats));
        
        // Render the Admin view
        $this->renderView('Admin/Admin', $data);
    }
    
    // API endpoint for updating admin profile
    public function updateProfile() {
        // Set content type first
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        error_log("Update admin profile API called");
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
            $result = $this->adminModel->updateAdminProfile($user_id, $data);
            if ($result) {
                // Update session data
                $_SESSION['user_name'] = $data['full_name'];
                $_SESSION['user_email'] = $data['email'];
                
                echo json_encode(['success' => true, 'message' => 'Cập nhật thông tin thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại - Không có dữ liệu nào được thay đổi']);
            }
        } catch (Exception $e) {
            error_log("Error updating admin profile: " . $e->getMessage());
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
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        error_log("Change admin password API called");
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
            $admin = $this->adminModel->getAdminById($user_id);
            if (!$admin) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin quản trị viên']);
                exit();
            }
            
            if (!password_verify($current_password, $admin['password'])) {
                echo json_encode(['success' => false, 'message' => 'Mật khẩu hiện tại không đúng']);
                exit();
            }
            
            // Change password
            $result = $this->adminModel->changePassword($user_id, $new_password);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Đổi mật khẩu thất bại - Không có dữ liệu nào được thay đổi']);
            }
        } catch (Exception $e) {
            error_log("Error changing admin password: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
        exit();
    }
    
    // API endpoint for chart data
    public function getChartData() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $chart_type = $_GET['type'] ?? '';
        $year = intval($_GET['year'] ?? date('Y'));
        
        try {
            switch ($chart_type) {
                case 'student_registration':
                    $data = $this->adminModel->getStudentRegistrationTrend($year);
                    break;
                case 'class_distribution':
                    $data = $this->adminModel->getClassLevelDistribution();
                    break;
                case 'enrollment_trend':
                    $data = $this->adminModel->getEnrollmentTrend($year);
                    break;
                case 'current_month':
                    $data = $this->adminModel->getCurrentMonthStats();
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid chart type']);
                    exit();
            }
            
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            error_log("Error getting chart data: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error loading chart data']);
        }
        exit();
    }
    
    // API endpoint for creating course
    public function createCourse() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        error_log("Create course API called");
        error_log("POST data: " . json_encode($_POST));
        
        try {
            // Validate required fields
            $required_fields = ['class_name', 'class_year', 'class_level', 'subject', 'max_students', 
                               'sessions_total', 'price_per_session', 'schedule_time', 'schedule_duration', 
                               'schedule_days', 'start_date', 'end_date'];
            
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode(['success' => false, 'message' => "Trường {$field} là bắt buộc"]);
                    exit();
                }
            }
            
            // Prepare data for insertion
            $data = [
                'class_name' => trim($_POST['class_name']),
                'class_year' => intval($_POST['class_year']),
                'class_level' => trim($_POST['class_level']),
                'subject' => trim($_POST['subject']),
                'description' => trim($_POST['description'] ?? ''),
                'max_students' => intval($_POST['max_students']),
                'sessions_total' => intval($_POST['sessions_total']),
                'price_per_session' => floatval($_POST['price_per_session']),
                'schedule_time' => $_POST['schedule_time'],
                'schedule_duration' => intval($_POST['schedule_duration']),
                'schedule_days' => $_POST['schedule_days'],
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'],
                'tutor_id' => !empty($_POST['tutor_id']) ? intval($_POST['tutor_id']) : null
            ];
            
            // Validate dates
            if (strtotime($data['start_date']) >= strtotime($data['end_date'])) {
                echo json_encode(['success' => false, 'message' => 'Ngày kết thúc phải sau ngày khai giảng']);
                exit();
            }
            
            // Create course
            $courseId = $this->adminModel->createCourse($data);
            
            if ($courseId) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Tạo khóa học thành công!',
                    'course_id' => $courseId
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi tạo khóa học']);
            }
            
        } catch (Exception $e) {
            error_log("Error creating course: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
        exit();
    }
    
    // API endpoint for getting all courses
    public function getCourses() {
        error_log("=== getCourses API DEBUG START ===");
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            error_log("Unauthorized access - user_id: " . ($_SESSION['user_id'] ?? 'null') . ", role: " . ($_SESSION['user_role'] ?? 'null'));
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        try {
            error_log("Session info - ID: " . $_SESSION['user_id'] . ", Role: " . $_SESSION['user_role']);
            error_log("Calling getAllCourses from model");
            
            $courses = $this->adminModel->getAllCourses();
            
            error_log("Model returned: " . gettype($courses));
            error_log("Courses count: " . (is_array($courses) ? count($courses) : 'not array'));
            
            if (is_array($courses) && count($courses) > 0) {
                error_log("Sample course keys: " . implode(', ', array_keys($courses[0])));
            }
            
            $response = ['success' => true, 'courses' => $courses];
            error_log("API Response: " . json_encode($response));
            
            echo json_encode($response);
        } catch (Exception $e) {
            error_log("Error in getCourses API: " . $e->getMessage());
            error_log("Error trace: " . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => 'Error loading courses: ' . $e->getMessage()]);
        }
        
        error_log("=== getCourses API DEBUG END ===");
        exit();
    }
    
    // API endpoint for getting tutors
    public function getTutors() {
        error_log("getTutors method called");
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            error_log("Unauthorized access to getTutors");
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        try {
            error_log("Calling getTutorsForForm from model");
            $tutors = $this->adminModel->getTutorsForForm();
            error_log("Tutors retrieved: " . count($tutors) . " tutors");
            
            echo json_encode(['success' => true, 'tutors' => $tutors]);
        } catch (Exception $e) {
            error_log("Error in getTutors: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error loading tutors: ' . $e->getMessage()]);
        }
        exit();
    }
    
    // API endpoint for closing course
    public function closeCourse() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $courseId = intval($input['course_id'] ?? 0);
            
            if ($courseId <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID khóa học không hợp lệ']);
                exit();
            }
            
            $result = $this->adminModel->closeCourse($courseId);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Đóng khóa học thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể đóng khóa học']);
            }
            
        } catch (Exception $e) {
            error_log("Error closing course: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
        exit();
    }
    
    // API endpoint for reopening course
    public function reopenCourse() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $courseId = intval($input['course_id'] ?? 0);
            
            if ($courseId <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID khóa học không hợp lệ']);
                exit();
            }
            
            $result = $this->adminModel->reopenCourse($courseId);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Mở lại khóa học thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể mở lại khóa học']);
            }
            
        } catch (Exception $e) {
            error_log("Error reopening course: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
        exit();
    }
}
?>
