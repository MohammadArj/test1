<?php

namespace App\Http\Middleware;

use App\Http\Helpers\jsonStorage;
use App\Models\adminUser;
use Closure;
use http\Url;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class adminActive
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
        if(Auth::guard("admin")->user()->status=="active"){
            return $next($request);
        }else{
            Auth::guard("admin")->logout();
            return redirect(route("admin.login"))->withErrors("حساب شما مسدود شده است");
        }
    }
}
