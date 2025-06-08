<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordLogs
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {

        $headers = $request->header();
        $data = [
            'path' => $request->getPathInfo(),
            'route' => $request->route()->getName(),
            'method' => $request->getMethod(),
            'ip' => $request->ip(),
            'http_version' => @$_SERVER['SERVER_PROTOCOL'],
            'headers' => [
                'user-agent' => @$headers['user-agent'],
                'referer' => @$headers['referer'],
                'origin' => @$headers['origin'],
            ],
        ];

        // if request if authenticated
        if ($request->user()) {
            $data['user_id'] = $request->user()->id;
        }

        // if you want to log all the request body
        if (count($request->all()) > 0) {
            // keys to skip like password or any sensitive information
            $hiddenKeys = ['password', 'token'];
            $data['request'] = $request->except($hiddenKeys);
        }

        $data['response']['status'] = $response->getStatusCode();
        $data['response']['content'] = json_validate($response->getContent()) ? json_decode($response->getContent(), true) : $response->getContent();

        // a unique message to log, I prefer to save the path of request for easy debug
        //        $message = str_replace('/', '_', trim($request->route()->getName(), '/'));

        // log the gathered information
        Log::info($request->getPathInfo(), $data);
    }
}
