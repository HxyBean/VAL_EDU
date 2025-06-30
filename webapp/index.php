<?php
session_start();
require_once('Base/BaseController.php');
require_once('Base/Database.php');
require_once('Base/BaseModel.php');

require_once('Controller/HomeController.php');
require_once('Controller/AuthController.php');

// Simple routing
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$path = str_replace('/webapp', '', $path);

// Handle API routes FIRST (before other routes)
if (strpos($path, '/api/') === 0) {
    $apiPath = substr($path, 4); // Remove '/api' prefix
    error_log("API Route called: " . $apiPath);
    error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
    
    switch ($apiPath) {
        case '/student/update-profile':
            if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'student') {
                require_once('Controller/StudentController.php');
                $controller = new StudentController();
                $controller->updateProfile();
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            }
            exit();
            
        case '/student/change-password':
            if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'student') {
                require_once('Controller/StudentController.php');
                $controller = new StudentController();
                $controller->changePassword();
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            }
            exit();
            
        case '/tutor/update-profile':
            if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'tutor') {
                require_once('Controller/TutorController.php');
                $controller = new TutorController();
                $controller->updateProfile();
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            }
            exit();
            
        case '/tutor/change-password':
            if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'tutor') {
                require_once('Controller/TutorController.php');
                $controller = new TutorController();
                $controller->changePassword();
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            }
            exit();
            
        case '/tutor/get-students':
            if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'tutor') {
                require_once('Controller/TutorController.php');
                $controller = new TutorController();
                $controller->getStudentsInClass();
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            }
            exit();
            
        case '/tutor/save-attendance':
            if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'tutor') {
                require_once('Controller/TutorController.php');
                $controller = new TutorController();
                $controller->saveAttendance();
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            }
            exit();
            
        case '/tutor/get-attendance-history':
            if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'tutor') {
                require_once('Controller/TutorController.php');
                $controller = new TutorController();
                $controller->getAttendanceHistory();
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            }
            exit();
            
        case '/tutor/get-student-list':
            if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'tutor') {
                require_once('Controller/TutorController.php');
                $controller = new TutorController();
                $controller->getStudentList();
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            }
            exit();
            
        case '/admin/update-profile':
            if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
                require_once('Controller/AdminController.php');
                $controller = new AdminController();
                $controller->updateProfile();
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            }
            exit();
            
        case '/admin/change-password':
            if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
                require_once('Controller/AdminController.php');
                $controller = new AdminController();
                $controller->changePassword();
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            }
            exit();
            
        case '/admin/chart-data':
            if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
                require_once('Controller/AdminController.php');
                $controller = new AdminController();
                $controller->getChartData();
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            }
            exit();
            
        // Admin course management APIs
        case '/admin/create-course':
            if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
                require_once('Controller/AdminController.php');
                $controller = new AdminController();
                $controller->createCourse();
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            }
            exit();
            
        case '/admin/get-courses':
            error_log("Admin get-courses API called");
            if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
                require_once('Controller/AdminController.php');
                $controller = new AdminController();
                $controller->getCourses();
            } else {
                error_log("Unauthorized access to get-courses");
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            }
            exit();
            
        case '/admin/get-tutors':
            error_log("Admin get-tutors API called");
            if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
                require_once('Controller/AdminController.php');
                $controller = new AdminController();
                $controller->getTutors();
            } else {
                error_log("Unauthorized access to get-tutors");
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            }
            exit();
            
        case '/admin/close-course':
            if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
                require_once('Controller/AdminController.php');
                $controller = new AdminController();
                $controller->closeCourse();
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            }
            exit();
            
        default:
            error_log("API endpoint not found: " . $apiPath);
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'API endpoint not found: ' . $apiPath]);
            exit();
    }
}

// Regular routes
switch ($path) {
    case '/':
    case '/home':
    case '/index.php':
        $controller = new HomeController();
        $controller->index();
        break;
    
    case '/login':
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->login();
        } else {
            $controller->showLogin();
        }
        break;
        
    case '/register':
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->register();
        } else {
            $controller->showRegister();
        }
        break;
        
    case '/forgot-password':
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->forgotPassword();
        } else {
            $controller->showForgotPassword();
        }
        break;
        
    case '/logout':
        $controller = new AuthController();
        $controller->logout();
        break;
        
    // Dynamic username-based dashboard route
    default:
        // Check if this is a username route pattern (/{username})
        if (preg_match('/^\/([a-zA-Z0-9_-]+)$/', $path, $matches)) {
            $username = $matches[1];
            
            // Verify user is logged in and this is their username
            if (isset($_SESSION['user_id']) && isset($_SESSION['username']) && $_SESSION['username'] === $username) {
                // Route to appropriate dashboard based on user role
                switch ($_SESSION['user_role']) {
                    case 'admin':
                        require_once('Controller/AdminController.php');
                        $controller = new AdminController();
                        $controller->dashboard();
                        break;
                    case 'tutor':
                        require_once('Controller/TutorController.php');
                        $controller = new TutorController();
                        $controller->dashboard();
                        break;
                    case 'student':
                        require_once('Controller/StudentController.php');
                        $controller = new StudentController();
                        $controller->dashboard();
                        break;
                    case 'parent':
                        require_once('Controller/ParentController.php');
                        $controller = new ParentController();
                        $controller->dashboard();
                        break;
                    default:
                        header('Location: /webapp/');
                        exit();
                }
            } else {
                // User not logged in or trying to access someone else's dashboard
                header('Location: /webapp/login');
                exit();
            }
        } else {
            // Handle old role-based routes (for backward compatibility)
            if (strpos($path, '/admin/dashboard') !== false) {
                if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
                    header('Location: /webapp/' . urlencode($_SESSION['username']));
                    exit();
                } else {
                    header('Location: /webapp/login');
                    exit();
                }
            } elseif (strpos($path, '/tutor/dashboard') !== false) {
                if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'tutor') {
                    header('Location: /webapp/' . urlencode($_SESSION['username']));
                    exit();
                } else {
                    header('Location: /webapp/login');
                    exit();
                }
            } elseif (strpos($path, '/student/dashboard') !== false) {
                if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'student') {
                    header('Location: /webapp/' . urlencode($_SESSION['username']));
                    exit();
                } else {
                    header('Location: /webapp/login');
                    exit();
                }
            } elseif (strpos($path, '/parent/dashboard') !== false) {
                if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'parent') {
                    header('Location: /webapp/' . urlencode($_SESSION['username']));
                    exit();
                } else {
                    header('Location: /webapp/login');
                    exit();
                }
            } else {
                // 404 for unrecognized routes
                http_response_code(404);
                echo "Page not found: " . $path;
            }
        }
        break;
}
?>