<?php
require_once(__DIR__ . '/../Base/BaseController.php');
require_once(__DIR__ . '/../Model/TutorModel.php');

class TutorController extends BaseController {
    private $tutorModel;
    
    public function __construct() {
        parent::__construct();
        $this->tutorModel = new TutorModel();
    }

    public function dashboard() {
        // Check if user is logged in and is a tutor
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'tutor') {
            header('Location: /webapp/login');
            exit();
        }

        // Get tutor data
        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['user_name'] ?? $_SESSION['full_name'] ?? '';
        $username = $_SESSION['username'] ?? '';
        
        // Debug logging
        error_log("Tutor Dashboard - User ID: " . $user_id);
        error_log("Tutor Dashboard - User Name: " . $user_name);
        error_log("Tutor Dashboard - Username: " . $username);
        error_log("Tutor Dashboard - Session data: " . json_encode($_SESSION));
        
        // Get tutor-specific data from database
        $tutorData = null;
        $classes = [];
        $sessions = [];
        $stats = [];
        $payments = [];
        
        try {
            // Get tutor profile information
            $tutorData = $this->tutorModel->getTutorById($user_id);
            
            // If we have tutor data from DB and no user_name in session, use it
            if ($tutorData && empty($user_name)) {
                $user_name = $tutorData['full_name'];
                // Update session for future use
                $_SESSION['user_name'] = $user_name;
            }
            
            // Get tutor's assigned classes
            $classes = $this->tutorModel->getTutorClasses($user_id);
            
            // Get tutor session records
            $sessions = $this->tutorModel->getTutorSessions($user_id);
            
            // Get tutor statistics
            $stats = $this->tutorModel->getTutorStats($user_id);
            
            // Get tutor payment records
            $payments = $this->tutorModel->getTutorPayments($user_id);
            
        } catch (Exception $e) {
            error_log("Error getting tutor data: " . $e->getMessage());
        }
        
        $data = [
            'page_title' => 'Tutor Dashboard - VAL Edu',
            'user_logged_in' => true,
            'user_name' => $user_name,
            'username' => $username,
            'user_role' => 'tutor',
            'tutor_data' => $tutorData,
            'classes' => $classes,
            'sessions' => $sessions,
            'stats' => $stats,
            'payments' => $payments
        ];
        
        // Debug logging
        error_log("Data passed to tutor view - user_name: " . ($data['user_name'] ?? 'NULL'));
        error_log("Classes found: " . count($classes));
        
        // Render the Tutor view
        $this->renderView('Tutor/Tutor', $data);
    }
    
    // API endpoint for updating tutor profile
    public function updateProfile() {
        // Set content type first
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'tutor') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        error_log("Update tutor profile API called");
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
            $result = $this->tutorModel->updateTutorProfile($user_id, $data);
            if ($result) {
                // Update session data
                $_SESSION['user_name'] = $data['full_name'];
                $_SESSION['user_email'] = $data['email'];
                
                echo json_encode(['success' => true, 'message' => 'Cập nhật thông tin thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại - Không có dữ liệu nào được thay đổi']);
            }
        } catch (Exception $e) {
            error_log("Error updating tutor profile: " . $e->getMessage());
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
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'tutor') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        error_log("Change tutor password API called");
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
            $tutor = $this->tutorModel->getTutorById($user_id);
            if (!$tutor) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin giảng viên']);
                exit();
            }
            
            if (!password_verify($current_password, $tutor['password'])) {
                echo json_encode(['success' => false, 'message' => 'Mật khẩu hiện tại không đúng']);
                exit();
            }
            
            // Change password
            $result = $this->tutorModel->changePassword($user_id, $new_password);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Đổi mật khẩu thất bại - Không có dữ liệu nào được thay đổi']);
            }
        } catch (Exception $e) {
            error_log("Error changing tutor password: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
        exit();
    }
    
    // API endpoint for getting students in a class
    public function getStudentsInClass() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'tutor') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $class_id = intval($_POST['class_id'] ?? 0);
        $tutor_id = $_SESSION['user_id'];
        
        if (!$class_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid class ID']);
            exit();
        }
        
        try {
            $students = $this->tutorModel->getStudentsInClass($class_id, $tutor_id);
            $next_session = $this->tutorModel->getNextSessionNumber($class_id);
            
            // Check if attendance already taken today
            $today = date('Y-m-d');
            $existing_session = $this->tutorModel->checkIfAttendanceTakenToday($class_id, $today);
            
            echo json_encode([
                'success' => true, 
                'students' => $students,
                'next_session' => $next_session,
                'attendance_taken' => $existing_session !== false,
                'existing_session_id' => $existing_session
            ]);
        } catch (Exception $e) {
            error_log("Error getting students: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error loading students']);
        }
        exit();
    }
    
    // API endpoint for saving attendance
    public function saveAttendance() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'tutor') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $class_id = intval($_POST['class_id'] ?? 0);
        $attendance_data = $_POST['attendance'] ?? [];
        $topic = trim($_POST['topic'] ?? '');
        $tutor_id = $_SESSION['user_id'];
        
        if (!$class_id || empty($attendance_data)) {
            echo json_encode(['success' => false, 'message' => 'Missing required data']);
            exit();
        }
        
        try {
            $today = date('Y-m-d');
            
            // Check if attendance already taken today
            $existing_session = $this->tutorModel->checkIfAttendanceTakenToday($class_id, $today);
            
            if ($existing_session) {
                // Update existing session attendance
                $session_id = $existing_session;
                $result = $this->tutorModel->saveAttendance($session_id, $attendance_data, $tutor_id);
            } else {
                // Create new session and save attendance
                $session_id = $this->tutorModel->createSession($class_id, $tutor_id, $today, $topic);
                if ($session_id) {
                    $result = $this->tutorModel->saveAttendance($session_id, $attendance_data, $tutor_id);
                } else {
                    $result = false;
                }
            }
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Điểm danh đã được lưu thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu điểm danh']);
            }
        } catch (Exception $e) {
            error_log("Error saving attendance: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
        exit();
    }
     
    // API endpoint for getting attendance history
    public function getAttendanceHistory() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'tutor') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $class_id = intval($_POST['class_id'] ?? 0);
        $tutor_id = $_SESSION['user_id'];
        
        if (!$class_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid class ID']);
            exit();
        }
        
        try {
            $history_data = $this->tutorModel->getAttendanceHistory($class_id, $tutor_id);
            $scheduled_sessions = $this->tutorModel->getClassScheduledSessions($class_id, $tutor_id);
            
            echo json_encode([
                'success' => true, 
                'history' => $history_data,
                'scheduled_sessions' => $scheduled_sessions
            ]);
        } catch (Exception $e) {
            error_log("Error getting attendance history: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error loading attendance history']);
        }
        exit();
    }
    
    // API endpoint for getting detailed student list with attendance stats
    public function getStudentList() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'tutor') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $class_id = intval($_POST['class_id'] ?? 0);
        $tutor_id = $_SESSION['user_id'];
        
        if (!$class_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid class ID']);
            exit();
        }
        
        try {
            $students = $this->tutorModel->getDetailedStudentList($class_id, $tutor_id);
            $attendanceStats = $this->tutorModel->getClassAttendanceStats($class_id, $tutor_id);
            
            echo json_encode([
                'success' => true, 
                'students' => $students,
                'attendance_stats' => $attendanceStats
            ]);
        } catch (Exception $e) {
            error_log("Error getting student list: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error loading student list']);
        }
        exit();
    }
    
    // API endpoint for getting tutor schedule
    public function getTutorSchedule() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'tutor') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        
        $user_id = $_SESSION['user_id'];
        $start_date = $_GET['start_date'] ?? null;
        $end_date = $_GET['end_date'] ?? null;
        
        try {
            $schedule = $this->tutorModel->getTutorSchedule($user_id, $start_date, $end_date);
            echo json_encode(['success' => true, 'schedule' => $schedule]);
        } catch (Exception $e) {
            error_log("Error getting tutor schedule: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error loading schedule']);
        }
        exit();
    }
}
?>
