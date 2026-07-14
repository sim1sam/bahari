<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAdminActivity
{
    public function __construct(private ActivityLogger $logger) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 400) {
            try {
                $this->logger->logFromRequest($request);
            } catch (\Throwable) {
                // Never break admin flows because of logging.
            }
        }

        return $response;
    }
}
