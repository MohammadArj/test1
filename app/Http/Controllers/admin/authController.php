<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\adminLoginReq;
use Illuminate\Support\Facades\Auth;

class authController extends Controller
{
    public function login()
    {
        if (Auth::guard("admin")->check() != true) {
            return view("admin.pages.auth.login");
        } else {
            return redirect(route("admin.dashboard"));
        }
    }

    public function login_submit(adminLoginReq $request)
    {
        if (session()->has('admin_login_try')) {
            if (session()->get('admin_login_try') <= 0) {
                return back()->withErrors("دسترسی شما محدود شده است");
            }
        }
        $remember = false;
        if ($request->input('remember') == "on") {
            $remember = true;
        }
        $username = $request->input('username');
        $password = $request->input('password');
        if (Auth::guard('admin')->attempt(["email"=>$username,"password"=>$password],$remember)) {
            return redirect(route('admin.dashboard'));
        } else {
            if (session()->has('admin_login_try')) {
                sessionUpdate(['admin_login_try' => intval(session()->get('admin_login_try')) - 1]);
            } else {
                sessionUpdate(['admin_login_try' => 4]);
            }
            return back()->withErrors("ایمیل یا پسورد اشتباه است");
        }
    }

    public function logout()
    {
        Auth::guard("admin")->logout();
        return redirect(route('admin.dashboard'));
    }

}
