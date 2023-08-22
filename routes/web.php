<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix("/admin")->group(function () {

    Route::get("/test", "App\Http\Controllers\admin\adminController@test");
/////////////
    Route::get("/login", "App\Http\Controllers\admin\authController@login")->name('admin.login');
    Route::post("/login-submit", "App\Http\Controllers\admin\authController@login_submit")->name('admin.login_submit');
    Route::get("/logout", "App\Http\Controllers\admin\authController@logout")->name('admin.logout');
/////////////
    Route::prefix("/")->middleware(['admin_auth',"adminHaveAccess","adminActive"])->group(function () {

        Route::get("/", "App\Http\Controllers\admin\adminController@dashboard")->name('admin.dashboard');
        Route::get("/get-public-file", "App\Http\Controllers\admin\adminController@get_public_file")->name('admin.get_public_file');

        Route::prefix("/settings")->group(function () {
            /////////////
            $section_name = "settings";
            $route_name = "admin.settings";
            $class_path = "App\Http\Controllers\SettingsController";
            //////////////
            getBasicAdminRoutes($section_name, $class_path);
        });

        Route::prefix("/templates")->group(function () {
            /////////////
            $section_name = "templates";
            $route_name = "admin.templates";
            $class_path = "App\Http\Controllers\TemplatesController";
            //////////////
            getBasicAdminRoutes($section_name, $class_path);
        });
        //////////////////////////
        Route::prefix("/admin-logs")->group(function () {
            /////////////
            $section_name = "adminLogs";
            /////////////
            getBasicAdminRoutes($section_name);
        });

        Route::prefix("/users")->group(function () {
            /////////////
            $section_name = "users";
            $route_name = "admin.users";
            $class_path = "App\Http\Controllers\admin\users";
            /////////////
            getBasicAdminRoutes($section_name);

        });

        Route::prefix("/proxy-accounts")->group(function () {
            /////////////
            $section_name = "proxyAccounts";
            $route_name = "admin.proxyAccounts";
            $class_path = "App\Http\Controllers\admin\proxyAccount";
            /////////////
            Route::get("/{bot_id}", $class_path . "@all")->name($route_name);
            Route::get("/add/{bot_id}", $class_path . "@add")->name($route_name . "_add");
            Route::post("/add-submit/{bot_id}", $class_path . "@add_submit")->name($route_name . "_add_submit");
            Route::get("/edit/{bot_id}/{id}", $class_path . "@edit")->name($route_name . "_edit");
            Route::post("/edit-submit/{bot_id}/{id}", $class_path . "@edit_submit")->name($route_name . "_edit_submit");
            Route::get("/delete", $class_path . "@delete")->name($route_name . "_delete");
            Route::get("/multiple-delete", $class_path . "@multiple_delete")->name($route_name . "_multiple_delete");
            //////////////
            Route::post("/edit-single-field", $class_path . "@edit_single_field")->name($route_name . "_edit_single_field");
            Route::get("/single/{bot_id}/{id}", $class_path . "@single")->name($route_name . "_single");
            Route::get("/list-servers/{bot_id}/{id}", $class_path."@list_servers")->name($route_name.'_list_servers');
        });


        Route::prefix("/file-manager")->group(function () {
            /////////////
            $section_name = "fileManager";
            $route_name = "admin.fileManager";
            $class_path = "App\Http\Controllers\FileManager";
            //////////////
            getBasicAdminRoutes($section_name, $class_path);
            Route::post('/api/add-post', $class_path . "@add_post_api")->name($route_name . "_add_post_api");
        });
        //////////////////////////////////////

        Route::prefix("/admin-users")->group(function () {
            /////////////
            $section_name = "adminUsers";
            /////////////
            $class_path= getBasicAdminRoutes($section_name);
            Route::get("/chart/{id}", $class_path . "@chart")->name("admin.".$section_name."_chart");
        });
    });

    Route::prefix("/api")->middleware(['admin_auth'])->group(function () {
        Route::get("/dark-mood", "App\Http\Controllers\admin\adminApiController@dark_mood")->name('api.admin.dark_mood');
        Route::get("/generate-uuid4", "App\Http\Controllers\admin\adminApiController@generate_uuid4")->name('api.admin.generate_uuid4');
        Route::get("/sync-user-info", "App\Http\Controllers\admin\adminApiController@sync_user_info")->name('api.admin.sync_user_info');
    });
});
