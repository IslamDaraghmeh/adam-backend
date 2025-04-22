<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Log;
use Symfony\Component\HttpFoundation\Response;

class HandleHttpExceptions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (\Throwable $exception) {
            if ($exception instanceof NotFoundHttpException) {
                Log::error($exception->getMessage());
                return response()->json([
                    'message' => 'The route you are looking for could not be found.'
                ], 404);
            }

            // Handle other exceptions or pass them to the default handler
            return response()->json([
                'message' => 'An error occurred.'
            ], 500);
        }
    }
}
