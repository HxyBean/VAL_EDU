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
        
        // Get parent-specific data from database
        $parentData = null;
        $children = [];
        $payments = [];
        $stats = [];
        $notifications = [];
        
        try {
            // Get parent profile information
            $parentData = $this->parentModel->getParentById($user_id);
            
            // If we have parent data from DB and no user_name in session, use it
            if ($parentData && empty($user_name)) {
                $user_name = $parentData['full_name'];
                $_SESSION['user_name'] = $user_name;
            }
            
            // Get parent's children
            $children = $this->parentModel->getParentChildren($user_id);
            
            // Get payment records for all children
            $payments = $this->parentModel->getChildrenPayments($user_id);
            
            // Get parent statistics
            $stats = $this->parentModel->getParentStats($user_id);
            
            // Get recent notifications
            $notifications = $this->parentModel->getParentNotifications($user_id, 10);
            
        } catch (Exception $e) {
            error_log("Error getting parent data: " . $e->getMessage());
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
            'notifications' => $notifications
        ];
        
        // Debug logging
        error_log("Data passed to parent view - user_name: " . ($data['user_name'] ?? 'NULL'));
        
        // Render the Parent view
        $this->renderView('Parent/Parent', $data);
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
                $payments = $this->parentModel->getChildPayments($child_id);
                echo json_encode(['success' => true, 'data' => $payments]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Thiếu thông tin học sinh']);
            }
            exit();
        }
    }
}
?>