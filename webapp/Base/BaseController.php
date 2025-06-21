<?php
    class BaseController {
        protected $viewPath;

        public function __construct() {
            // Set the base view directory
            $this->viewPath = __DIR__ . '/../View/';
            //__DIR__ gives the directory of the current file, so after/../ we go up one level then to Base/View/
        }

        public function index() {
            echo "Index is working on " . get_called_class();
        }

        public function renderView($viewName, $data = []) {
            // Extract data array to variables so they can be used in the view ( into php variables )
            if (!empty($data)) {
                extract($data);
            }
            /*
            // Get controller name and convert to view folder
            $viewFolder = get_called_class(); // get the name of the current class
            $viewFolder = str_replace('Controller', '', $viewFolder); // Remove 'Controller' suffix
            $viewFolder = ucfirst($viewFolder); // Ensure first letter is uppercase
            // Example: If the controller is 'UserController', the view folder will be 'User'

            // Build full view path with .php extension
            // Example: If the view is 'profile', the path will be 'View/User(get from above)/profile.php'
            $viewPath = $this->viewPath . $viewFolder . '/' . $viewName . '.php';
            
            // Check if view file exists
            if (file_exists($viewPath)) {
                include $viewPath;
            } else {
                throw new Exception("View file not found: $viewPath");
            }*/
            
        
            // Try multiple view locations
            $possiblePaths = [
            $this->viewPath . 'Auth/' . $viewName . '.php',      // For AuthController
            $this->viewPath . 'Home/' . $viewName . '.php',      // For HomeController
            $this->viewPath . 'Admin/' . $viewName . '.php',     // For AdminController
            $this->viewPath . 'Student/' . $viewName . '.php',   // For StudentController
            $this->viewPath . 'Tutor/' . $viewName . '.php',     // For TutorController
            $this->viewPath . 'Parent/' . $viewName . '.php',    // For ParentController
            ];
        
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    include $path;
                    return;
                }
            }
                // If no view found, show error
                echo "View not found: " . $viewName . ". Searched paths: " . implode(', ', $possiblePaths); 
        }
        // Include partial views (like header, footer)
        public function includePartial($partialName, $data = []) {
            if (!empty($data)) {
                extract($data);
            }
            
            $partialPath = $this->viewPath . 'Partial/' . $partialName . '.php';
            // Example: If the partial is 'HomeHeader', the path will be 'View/Partial/HomeHeader.php'
            if (file_exists($partialPath)) {
                include $partialPath;
            } else {
                echo "<!-- Partial not found: $partialPath -->";
            }
        }

        // Render with layout
        public function renderWithLayout($layoutName, $contentView, $data = []) {
            if (!empty($data)) {
                extract($data);
            }
            
            // Start output buffering to capture content
            ob_start(); // start output buffering ( capture the output )
            $this->renderView($contentView, $data); // render the content view and capture its output
            $content = ob_get_clean(); // get the content from the buffer and clean it ( remove it from the buffer )
            // Example: If the content view is 'home', the content will be captured from 'View/User/home.php'

            // Now render layout with content
            $layoutPath = $this->viewPath . 'Layout/' . $layoutName . '.php';
            // Example: If the layout is 'main', the path will be 'View/Layout/main.php'

            if (file_exists($layoutPath)) {
                include $layoutPath;
            } else {
                echo $content; // Fallback to just content
            }
        }

        // Helper methods
        public function redirect($url) {
            header("Location: $url"); // Redirect to the specified URL
            exit();
        }

        public function jsonResponse($data, $status = 200) {
            http_response_code($status); // Set the HTTP response status code
            header('Content-Type: application/json'); // Set the content type to JSON
            echo json_encode($data); // Encode data to JSON format and output it
            exit();
        }
    }
?>