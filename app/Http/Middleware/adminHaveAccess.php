<?php

namespace App\Http\Middleware;

use App\Http\Helpers\jsonStorage;
use App\Models\adminUser;
use Closure;
use http\Url;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class adminHaveAccess
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
        $route_name=Route::getCurrentRoute()->getName();
        if (adminHaveAccess($route_name)) {
            return $next($request);
        } else {
            return abort(403);
        }
    }
}
