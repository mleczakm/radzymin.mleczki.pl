<?php

declare(strict_types=1);

namespace App\Http;

use Swoole\Http\Request;
use Swoole\Http\Response;

final class Router
{
    /** @var list<array{0: string, 1: string, 2: callable}> */
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, callable $handler): void
    {
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $pattern) . '$#';
        $this->routes[] = [$method, $regex, $handler];
    }

    public function dispatch(Request $request, Response $response): void
    {
        $method = $request->server['request_method'] ?? 'GET';
        $path = rtrim(parse_url($request->server['request_uri'] ?? '/', PHP_URL_PATH) ?: '/', '/');
        $path = $path === '' ? '/' : $path;

        foreach ($this->routes as [$routeMethod, $regex, $handler]) {
            if ($routeMethod !== $method || !preg_match($regex, $path, $matches)) {
                continue;
            }

            $params = array_filter($matches, static fn (int|string $key): bool => is_string($key), ARRAY_FILTER_USE_KEY);
            $handler($request, $response, $params);

            return;
        }

        $response->status(404);
        $response->end('Nie znaleziono strony.');
    }
}
