<?php
declare(strict_types=1);

final class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->map('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->map('POST', $path, $handler);
    }

    private function map(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [$method, rtrim($path, '/') ?: '/', $handler];
    }

    public function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $rawUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        // Strip subfolder prefix if hosted under a subdirectory (e.g. /sis_calif/)
        $scriptName = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($scriptName !== '/' && $scriptName !== '\\' && str_starts_with($rawUri, $scriptName)) {
            $rawUri = substr($rawUri, strlen($scriptName));
        }

        $uri = rtrim($rawUri, '/') ?: '/';

        foreach ($this->routes as [$m, $path, $handler]) {
            if ($m === $method && $path === $uri) {
                call_user_func($handler);
                return;
            }
        }
        http_response_code(404);
        view_plain('errors/404');
    }
}
