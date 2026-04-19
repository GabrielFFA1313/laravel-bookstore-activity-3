<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CamelCaseResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only transform JSON responses
        if (!$response->headers->contains('Content-Type', 'application/json')) {
            return $response;
        }

        $content = json_decode($response->getContent(), true);

        if (is_array($content)) {
            $content = $this->convertKeysToCamelCase($content);
            $response->setContent(json_encode($content));
        }

        return $response;
    }

    protected function convertKeysToCamelCase(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $camelKey = Str::camel($key);

            if (is_array($value)) {
                $result[$camelKey] = $this->convertKeysToCamelCase($value);
            } else {
                $result[$camelKey] = $value;
            }
        }

        return $result;
    }
}