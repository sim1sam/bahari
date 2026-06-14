<?php

namespace App\Http\Middleware;

use App\Models\ApiSource;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiSource
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key')
            ?? $request->query('api_key');

        $token = $request->bearerToken()
            ?? $request->query('api_token');

        if (! $apiKey || ! $token) {
            return response()->json(['message' => 'API key and token are required.'], 401);
        }

        $source = ApiSource::where('api_key', $apiKey)->where('is_active', true)->first();

        if (! $source || ! $source->matchesToken($token)) {
            return response()->json(['message' => 'Invalid API credentials.'], 401);
        }

        $request->attributes->set('api_source', $source);

        return $next($request);
    }
}
