<?php

declare(strict_types=1);

namespace App\Http;

use App\View\Renderer;
use Swoole\Http\Request;
use Swoole\Http\Response;

final class Router
{
    /** @var list<array{0: string, 1: string, 2: callable}> */
    private array $routes = [];

    public function __construct(private readonly Renderer $view)
    {
    }

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
        $regex = '#^' . (string) preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $pattern) . '$#';
        $this->routes[] = [$method, $regex, $handler];
    }

    public function dispatch(Request $request, Response $response): void
    {
        $method = RequestInput::server($request, 'request_method') ?: 'GET';
        $path = rtrim(parse_url(RequestInput::server($request, 'request_uri') ?: '/', PHP_URL_PATH) ?: '/', '/');
        $path = $path === '' ? '/' : $path;

        foreach ($this->routes as [$routeMethod, $regex, $handler]) {
            if ($routeMethod !== $method) {
                continue;
            }

            $matches = [];
            if (!preg_match($regex, $path, $matches)) {
                continue;
            }

            $params = array_filter($matches, static fn (int|string $key): bool => is_string($key), ARRAY_FILTER_USE_KEY);
            $handler($request, $response, $params);

            return;
        }

        Responder::html($response, $this->view->renderPage('error', [
            'message' => 'Nie znaleziono tej strony. Link może być nieaktualny. Wróć na stronę główną, aby znaleźć petycje i sprawy mieszkańców.',
        ], 'Nie znaleziono strony — Radzymińskie Petycje'), 404);
    }
}
