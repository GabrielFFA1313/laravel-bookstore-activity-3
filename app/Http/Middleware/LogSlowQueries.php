<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogSlowQueries
{
    // Log queries slower than this threshold (ms)
    private const SLOW_THRESHOLD_MS = 100;

    public function handle(Request $request, Closure $next): Response
    {
        // Only log in non-testing environments
        if (app()->environment('testing')) {
            return $next($request);
        }

        $queries = [];

        // Listen to all DB queries during this request
        DB::listen(function ($query) use (&$queries) {
            $timeMs = $query->time;

            if ($timeMs >= self::SLOW_THRESHOLD_MS) {
                $queries[] = [
                    'query'            => $query->sql,
                    'bindings'         => json_encode($query->bindings),
                    'execution_time_ms'=> $timeMs,
                    'connection'       => $query->connectionName,
                    'is_slow'          => true,
                    'threshold_ms'     => self::SLOW_THRESHOLD_MS,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }
        });

        $response = $next($request);

        // Batch insert all slow queries for this request
        if (!empty($queries)) {
            foreach ($queries as &$q) {
                $q['endpoint']        = $request->path();
                $q['request_method']  = $request->method();
                $q['user_id']         = $request->user()?->id;
                $q['ip_address']      = $request->ip();
            }

            try {
                DB::table('query_performance_logs')->insert($queries);
            } catch (\Exception $e) {
                Log::warning('Failed to log slow queries: ' . $e->getMessage());
            }
        }

        return $response;
    }
}