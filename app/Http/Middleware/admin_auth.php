<?php

namespace App\Http\Middleware;

use App\Models\adminUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class admin_auth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard("admin")->check() != true) {
            return redirect(route('admin.login'));
        } else {
            if(adminUser::where("id",Auth::guard("admin")->user()->id)->where("status","active")->first()==null){
                Auth::guard("admin")->logout();
                return redirect(route('admin.login'));
            }
            return $next($request);
        }

    }
}
