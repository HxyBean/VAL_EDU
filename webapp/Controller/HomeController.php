<?php
require_once(__DIR__ . '/../Base/BaseController.php');
require_once(__DIR__ . '/../Model/HomeModel.php');

class HomeController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $user_logged_in = isset($_SESSION['user_id']);
        $user_name = $_SESSION['user_name'] ?? '';
        $user_role = $_SESSION['user_role'] ?? '';
        
        // Check for success messages
        $success_message = $_SESSION['success_message'] ?? null;
        if ($success_message) {
            unset($_SESSION['success_message']); // Clear message after displaying
        }
        
        $data = [
            'page_title' => 'VAL Edu - English Center For Everyone',
            'user_logged_in' => $user_logged_in,
            'user_name' => $user_name,
            'user_role' => $user_role,
            'success_message' => $success_message
        ];
        
        // Render the Home view from Home folder
        $this->renderView('Home/Home', $data);
    }
}