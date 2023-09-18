<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$params)
    {
        // Pre-Middleware Action
        $authorizationData = json_decode(file_get_contents(resource_path('/Json/authorization.json')), true);

        if (!in_array($request->auth->role, $authorizationData[$params[0]][$params[1]])) {
            
            return response()->json([
                'status'=>'error',
                'message'=>'Unauthorized',
            ],401);
        }

        
      
        return $next($request);
    }
}
