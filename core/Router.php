<?php

class Router {
    private $routes = [];
    
    public function addRoute($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestPath = $this->getPath();
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $this->matchPath($route['path'], $requestPath)) {
                $controllerName = $route['controller'];
                $actionName = $route['action'];
                
                // Include controller file
                $controllerFile = "controllers/{$controllerName}.php";
                if (file_exists($controllerFile)) {
                    require_once $controllerFile;
                    
                    $controller = new $controllerName();
                    if (method_exists($controller, $actionName)) {
                        $controller->$actionName();
                        return;
                    }
                }
            }
        }
        
        // 404 Not Found
        http_response_code(404);
        require_once 'views/404.php';
    }
    
    private function getPath() {
        $path = $_SERVER['REQUEST_URI'];
        $path = parse_url($path, PHP_URL_PATH);
        return rtrim($path, '/') ?: '/';
    }
    
    private function matchPath($routePath, $requestPath) {
        // Convert route path to regex pattern
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        return preg_match($pattern, $requestPath);
    }
    
    public function redirect($url) {
        header("Location: $url");
        exit();
    }
}