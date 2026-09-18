<?php

declare(strict_types=1);

namespace App\Http;

use Swoole\Http\Request;

/**
 * Narrows Swoole's loosely typed request data (headers, server vars and POST fields are
 * `mixed`) to what the application actually expects, in one place.
 */
final class RequestInput
{
    /** Header value by name (case-insensitive), or '' when absent. */
    public static function header(Request $request, string $name): string
    {
        $value = $request->header[strtolower($name)] ?? '';

        return is_string($value) ? $value : '';
    }

    /** Server variable (request_method, request_uri, remote_addr, ...), or '' when absent. */
    public static function server(Request $request, string $key): string
    {
        $value = $request->server[strtolower($key)] ?? '';

        return is_string($value) ? $value : '';
    }

    /**
     * Raw POST fields, including nested ones (`field[]=x`).
     *
     * @return array<array-key, mixed>
     */
    public static function post(Request $request): array
    {
        return is_array($request->post) ? $request->post : [];
    }

    /**
     * Plain text fields only: nested values (`first_name[]=x`) are dropped instead of being
     * turned into the string "Array" by a cast.
     *
     * @param array<array-key, mixed> $post
     * @return array<string, string>
     */
    public static function stringFields(array $post): array
    {
        $fields = [];
        foreach ($post as $key => $value) {
            if (!is_string($key) || !is_string($value)) {
                continue;
            }

            $fields[$key] = $value;
        }

        return $fields;
    }
}
