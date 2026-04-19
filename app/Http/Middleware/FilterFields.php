<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FilterFields
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only process if ?fields= is present and response is JSON
        if (!$request->has('fields')) {
            return $response;
        }

        if (!$response->headers->contains('Content-Type', 'application/json')) {
            return $response;
        }

        $fields  = array_map('trim', explode(',', $request->query('fields')));
        $content = json_decode($response->getContent(), true);

        if (is_array($content)) {
            $content = $this->filterFields($content, $fields);
            $response->setContent(json_encode($content));
        }

        return $response;
    }

    protected function filterFields(array $data, array $fields): array
    {
        // Handle paginated responses with 'data' key
        if (isset($data['data']) && is_array($data['data'])) {
            $data['data'] = array_map(
                fn($item) => is_array($item) ? array_intersect_key($item, array_flip($fields)) : $item,
                $data['data']
            );
            return $data;
        }

        // Handle single resource or flat array
        if (isset($data[0]) && is_array($data[0])) {
            return array_map(
                fn($item) => array_intersect_key($item, array_flip($fields)),
                $data
            );
        }

        return array_intersect_key($data, array_flip($fields));
    }
}