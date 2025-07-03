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
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
                throw new Exception('Unauthorized');
            }

            $tutors = $this->adminModel->getAllTutors();
            
            echo json_encode([
                'success' => true,
                'tutors' => $tutors
            ]);

        } catch (Exception $e) {
            error_log("Error in getTutors: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
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
    
    // API endpoint for getting course details
    public function getCourseDetails() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        try {
            $courseId = intval($_GET['id'] ?? 0);
            
            if ($courseId <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID khóa học không hợp lệ']);
                exit();
            }
            
            $courseDetails = $this->adminModel->getCourseDetails($courseId);
            
            if ($courseDetails) {
                echo json_encode([
                    'success' => true,
                    'data' => $courseDetails
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin khóa học'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Error getting course details: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ]);
        }
        exit();
    }
    
    // API endpoint for updating course
    public function updateCourse() {
        header('Content-Type: application/json');
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed');
            }

            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
                throw new Exception('Unauthorized');
            }

            error_log("Update course API called");
            error_log("POST data: " . json_encode($_POST));

            $courseId = intval($_POST['course_id'] ?? 0);
            
            // Validate course ID
            if ($courseId <= 0) {
                throw new Exception('ID khóa học không hợp lệ');
            }

            // Validate required fields
            $required_fields = ['class_name', 'class_year', 'class_level', 'subject', 'max_students', 'sessions_total', 'price_per_session', 'schedule_time', 'schedule_duration', 'schedule_days', 'start_date', 'end_date'];
            
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Trường {$field} là bắt buộc");
                }
            }

            // Validate and sanitize input data
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

            // Additional validations
            if ($data['max_students'] <= 0) {
                throw new Exception('Số học sinh tối đa phải lớn hơn 0');
            }

            if ($data['sessions_total'] <= 0) {
                throw new Exception('Số buổi học phải lớn hơn 0');
            }

            if ($data['price_per_session'] < 0) {
                throw new Exception('Giá tiền không được âm');
            }

            if (strtotime($data['start_date']) >= strtotime($data['end_date'])) {
                throw new Exception('Ngày kết thúc phải sau ngày khai giảng');
            }

            error_log("Validated data: " . json_encode($data));

            // Perform update
            $result = $this->adminModel->updateCourse($courseId, $data);
            
            if ($result === true) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Cập nhật khóa học thành công'
                ]);
            } else {
                throw new Exception('Cập nhật khóa học thất bại');
            }

        } catch (Exception $e) {
            error_log("Error in updateCourse: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }
    
    // API endpoint for creating tutor
    public function createTutor() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
                throw new Exception('Unauthorized');
            }

            // Validate required fields
            $required = ['fullname', 'email', 'username', 'password'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field $field is required");
                }
            }

            // Validate email format
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format');
            }

            // Validate username format
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $_POST['username'])) {
                throw new Exception('Username can only contain letters, numbers and underscore');
            }

            // Validate password length
            if (strlen($_POST['password']) < 6) {
                throw new Exception('Password must be at least 6 characters');
            }

            $tutorData = [
                'full_name' => trim($_POST['fullname']),
                'email' => trim($_POST['email']),
                'username' => trim($_POST['username']),
                'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'phone' => trim($_POST['phone'] ?? ''),
                'role' => 'tutor'
            ];

            $result = $this->adminModel->createTutor($tutorData);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Tutor created successfully'
                ]);
            } else {
                throw new Exception('Failed to create tutor');
            }

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }

    // API endpoint for getting tutor details
    public function getTutorDetails() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
                throw new Exception('Unauthorized');
            }

            $tutorId = intval($_GET['id'] ?? 0);
            if ($tutorId <= 0) {
                throw new Exception('Invalid tutor ID');
            }

            error_log("Getting details for tutor ID: " . $tutorId);

            $tutor = $this->adminModel->getTutorDetails($tutorId);
            
            if (!$tutor) {
                throw new Exception('Tutor not found');
            }

            echo json_encode([
                'success' => true,
                'tutor' => $tutor
            ]);

        } catch (Exception $e) {
            error_log("Error in getTutorDetails: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }

    // API endpoint for updating tutor
    public function updateTutor() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
                throw new Exception('Unauthorized');
            }

            $tutorId = intval($_POST['tutor_id'] ?? 0);
            if ($tutorId <= 0) {
                throw new Exception('Invalid tutor ID');
            }

            // Validate required fields
            $required = ['fullname', 'email', 'phone'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field $field is required");
                }
            }

            // Validate email format
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format');
            }

            $tutorData = [
                'full_name' => trim($_POST['fullname']),
                'email' => trim($_POST['email']),
                'phone' => trim($_POST['phone'])
            ];

            $result = $this->adminModel->updateTutor($tutorId, $tutorData);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Cập nhật thông tin giáo viên thành công'
                ]);
            } else {
                throw new Exception('Failed to update tutor');
            }

        } catch (Exception $e) {
            error_log("Error in updateTutor: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }

    // API endpoint for getting all students
    public function getStudents() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
                throw new Exception('Unauthorized');
            }

            $students = $this->adminModel->getAllStudents();
            
            echo json_encode([
                'success' => true,
                'students' => $students
            ]);

        } catch (Exception $e) {
            error_log("Error in getStudents: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }

    // API endpoint for getting student details
    public function getStudentDetails() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
                throw new Exception('Unauthorized');
            }

            $studentId = intval($_GET['id'] ?? 0);
            if ($studentId <= 0) {
                throw new Exception('Invalid student ID');
            }

            $student = $this->adminModel->getStudentDetails($studentId);
            
            if (!$student) {
                throw new Exception('Student not found');
            }

            echo json_encode([
                'success' => true,
                'student' => $student
            ]);

        } catch (Exception $e) {
            error_log("Error in getStudentDetails: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }
    
    // API endpoint for updating student
    public function updateStudent() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
                throw new Exception('Unauthorized');
            }

            $studentId = intval($_POST['student_id'] ?? 0);
            if ($studentId <= 0) {
                throw new Exception('ID học viên không hợp lệ');
            }

            // Validate required fields
            $required = ['fullname', 'email'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Trường $field là bắt buộc");
                }
            }

            // Validate email format
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email không hợp lệ');
            }

            $studentData = [
                'full_name' => trim($_POST['fullname']),
                'email' => trim($_POST['email']),
                'phone' => trim($_POST['phone'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? intval($_POST['is_active']) : 1
            ];

            $result = $this->adminModel->updateStudent($studentId, $studentData);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Cập nhật thông tin học viên thành công'
                ]);
            } else {
                throw new Exception('Không thể cập nhật thông tin học viên');
            }

        } catch (Exception $e) {
            error_log("Error in updateStudent: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }

    // API endpoint for getting available courses
    public function getAvailableCourses() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
                throw new Exception('Unauthorized');
            }

            $courses = $this->adminModel->getAvailableCourses();
            
            echo json_encode([
                'success' => true,
                'courses' => $courses
            ]);

        } catch (Exception $e) {
            error_log("Error in getAvailableCourses: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }

    // API endpoint for enrolling student in course
    public function enrollStudent() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
                throw new Exception('Unauthorized');
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            $studentId = intval($input['student_id'] ?? 0);
            $courseId = intval($input['course_id'] ?? 0);

            if ($studentId <= 0 || $courseId <= 0) {
                throw new Exception('Dữ liệu không hợp lệ');
            }

            $result = $this->adminModel->enrollStudent($studentId, $courseId);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Thêm học viên vào lớp thành công'
                ]);
            } else {
                throw new Exception('Không thể thêm học viên vào lớp');
            }

        } catch (Exception $e) {
            error_log("Error in enrollStudent: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }

    // API endpoint for removing student from course
    public function removeFromCourse() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
                throw new Exception('Unauthorized');
            }

            // Get and decode JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Log received data
            error_log("Received data for removeFromCourse: " . json_encode($input));
            
            // Validate student ID
            $studentId = isset($input['student_id']) ? intval($input['student_id']) : 0;
            if ($studentId <= 0) {
                throw new Exception('ID học viên không hợp lệ');
            }

            // Validate course ID
            $courseId = isset($input['course_id']) ? intval($input['course_id']) : 0;
            if ($courseId <= 0) {
                throw new Exception('ID khóa học không hợp lệ');
            }

            $result = $this->adminModel->removeFromCourse($studentId, $courseId);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Đã xóa học viên khỏi khóa học thành công'
                ]);
            } else {
                throw new Exception('Không thể xóa học viên khỏi khóa học');
            }

        } catch (Exception $e) {
            error_log("Error in removeFromCourse: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }

    // API endpoint for getting all parents
    public function getParents() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit();
            }

            $parents = $this->adminModel->getAllParents();
            
            echo json_encode([
                'success' => true,
                'parents' => $parents
            ]);

        } catch (Exception $e) {
            error_log("Error in getParents: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }

    // API endpoint for getting parent details
    public function getParentDetails() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit();
            }

            $parentId = intval($_GET['parent_id'] ?? 0);
            
            if (!$parentId) {
                throw new Exception("Parent ID is required");
            }

            $parent = $this->adminModel->getParentDetails($parentId);
            
            echo json_encode([
                'success' => true,
                'parent' => $parent
            ]);

        } catch (Exception $e) {
            error_log("Error getting parent details: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }

    // API endpoint for updating parent - giống như updateStudent
    public function updateParent() {
        // Enable error reporting for debugging
        ini_set('display_errors', 0);
        error_reporting(E_ALL);
        
        header('Content-Type: application/json');
        
        // Log all incoming data for debugging
        error_log("=== UPDATE PARENT CONTROLLER DEBUG ===");
        error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
        error_log("POST data: " . json_encode($_POST));
        error_log("SESSION user_id: " . ($_SESSION['user_id'] ?? 'null'));
        error_log("SESSION user_role: " . ($_SESSION['user_role'] ?? 'null'));
        
        try {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit();
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit();
            }

            $parentId = intval($_POST['parent_id'] ?? 0);
            if (!$parentId) {
                throw new Exception("Parent ID is required");
            }

            $data = [
                'full_name' => trim($_POST['full_name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'is_active' => intval($_POST['is_active'] ?? 1)
            ];

            error_log("Parsed data: " . json_encode($data));

            // Validation
            if (empty($data['full_name']) || empty($data['email'])) {
                throw new Exception("Full name and email are required");
            }

            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }

            error_log("Calling adminModel->updateParent with ID: $parentId");
            $result = $this->adminModel->updateParent($parentId, $data);
            error_log("updateParent result: " . var_export($result, true));
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Parent updated successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to update parent'
                ]);
            }

        } catch (Exception $e) {
            error_log("EXCEPTION in updateParent: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]);
        }
        exit();
    }

    // API endpoint for creating parent
    public function createParent() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit();
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit();
            }

            $data = [
                'username' => trim($_POST['username'] ?? ''),
                'full_name' => trim($_POST['full_name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'phone' => trim($_POST['phone'] ?? ''),
                'address' => trim($_POST['address'] ?? '')
            ];

            // Validation
            if (empty($data['username']) || empty($data['full_name']) || 
                empty($data['email']) || empty($data['password'])) {
                throw new Exception("All required fields must be filled");
            }

            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }

            if (strlen($data['password']) < 6) {
                throw new Exception("Password must be at least 6 characters");
            }

            $result = $this->adminModel->createParent($data);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Parent created successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to create parent'
                ]);
            }

        } catch (Exception $e) {
            error_log("Error creating parent: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }

    // API endpoint for searching students
    public function searchStudents() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit();
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit();
            }

            $searchTerm = trim($_POST['search_term'] ?? '');
            $parentId = intval($_POST['parent_id'] ?? 0);

            if (empty($searchTerm)) {
                echo json_encode(['success' => false, 'message' => 'Search term is required']);
                exit();
            }

            $students = $this->adminModel->searchStudentsForLink($searchTerm, $parentId);
            
            echo json_encode([
                'success' => true,
                'students' => $students
            ]);

        } catch (Exception $e) {
            error_log("Error searching students: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }

    // API endpoint for linking parent and student
    public function linkParentStudent() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit();
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit();
            }

            $parentId = intval($_POST['parent_id'] ?? 0);
            $studentId = intval($_POST['student_id'] ?? 0);
            $relationshipType = trim($_POST['relationship_type'] ?? '');
            $isPrimary = intval($_POST['is_primary'] ?? 0);

            if (!$parentId || !$studentId) {
                throw new Exception("Parent ID and Student ID are required");
            }

            if (!in_array($relationshipType, ['father', 'mother', 'guardian'])) {
                throw new Exception("Invalid relationship type");
            }

            $result = $this->adminModel->linkParentStudent($parentId, $studentId, $relationshipType, $isPrimary);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Student linked to parent successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to link student to parent'
                ]);
            }

        } catch (Exception $e) {
            error_log("Error linking parent and student: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }
}
?>
