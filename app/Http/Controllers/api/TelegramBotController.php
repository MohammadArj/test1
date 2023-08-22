<?php

namespace App\Http\Controllers\api;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TelegramBotController extends Controller
{

    public function test()
    {

    }


    #$telegram_api_key = env('TELEGRAM_API_KEY');
    #return callAPI('https://api.telegram.org/bot' . $telegram_api_key . '/setWebhook', ["url"=>"https://noobcoder.ir/api/telegram-bot/receive-update"], 'POST');
    public function receive_update(Request $request)
    {

        $update = $request->all();
        if (getProperty($update, ["message", "chat", "type"]) != "private") {
            return;
        }
        $bot_id=$request->get("id");
        $bot_token=getProperty(getGeneralModel("telegram_bots")->where("id",$bot_id)->first(),"api_token");
        $time_now = verta()->now()->timestamp;
        /////////////////////////
        $telegram_update = getGeneralModel("telegram_updates");
        $telegram_update->bot_id = $bot_id;
        $telegram_update->update_id = getProperty($update, ["update_id"]);
        $telegram_update->message_id = getProperty($update, ["message", "message_id"]);
        $telegram_update->user_id = getProperty($update, ["message", "chat", "id"]);
        $telegram_update->first_name = getProperty($update, ["message", "chat", "first_name"]);
        $telegram_update->username = getProperty($update, ["message", "chat", "username"]);
        $telegram_update->content = getProperty($update, ["message", "text"]);
        $telegram_update->update_date = getProperty($update, ["message", "date"]);
        $telegram_update->update_content = json_encode($update);
        $telegram_update->date = verta()->now()->format('Y-m-d');
        $telegram_update->time = verta()->now()->format('H:i:s');
        $telegram_update->timestamp = verta()->now()->timestamp;
        $telegram_update->save();
        /////////////////////
        $user = getGeneralModel("users")->where("bot_id",$bot_id)->where("user_id", $telegram_update->user_id)->first();
        if ($user == null) {
            $user = getGeneralModel("users");
            $user->user_id = $telegram_update->user_id;
            $user->bot_id = $bot_id;
            $user->status = "active";
            $user->first_name = $telegram_update->first_name;
            $user->username = $telegram_update->username;
            $user->date = verta()->now()->format('Y-m-d');
            $user->time = verta()->now()->format('H:i:s');
            $user->timestamp = verta()->now()->timestamp;
            $user->save();
        }
        if (isValidValue($user->current_session_subject) != true and isValidValue($user->current_session_step) != true) {
            if (getGeneralModel("proxy_accounts")->where("bot_id",$bot_id)->where("telegram_user_id", $telegram_update->user_id)->first() == null) {
                getGeneralModel("users")->where("bot_id",$bot_id)->where("id", $user->id)->update(["current_session_subject" => "login", "current_session_step" => "set_username"]);
                sendTelegramMsg($bot_token,$telegram_update->user_id, "نام کاربری خود را وارد کنید");
                return response(["status" => "ok"]);
            }
        }


        ///////////////////////
        if ($user->current_session_subject == "login") {
            if ($user->current_session_step == "set_username") {
                getGeneralModel("telegram_updates")->where("bot_id",$bot_id)->where("id", $telegram_update->id)->update(["session_subject" => "login", "session_step" => "set_username"]);
                getGeneralModel("users")->where("bot_id",$bot_id)->where("id", $user->id)->update(["current_session_step" => "set_password"]);
                sendTelegramMsg($bot_token,$telegram_update->user_id, "رمز کاربری خود را وارد کنید");
                return response(["status" => "ok"]);
            } else if ($user->current_session_step == "set_password") {
                getGeneralModel("telegram_updates")->where("bot_id",$bot_id)->where("id", $telegram_update->id)->update(["session_subject" => "login", "session_step" => "set_password"]);
                $proxy_account = getGeneralModel("proxy_accounts")->where("bot_id",$bot_id)
                    ->where("username", getSessionStepContent($user->user_id, "login", "set_username"))
                    ->where("password", getSessionStepContent($user->user_id, "login", "set_password"))->first();
                if (isValidValue($proxy_account)) {
                    getGeneralModel("proxy_accounts")->where("bot_id",$bot_id)->where("id", $proxy_account->id)->update(["telegram_user_id" => $user->user_id]);
                    getGeneralModel("users")->where("bot_id",$bot_id)->where("id", $user->id)->update(["current_proxy_account" => $proxy_account->id,
                        "current_session_subject" => "", "current_session_step" => ""]);
                    sendTelegramMsg($bot_token,$telegram_update->user_id, "ورود با موفقیت انجام شد.");
                    sendTelegramMsg($bot_token,$telegram_update->user_id, "می توانید با انتخاب لیست سرور ها از منوی زیر سرور های فعال را مشاهده نمایید.");
                    sendTelegramCommand($bot_token,[["command" => "list_servers", "description" => "لیست سرور ها"], ["command" => "account_info", "description" => "اطلاعات حساب کاربری"]]);
                    return response(["status" => "ok"]);
                } else {
                    sendTelegramMsg($bot_token,$telegram_update->user_id, "حسابی با این اطلاعات یافت نشد");
                    sendTelegramMsg($bot_token,$telegram_update->user_id, "نام کاربری خود را وارد کنید");
                    getGeneralModel("users")->where("bot_id",$bot_id)->where("id", $user->id)->update(["current_session_step" => "set_username"]);
                    return response(["status" => "ok"]);
                }
            }
        }
        //////////////////////

        sendTelegramMsg($bot_token,$telegram_update->user_id, "", ["reply_markup" => ["remove_keyboard"=> true]]);
        //sendTelegramCommand($bot_token,[["command" => "list_servers", "description" => "لیست سرور ها"]]);
        //sendTelegramCommand($bot_token,[["command" => "list_servers", "description" => "لیست سرور ها"], ["command" => "logout", "description" => "خروج از حساب کاربری"]]);
        if (isValidValue($user->current_session_subject) != true) {
            if ($telegram_update->content == "/list_servers") {
                $proxy_account = getGeneralModel("proxy_accounts")->where("bot_id",$bot_id)->where("telegram_user_id", $user->user_id)->first();
                //////////////////
                $msg = "لیست سرور های ملی:" . "\n\n";
                foreach (getGeneralModel("proxy_servers")
                             //->where("bot_id",$bot_id)
                             ->where("status", "active")
                             ->where("display", "active")
                             ->where("type", "vip")
                             ->orderBy('last_ping', 'ASC')
                             ->get() as $proxy) {
                    if (true) {
//                    if (isTimePast($proxy->last_time_ping, (60*3))!=true) {
                        $msg .= fillStr(getProperty($proxy, "display_name"), 26) . "  ";
                        $msg .= fillStr("/_" . getProperty($proxy, "id"), 5) . " ";
                        $msg .= fillStr(" " . getServerPingStatus(getProperty($proxy, "last_ping")) . "  ", 5);
                        $msg .= fillStr(" " . getProperty($proxy, "last_users_count") . " نفر ", 5);
                        $msg .= fillStr(" " . getProperty($proxy, "last_ping") . "ms  ", 5);
                        $msg .= "\n\n";
                    }
                }
                ////////////////////////
                $msg .= "لیست سرور های عادی:" . "\n\n";
                foreach (getGeneralModel("proxy_servers")
                            // ->where("bot_id",$bot_id)
                             ->where("status", "active")
                             ->where("display", "active")
                             ->where("type", "normal")
                             ->orderBy('last_ping', 'ASC')
                             ->get() as $proxy) {
//                    if (isTimePast($proxy->last_time_ping, (60*3))!=true) {
                    if (true) {
                        $msg .= fillStr(getProperty($proxy, "display_name"), 20) . "  ";
                        $msg .= fillStr("/_" . getProperty($proxy, "id"), 5) . " ";
                        $msg .= fillStr(" " . getServerPingStatus(getProperty($proxy, "last_ping")) . "  ", 5);
                        $msg .= fillStr(" " . getProperty($proxy, "last_users_count") . " نفر ", 5);
                        $msg .= fillStr(" " . getProperty($proxy, "last_ping") . "ms  ", 5);
                        $msg .= "\n\n";
                    }
                }
                /////////////////////////////////////////
                ////////////////////////////////////////
                $msg .= "\n" . "حساب کاربری: " . $proxy_account->username . " \n";
                $msg .= "\n" . "حجم باقیمانده ویژه: ";
                $msg .= number_format(intval($proxy_account->traffic_left_over_vip)) . " مگابایت " . " \n\n";
                $msg .= "حجم باقیمانده عادی: ";
                $msg .= number_format(intval($proxy_account->traffic_left_over)) . " مگابایت " . " \n\n";
                $msg .= "حجم کلی مصرفی: ";
                $msg .= number_format(intval($proxy_account->traffic_total_used)) . " مگابایت " . " \n\n";
//                $msg .= "تاریخ اتقضا: ";
//                // $msg.=Carbon::createFromTimestamp($proxy_account->expiry_time)->toDateTimeString()."\n\n" ;
//                $time_left = secondsToTime(intval($proxy_account->expiry_time) - intval($time_now));
//                if (getProperty($time_left, "y") != 0) {
//                    $msg .= getProperty($time_left, "y") . " سال ";
//                }
//                if (getProperty($time_left, "m") != 0) {
//                    $msg .= getProperty($time_left, "m") . " ماه ";
//                }
//                $msg .= getProperty($time_left, "d") . " روز دیگر ";
                $msg .= "\n\n ";
                sendTelegramMsg($bot_token,$telegram_update->user_id, $msg);
            } else if (str_contains($telegram_update->content, "/_")) {
                $server_id = explode("/_", $telegram_update->content)[1];
                $proxy_account = getGeneralModel("proxy_accounts")->where("bot_id",$bot_id)->where("telegram_user_id", $user->user_id)->first();
                $link = generateServerLink($server_id, $proxy_account->id);
                if (isValidValue($link)) {
                    $proxy_server = getGeneralModel("proxy_servers")->where("bot_id",$bot_id)->where("id", $server_id)->where("status", "active")->first();
//                    if ($proxy_server->is_beta == "active") {
//                        sendTelegramMsg($bot_token,$telegram_update->user_id, "سرور های بتا در مرحله تست میباشند و احتمال قطعی دارند");
//                    }
                    sendTelegramMsg($bot_token,$telegram_update->user_id, $link);
                } else {
                    sendTelegramMsg($bot_token,$telegram_update->user_id, "سرور درخواستی یافت نشد");
                }
            } else if ($telegram_update->content == "u" and $telegram_update->user_id == "290240888") {
                $proxy_servers_model = getGeneralModel("proxy_servers")->where("bot_id",$bot_id)->where("status", "active");
                $proxy_servers_model = $proxy_servers_model->orderBy('index_num', 'ASC')->get();
                $total_users = 0;
                foreach ($proxy_servers_model as $proxy) {
                    $total_users += intval($proxy->last_users_count);
                    sendTelegramMsg($bot_token,"290240888", $proxy->display_name . " " . $proxy->last_users_count . " " . $proxy->current_users);
                }
                sendTelegramMsg($bot_token,"290240888", "total users: " . strval($total_users));
            } elseif ($telegram_update->content == "/logout") {
                if (isValidValue($user->user_id)) {
                    getGeneralModel("proxy_accounts")->where("bot_id",$bot_id)->where("telegram_user_id", $user->user_id)->
                    update([
                        "telegram_user_id" => ""
                    ]);
                    sendTelegramMsg($bot_token,$telegram_update->user_id, "از حساب کاربری با موفقیت خارج شدید");
                }
            }
        }
    }

    public function get_account_info(Request $request)
    {
        $token=$request->get("token");
        if(isValidValue($token)!=true){
            return response([],422);
        }
        $inputs=obj_base64_decode($token);
        $proxy_account = getGeneralModel("proxy_accounts")
            ->where("bot_id",getProperty($inputs,"bi"))
            ->where("telegram_user_id",getProperty($inputs,"ui"))
            ->first();

        if(isValidValue($proxy_account)!=true){
            return response([],404);
        }
        $traffic_left_over_vip=split_traffic_number($proxy_account->traffic_left_over_vip);
        $traffic_left_over=split_traffic_number($proxy_account->traffic_left_over);

        return response(["traffic_left_over_vip"=>$traffic_left_over_vip,"traffic_left_over"=>$traffic_left_over]);
    }

    public function get_list_servers(Request $request)
    {
        $token=$request->get("token");
        if(isValidValue($token)!=true){
            return response([],422);
        }
        $inputs=obj_base64_decode($token);
        $bot_id=getProperty($inputs,"bi");
        $proxy_account = getGeneralModel("proxy_accounts")
            ->where("bot_id",$bot_id)
            ->where("telegram_user_id",getProperty($inputs,"ui"))
            ->first();
        if(isValidValue($proxy_account)!=true){
            return response([],404);
        }
        ////////////////////////////
        $list_servers_vip=[];
        $servers_vip =getGeneralModel("proxy_servers")
//            ->where("bot_id",$bot_id)
            ->where("status", "active")
            ->where("display", "active")
            ->where("type", "vip")
            ->orderBy('index_num', 'ASC')
            ->get();
        foreach ($servers_vip as $server){
            array_push($list_servers_vip,[
                "title"=>$server->display_name,
                "subtitle"=>$server->subtitle,
                "flag"=>$server->flag,
                "link"=>generateServerLink($server->id, $proxy_account->id)
            ]);
        }
        //////////////////////////////
        $list_servers_normal=[];
        $servers_normal= getGeneralModel("proxy_servers")
//            ->where("bot_id",$bot_id)
            ->where("status", "active")
            ->where("display", "active")
            ->where("type", "normal")
            ->orderBy('index_num', 'ASC')
            ->get();
        foreach ($servers_normal as $server){
            array_push($list_servers_normal,[
                "title"=>$server->display_name,
                "subtitle"=>$server->subtitle,
                "flag"=>$server->flag,
                "link"=>generateServerLink($server->id, $proxy_account->id)
            ]);
        }
        /////////////////////////
        return response(["servers_vip"=>$list_servers_vip,"servers_normal"=>$list_servers_normal]);
    }
}


