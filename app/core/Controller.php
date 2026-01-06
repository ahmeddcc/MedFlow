<?php
/**
 * Base Controller Class
 */
class Controller {
    
    protected function view(string $view, array $data = []): void {
        // Extract data to variables
        extract($data);
        
        // Define the view path
        $viewPath = VIEWS_PATH . str_replace('.', '/', $view) . '.php';
        
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            throw new Exception("View not found: $view");
        }
    }
    
    protected function checkPermission(array $roles): void {
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $roles)) {
            redirect('login');
            exit;
        }
    }
}
