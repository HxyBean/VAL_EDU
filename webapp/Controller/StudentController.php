<?php

require_once(__DIR__ . '/../Base/BaseController.php');
require_once(__DIR__ . '/../Model/StudentModel.php');

class StudentController extends BaseController {
    public function dashboard() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
            header('Location: /webapp/login');
            exit();
        }
        $studentModel = new StudentModel();
        $studentId = $_SESSION['user_id'];
        $studentInfo = $studentModel->getStudentInfo($studentId);
        $classes = $studentModel->getStudentClasses($studentId);

        $data = [
            'student' => $studentInfo,
            'classes' => $classes
        ];
        $this->renderView('Student', $data);
    }

    public function updateInfo() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        $studentId = $_SESSION['user_id'];
        $fullName = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (!$fullName || !$email || !$phone) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!']);
            exit();
        }

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Địa chỉ email không hợp lệ!']);
            exit();
        }

        // Validate phone
        if (!preg_match('/^[0-9]{10,11}$/', str_replace(' ', '', $phone))) {
            echo json_encode(['success' => false, 'message' => 'Số điện thoại không hợp lệ!']);
            exit();
        }

        require_once(__DIR__ . '/../Model/UserModel.php');
        $userModel = new UserModel();
        $result = $userModel->updateStudentInfo($studentId, $fullName, $email, $phone);

        if ($result['code'] === 0) {
            $_SESSION['user_name'] = $fullName; // Cập nhật session
            echo json_encode(['success' => true, 'message' => 'Thông tin cá nhân đã được cập nhật thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => $result['error'] ?? 'Lỗi hệ thống!']);
        }
        exit();
    }

    public function changePassword() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        $studentId = $_SESSION['user_id'];
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';

        if (!$currentPassword || !$newPassword) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin!']);
            exit();
        }

        require_once(__DIR__ . '/../Model/UserModel.php');
        $userModel = new UserModel();
        $result = $userModel->changePassword($studentId, $currentPassword, $newPassword);

        if ($result['code'] === 0) {
            echo json_encode(['success' => true, 'message' => 'Mật khẩu đã được thay đổi thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => $result['error'] ?? 'Lỗi hệ thống!']);
        }
        exit();
    }
}
?>