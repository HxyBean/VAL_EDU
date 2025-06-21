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
// example: if the request is '/webapp/home', $path will be '/home'
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
        
    // Role-based dashboard routes (for future implementation)
    case '/admin/dashboard':
        // Will be implemented with AdminController
        if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
            echo "<h1>Admin Dashboard</h1><p>Welcome, " . htmlspecialchars($_SESSION['user_name']) . "!</p>";
            echo "<a href='/webapp/logout'>Logout</a> | <a href='/webapp/'>Home</a>";
        } else {
            header('Location: /webapp/login');
            exit();
        }
        break;
        
    case '/tutor/dashboard':
        // Will be implemented with TutorController
        if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'tutor') {
            echo "<h1>Tutor Dashboard</h1><p>Welcome, " . htmlspecialchars($_SESSION['user_name']) . "!</p>";
            echo "<a href='/webapp/logout'>Logout</a> | <a href='/webapp/'>Home</a>";
        } else {
            header('Location: /webapp/login');
            exit();
        }
        break;
        
    case '/student/dashboard':
        // Will be implemented with StudentController
        if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'student') {
            echo "<h1>Student Dashboard</h1><p>Welcome, " . htmlspecialchars($_SESSION['user_name']) . "!</p>";
            echo "<a href='/webapp/logout'>Logout</a> | <a href='/webapp/'>Home</a>";
        } else {
            header('Location: /webapp/login');
            exit();
        }
        break;
        
    case '/parent/dashboard':
        // Will be implemented with ParentController
        if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'parent') {
            echo "<h1>Parent Dashboard</h1><p>Welcome, " . htmlspecialchars($_SESSION['user_name']) . "!</p>";
            echo "<a href='/webapp/logout'>Logout</a> | <a href='/webapp/'>Home</a>";
        } else {
            header('Location: /webapp/login');
            exit();
        }
        break;
        
    default:
        http_response_code(404);
        echo "Page not found: " . $path;
        break;
}