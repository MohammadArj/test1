<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class GeneralMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
        $method = Route::getCurrentRoute()->getActionMethod();
        if(getCurrentStructure(["sections",str_replace('_submit',"",$method)])==true){
            return $next($request);
        }else{
            return abort(403);
        }
    }
}
