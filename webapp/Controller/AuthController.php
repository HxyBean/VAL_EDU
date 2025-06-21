<?php
require_once(__DIR__ . '/../Base/BaseController.php');
require_once(__DIR__ . '/../Model/UserModel.php');

class AuthController extends BaseController {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new UserModel();
    }
    
    public function showLogin($error = null) {
        // If user is already logged in, redirect based on role
        if (isset($_SESSION['user_id'])) {
            $this->redirectToDashboard($_SESSION['user_role']);
        }
        
        $data = [
            'page_title' => 'Đăng nhập - VAL Edu',
            'user_logged_in' => false,
            'error_message' => $error
        ];
        
        $this->renderView('Login', $data);
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/webapp/login');
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Basic validation
        if (empty($username)) {
            $this->showLogin('Please enter your username or email address.');
            return;
        }
        
        if (empty($password)) {
            $this->showLogin('Please enter your password.');
            return;
        }
        
        if (strlen($password) < 6) {
            $this->showLogin('Password must be at least 6 characters long.');
            return;
        }

        try {
            // Authenticate user
            $result = $this->userModel->authenticate($username, $password);
            
            if ($result['code'] === 0) {
                $user = $result['data'];
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_email'] = $user['email'];
                
                // Update last login
                $this->userModel->updateLastLogin($user['id']);
                
                // Redirect based on role
                $this->redirectToDashboard($user['role']);
            } else {
                // Show specific error message from UserModel
                $this->showLogin($result['error']);
            }
        } catch (Exception $e) {
            // Log error for debugging (in production, log to file)
            error_log("Login error: " . $e->getMessage());
            $this->showLogin('A system error occurred. Please try again later.');
        }
    }
    
    public function showRegister($error = null, $oldData = []) {
        // If user is already logged in, redirect to dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirectToDashboard($_SESSION['user_role']);
        }
        
        $data = [
            'page_title' => 'Đăng ký - VAL Edu',
            'user_logged_in' => false,
            'error_message' => $error,
            'old_data' => $oldData
        ];
        
        $this->renderView('Register', $data);
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/webapp/register');
            return;
        }

        // Get form data
        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $role = $_POST['role'] ?? '';
        $birthdate = $_POST['birthdate'] ?? null;
        
        // Store old data for repopulating form on error
        $oldData = [
            'fullname' => $fullname,
            'email' => $email,
            'username' => $username,
            'phone' => $phone,
            'role' => $role,
            'birthdate' => $birthdate
        ];
        
        // Detailed validation
        if (empty($fullname)) {
            $this->showRegister('Full name is required.', $oldData);
            return;
        }
        
        if (strlen($fullname) < 2) {
            $this->showRegister('Full name must be at least 2 characters long.', $oldData);
            return;
        }
        
        if (empty($email)) {
            $this->showRegister('Email address is required.', $oldData);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->showRegister('Please enter a valid email address.', $oldData);
            return;
        }
        
        if (empty($username)) {
            $this->showRegister('Username is required.', $oldData);
            return;
        }
        
        if (strlen($username) < 3) {
            $this->showRegister('Username must be at least 3 characters long.', $oldData);
            return;
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $this->showRegister('Username can only contain letters, numbers, and underscores.', $oldData);
            return;
        }
        
        if (empty($password)) {
            $this->showRegister('Password is required.', $oldData);
            return;
        }
        
        if (strlen($password) < 6) {
            $this->showRegister('Password must be at least 6 characters long.', $oldData);
            return;
        }
        
        if (empty($role)) {
            $this->showRegister('Please select your role.', $oldData);
            return;
        }
        
        if (!in_array($role, ['student', 'parent'])) {
            $this->showRegister('Please select a valid role.', $oldData);
            return;
        }
        
        if ($role === 'student' && empty($birthdate)) {
            $this->showRegister('Birthdate is required for students.', $oldData);
            return;
        }
        
        if (!empty($phone) && !preg_match('/^[0-9+\-\s()]{10,15}$/', $phone)) {
            $this->showRegister('Please enter a valid phone number.', $oldData);
            return;
        }
        
        // Prepare user data
        $userData = [
            'full_name' => $fullname,
            'email' => $email,
            'username' => $username,
            'password' => $password,
            'role' => $role,
            'phone' => $phone,
            'birthdate' => $role === 'student' ? $birthdate : null
        ];
        
        try {
            // Create user
            $result = $this->userModel->createUser($userData);
            
            if ($result['code'] === 0) {
                $user = $result['data'][0];
                
                // Auto-login the user
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_email'] = $user['email'];
                
                // Redirect to main page with success message
                $_SESSION['success_message'] = 'Registration successful! Welcome to VAL Edu.';
                $this->redirect('/webapp/');
            } else {
                // Show specific error message from UserModel
                $this->showRegister($result['error'], $oldData);
            }
        } catch (Exception $e) {
            // Log error for debugging
            error_log("Registration error: " . $e->getMessage());
            $this->showRegister('A system error occurred. Please try again later.', $oldData);
        }
    }
    
    public function logout() {
        // Clear all session data
        $_SESSION = array();
        
        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-42000, '/');
        }
        
        // Destroy session
        session_destroy();
        
        // Redirect with message
        header('Location: /webapp/?message=logged_out');
        exit();
    }
    
    public function showForgotPassword($error = null, $success = null) {
        $data = [
            'page_title' => 'Quên mật khẩu - VAL Edu',
            'user_logged_in' => false,
            'error_message' => $error,
            'success_message' => $success
        ];
        
        $this->renderView('F_pswd', $data);
    }
    
    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/webapp/forgot-password');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            $this->showForgotPassword('Please enter your email address.');
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->showForgotPassword('Please enter a valid email address.');
            return;
        }
        
        try {
            // Check if email exists
            $result = $this->userModel->getUserByEmail($email);
            
            if ($result['code'] === 0 && !empty($result['data'])) {
                // In a real application, you would send a password reset email here
                // For now, just show a success message
                $this->showForgotPassword(null, 'Password reset instructions have been sent to your email address. Please check your inbox.');
            } else {
                $this->showForgotPassword('No account found with that email address. Please check your email or register for a new account.');
            }
        } catch (Exception $e) {
            error_log("Forgot password error: " . $e->getMessage());
            $this->showForgotPassword('A system error occurred. Please try again later.');
        }
    }
    
    // Helper method to redirect based on user role
    private function redirectToDashboard($role) {
        switch ($role) {
            case 'admin':
                $this->redirect('/webapp/admin/dashboard');
                break;
            case 'tutor':
                $this->redirect('/webapp/tutor/dashboard');
                break;
            case 'student':
                $this->redirect('/webapp/student/dashboard');
                break;
            case 'parent':
                $this->redirect('/webapp/parent/dashboard');
                break;
            default:
                $this->redirect('/webapp/');
                break;
        }
    }
}
?>