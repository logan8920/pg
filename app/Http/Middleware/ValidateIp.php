<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiCredentials;
use Illuminate\Support\Facades\Auth;
class ValidateIp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        $apiCredentials = ApiCredentials::whereJsonContains('ipaddress', $ip)->first();

        if (!$apiCredentials)
            return response()->json(['message' => 'Unauthorized IP address.'], 403);

        $request->attributes->set('apiCredentials', $apiCredentials);
        return $next($request);
    }
}
