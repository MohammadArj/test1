<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class adminController extends Controller
{

    public function test(Request $request)
    {
        //dd(back()->headers);
        //dd(Hash::make("n@Gx$0gqTzwt5g55o4334H7.XD"));
        //dd($request->all());
//        $data="query_id=AAF4uUwRAAAAAHi5TBFtRIv0&user=%7B%22id%22%3A290240888%2C%22first_name%22%3A%22ALiReza%22%2C%22last_name%22%3A%22%22%2C%22username%22%3A%22alimafi00900%22%2C%22language_code%22%3A%22en%22%7D&auth_date=1672225934&hash=f67cb8a05c4b540d353ad320427009fb0cf5146ef078a972019f278b0fa15e62";
//        $secret_key = hash_hmac("sha256", "5601924177:AAEk9F7hfP0cyteVM4IqK90S4kJFDP8uc7Q", "WebAppData");
//        //dd($secret_key);
//
//        dd((hash_hmac("sha256",$data,$secret_key)));


        //$fd = obj_base64_encode(["username" => "alimafi00900@hdsd.com", "password" => "df3d78ae-7b19-4d37-95c0-936d56f36fce", "bot_id" => "1"]);
        //dd($fd);
        return callAPI('https://api.telegram.org/bot' . "271760414:AAFMLHX953YssiM3ds5G3p9FvQaWZhTGnQI" . '/setWebhook', ["url" => "https://www.ojbot1.shop/api/telegram-bot/receive-update?id=1"], 'POST');
        //sendTelegramMsg("290240888", "sdsd", ["reply_markup" => ["remove_keyboard" => true, "keyboard" => [[["text" => "sdcds", "web_app" => ["url" => "https://bot.noobcoder.ir?token=" . $fd]]]]]]);
    }



    /////////////////
    public function dashboard()
    {
        return view('admin.pages.dashboard');
    }

    //////////////// app_service_orders
    public function app_service_orders()
    {
        return view('admin.pages.appService.orders');
    }


}
