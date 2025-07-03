<?php
require_once(__DIR__ . '/../Base/BaseController.php');
require_once(__DIR__ . '/../Model/ParentModel.php');

class ParentController extends BaseController {
    private $parentModel;
    
    public function __construct() {
        parent::__construct();
        $this->parentModel = new ParentModel();
    }
    
    public function dashboard() {
        // Check if user is logged in and has parent role
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'parent') {
            header('Location: /webapp/login');
            exit();
        }
        
        // Get parent data
        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['user_name'] ?? $_SESSION['full_name'] ?? '';
        $username = $_SESSION['username'] ?? '';
        
        // Debug logging
        error_log("Parent Dashboard - User ID: " . $user_id);
        error_log("Parent Dashboard - User Name: " . $user_name);
        error_log("Parent Dashboard - Username: " . $username);
        
        // Initialize default values
        $parentData = null;
        $children = [];
        $payments = [];
        $stats = [
            'total_children' => 0,
            'total_classes' => 0,
            'total_sessions' => 0,
            'attended_sessions' => 0,
            'total_payments' => 0,
            'total_paid' => 0,
            'average_attendance_rate' => 0
        ];
        $notifications = [];
        $connection_requests = [];
        
        try {
            // Get parent profile information
            $parentData = $this->parentModel->getParentById($user_id);
            error_log("Parent data loaded: " . ($parentData ? 'YES' : 'NO'));
            
            // If we have parent data from DB and no user_name in session, use it
            if ($parentData && empty($user_name)) {
                $user_name = $parentData['full_name'];
                $_SESSION['user_name'] = $user_name;
            }
            
            // Get parent's children
            $children = $this->parentModel->getParentChildren($user_id);
            error_log("Children loaded: " . count($children));
            
            // Get payment records for all children
            $payments = $this->parentModel->getChildrenPayments($user_id);
            error_log("Payments loaded: " . count($payments));
            
            // Get parent statistics
            $stats = $this->parentModel->getParentStats($user_id);
            error_log("Stats loaded: " . json_encode($stats));
            
            // Get recent notifications
            $notifications = $this->parentModel->getParentNotifications($user_id, 10);
            error_log("Notifications loaded: " . count($notifications));
            
        } catch (Exception $e) {
            error_log("CRITICAL ERROR in parent dashboard: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // Don't let the error crash the page, use default values
        }
        
        $data = [
            'page_title' => 'Parent Dashboard - VAL Edu',
            'user_logged_in' => true,
            'user_name' => $user_name,
            'username' => $username,
            'user_role' => 'parent',
            'parent_data' => $parentData,
            'children' => $children,
            'payments' => $payments,
            'stats' => $stats,
            'notifications' => $notifications,
            'connection_requests' => $connection_requests
        ];
        
        // Debug logging
        error_log("Data passed to parent view - user_name: " . ($data['user_name'] ?? 'NULL'));
        error_log("Children count: " . count($children));
        error_log("Final stats: " . json_encode($stats));
        
        try {
            // Render the Parent view
            $this->renderView('Parent/Parent', $data);
        } catch (Exception $e) {
            error_log("ERROR rendering parent view: " . $e->getMessage());
            // Fallback error page
            echo "<h1>Dashboard Error</h1>";
            echo "<p>Sorry, there was an error loading your dashboard. Please try again later.</p>";
            echo "<p><a href='/webapp/logout'>Logout</a></p>";
        }
    }
    
    // API endpoints for parent dashboard
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            
            $data = [
                'full_name' => $_POST['fullname'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? ''
            ];
            
            $result = $this->parentModel->updateParentProfile($user_id, $data);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Cập nhật thông tin thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật thông tin!']);
            }
            exit();
        }
    }
    
    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $new_password = $_POST['new_password'] ?? '';
            
            $result = $this->parentModel->changePassword($user_id, $new_password);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi đổi mật khẩu!']);
            }
            exit();
        }
    }
    
    public function getChildAttendance() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $child_id = $_GET['child_id'] ?? '';
            $class_id = $_GET['class_id'] ?? '';
            
            if ($child_id) {
                $attendance = $this->parentModel->getChildAttendance($child_id, $class_id);
                echo json_encode(['success' => true, 'data' => $attendance]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Thiếu thông tin học sinh']);
            }
            exit();
        }
    }
    
    public function getPaymentHistory() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $child_id = $_GET['child_id'] ?? '';
            
            if ($child_id) {
                $payments = $this->parentModel->getChildPaymentHistory($child_id);
                echo json_encode(['success' => true, 'data' => $payments]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Thiếu thông tin học sinh']);
            }
            exit();
        }
    }
    
    public function getChildDetails() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'parent') {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit();
            }

            $childId = intval($_GET['child_id'] ?? 0);
            $parentId = $_SESSION['user_id'];
            
            if (!$childId) {
                throw new Exception("Child ID is required");
            }

            $child = $this->parentModel->getChildDetails($childId, $parentId);
            
            if (!$child) {
                throw new Exception("Child not found or access denied");
            }
            
            echo json_encode([
                'success' => true,
                'child' => $child
            ]);

        } catch (Exception $e) {
            error_log("Error getting child details: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }

    public function getChildAttendanceByClass() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'parent') {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit();
            }

            $childId = intval($_GET['child_id'] ?? 0);
            $classId = intval($_GET['class_id'] ?? 0);
            $parentId = $_SESSION['user_id'];
            
            if (!$childId) {
                throw new Exception("Child ID is required");
            }
            
            // Verify parent has access to this child
            $child = $this->parentModel->getChildDetails($childId, $parentId);
            if (!$child) {
                throw new Exception("Access denied");
            }

            if ($classId) {
                $attendance = $this->parentModel->getChildAttendanceByClass($childId, $classId);
            } else {
                $attendance = $this->parentModel->getChildAttendanceHistory($childId);
            }
            
            echo json_encode([
                'success' => true,
                'attendance' => $attendance
            ]);

        } catch (Exception $e) {
            error_log("Error getting child attendance: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }
}
?>