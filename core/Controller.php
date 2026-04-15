<?php
/**
 * Base Controller Class
 * Lớp cơ sở cho tất cả controllers
 */

class Controller
{
    protected string $viewPath = '/app/Views/';

    /**
     * Thực hiện render view
     */
    protected function view(string $viewName, array $data = []): void
    {
        $viewFile = dirname(dirname(__FILE__)) . '/app/Views/' . str_replace('.', '/', $viewName) . '.php';

        if (!file_exists($viewFile)) {
            throw new Exception("View not found: {$viewFile}");
        }

        // Extract data thành biến
        extract($data);

        // Output HTML
        header('Content-Type: text/html; charset=utf-8');
        require_once $viewFile;
    }

    /**
     * Trả về JSON response
     */
    protected function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Trả về success response
     */
    protected function success(string $message, mixed $data = null, int $statusCode = 200): void
    {
        $this->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Trả về error response
     */
    protected function error(string $message, mixed $data = null, int $statusCode = 400): void
    {
        $this->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Lấy input từ request ($_GET, $_POST)
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        return $_REQUEST[$key] ?? $default;
    }

    /**
     * Lấy input post
     */
    protected function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Lấy input query
     */
    protected function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Kiểm tra request method
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function isPut(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'PUT';
    }

    protected function isDelete(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'DELETE';
    }

    /**
     * Lấy JSON body từ request
     */
    protected function getJsonBody(): array
    {
        $json = file_get_contents('php://input');
        return json_decode($json, true) ?? [];
    }

    /**
     * Redirect đến URL
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Lấy session value
     */
    protected function session(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set session value
     */
    protected function setSession(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Xóa session value
     */
    protected function unsetSession(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Authorize - Kiểm tra quyền truy cập
     */
    protected function authorize(bool $condition, string $message = 'Unauthorized'): void
    {
        if (!$condition) {
            $this->error($message, null, 403);
        }
    }

    /**
     * Authenticate - Kiểm tra xác thực
     */
    protected function authenticate(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->error('Unauthorized - Please login first', null, 401);
        }
    }

    /**
     * Validate input
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $rules_array = explode('|', $fieldRules);

            foreach ($rules_array as $rule) {
                $this->validateRule($field, $data[$field] ?? null, $rule, $errors);
            }
        }

        return $errors;
    }

    /**
     * Validate rule helper
     */
    private function validateRule(string $field, mixed $value, string $rule, array &$errors): void
    {
        list($ruleName, $param) = array_pad(explode(':', $rule), 2, null);

        switch ($ruleName) {
            case 'required':
                if (empty($value)) {
                    $errors[$field] = "{$field} không được để trống";
                }
                break;

            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "{$field} không phải email hợp lệ";
                }
                break;

            case 'min':
                if (strlen((string)$value) < (int)$param) {
                    $errors[$field] = "{$field} phải có tối thiểu {$param} ký tự";
                }
                break;

            case 'max':
                if (strlen((string)$value) > (int)$param) {
                    $errors[$field] = "{$field} không được vượt quá {$param} ký tự";
                }
                break;

            case 'numeric':
                if (!is_numeric($value)) {
                    $errors[$field] = "{$field} phải là số";
                }
                break;
        }
    }
}
