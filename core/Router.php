<?php
/**
 * Router - Xử lý routing cho ứng dụng
 * Hỗ trợ GET, POST, PUT, DELETE
 */

class Router
{
    private array $routes = [];

    /**
     * Đăng ký route GET
     */
    public function get(string $path, string|callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Đăng ký route POST
     */
    public function post(string $path, string|callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Đăng ký route PUT
     */
    public function put(string $path, string|callable $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    /**
     * Đăng ký route DELETE
     */
    public function delete(string $path, string|callable $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Thêm route vào danh sách
     */
    private function addRoute(string $method, string $path, string|callable $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'pattern' => $this->pathToRegex($path),
        ];
    }

    /**
     * Chuyển đổi path thành regex pattern
     * /rooms/:id -> /rooms/(?P<id>[a-zA-Z0-9_-]+)
     * /users/:name -> /users/(?P<name>[a-zA-Z0-9_-]+)
     */
    private function pathToRegex(string $path): string
    {
        $pattern = preg_replace('/:(\/w+)/', '(?P<$1>[a-zA-Z0-9_-]+)', $path);
        return '#^' . $pattern . '$#i';  // Return regex with delimiters
    }

    /**
     * Xử lý request và gọi controller
     * 
     * @param string $method HTTP method
     * @param string $requestPath Request path
     */
    public function dispatch(string $method, string $requestPath): void
    {
        // Loại bỏ query string nếu có
        $requestPath = parse_url($requestPath, PHP_URL_PATH);
        
        // Loại bỏ prefix /RoomFinderSystem/public/ nếu có
        if (strpos($requestPath, '/RoomFinderSystem/public/') === 0) {
            $requestPath = substr($requestPath, strlen('/RoomFinderSystem/public/'));
        }

        // Loại bỏ trailing slash
        $requestPath = rtrim($requestPath, '/') ?: '/';

        // Tìm route khớp
        foreach ($this->routes as $route) {
            if (!preg_match($route['pattern'], $requestPath, $matches)) {
                continue;
            }

            if ($route['method'] !== $method) {
                continue;
            }

            // Tìm thấy route khớp
            $this->callHandler($route['handler'], $matches);
            return;
        }

        // Không tìm thấy route
        throw new Exception("Route not found: {$method} {$requestPath}");
    }

    /**
     * Gọi handler (callable hoặc controller method)
     * 
     * @param string|callable $handler Format: ControllerName@methodName hoặc closure function
     * @param array $params Parameters từ URL
     */
    private function callHandler(string|callable $handler, array $params): void
    {
        // Nếu handler là callable (closure/function)
        if (is_callable($handler)) {
            call_user_func($handler, $params);
            return;
        }

        // Parse handler string (ControllerName@methodName)
        if (!str_contains($handler, '@')) {
            throw new Exception("Invalid handler format: {$handler}. Expected: ControllerName@methodName");
        }

        list($controllerName, $methodName) = explode('@', $handler);

        // Tạo namespace
        $controllerClass = "App\\Controllers\\{$controllerName}";

        // Kiểm tra class có tồn tại không
        if (!class_exists($controllerClass)) {
            throw new Exception("Controller not found: {$controllerClass}");
        }

        // Khởi tạo controller
        $controller = new $controllerClass();

        // Kiểm tra method có tồn tại không
        if (!method_exists($controller, $methodName)) {
            throw new Exception("Method not found: {$controllerClass}@{$methodName}");
        }

        // Lọc parameters (bỏ các key số từ regex)
        $routeParams = array_filter(
            $params,
            fn($key) => is_string($key),
            ARRAY_FILTER_USE_KEY
        );

        // Gọi method
        $controller->$methodName(...array_values($routeParams));
    }

    /**
     * Lấy tất cả routes (dùng debug)
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
