<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post("/receive-telegram-msg-irannova", "App\Http\Controllers\api\mainController@receive_telegram_msg_irannova");

Route::get("/send-to-all", "App\Http\Controllers\api\mainController@send_to_all");
Route::get("/test", "App\Http\Controllers\api\mainController@test");
Route::get("/is-ping-server-alive", "App\Http\Controllers\api\mainController@is_ping_server_alive");
Route::get("/do-jobs", "App\Http\Controllers\api\mainController@do_jobs");
Route::get("/jhkkhblhjbkhvcvkbmbgvtdslkdskldskljy", "App\Http\Controllers\api\mainController@get_list_servers")->name("get_list_servers");
Route::post("/gfcjgvjgvjfrcvhgvghjksdkdskldsklds", "App\Http\Controllers\api\mainController@get_servers_result")->name("get_servers_result");
Route::get("/check-proxy", "App\Http\Controllers\api\mainController@checkProxy")->name("check_proxy");

Route::post("/background-service-alive", "App\Http\Controllers\api\mainController@background_service_alive");
Route::get("/report-to-dev", "App\Http\Controllers\api\mainController@reportToDev");


Route::get("/sasaaaaaaaaaaaaaaa-get-accounts", "App\Http\Controllers\api\mainController@get_accounts");
Route::post("/sasaaaaaaaaaaaaaaa-send-msg-user", "App\Http\Controllers\api\mainController@send_msg_to_user");

//////////////
Route::prefix('/')->middleware(['server_auth'])->group(function (){
    Route::post("/fetch-users", "App\Http\Controllers\api\mainController@fetch_users");
    Route::post("/update-user-traffic", "App\Http\Controllers\api\mainController@update_user_traffic");

});
/////////////
Route::prefix('/telegram-bot')->group(function (){
    Route::get("/test", "App\Http\Controllers\api\TelegramBotController@test");
    Route::post("/receive-update", "App\Http\Controllers\api\TelegramBotController@receive_update");
});


Route::prefix("/bot")->group(function () {

    Route::get("/get-account-info", "App\Http\Controllers\api\TelegramBotController@get_account_info")->name("user_bot_get_account_info");
    Route::get("/get-list-servers", "App\Http\Controllers\api\TelegramBotController@get_list_servers")->name("user_bot_get_list_servers");

});
