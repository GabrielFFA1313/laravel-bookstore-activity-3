<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ETagCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only apply to GET requests with JSON responses
        if ($request->method() !== 'GET') {
            return $response;
        }

        $content = $response->getContent();
        $etag    = '"' . md5($content) . '"';

        $response->headers->set('ETag', $etag);
        $response->headers->set('Cache-Control', 'private, must-revalidate');

        // If client sent If-None-Match and it matches, return 304
        $ifNoneMatch = $request->header('If-None-Match');
        if ($ifNoneMatch && $ifNoneMatch === $etag) {
            return response('', 304, [
                'ETag'          => $etag,
                'Cache-Control' => 'private, must-revalidate',
            ]);
        }

        return $response;
    }
}