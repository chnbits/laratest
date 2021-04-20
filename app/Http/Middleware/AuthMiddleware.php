<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $isAdmin = Auth::guard('api')->user();

        if (!$isAdmin){
            return response()->json(['code'=>401,'msg'=>'未授权！']);
        }
        if ($isAdmin['state'] === 1){
            return response()->json(['code'=>401,'msg'=>'账户已禁用！']);
        }
        $request->admin = $isAdmin;
        return $next($request);
    }
}
