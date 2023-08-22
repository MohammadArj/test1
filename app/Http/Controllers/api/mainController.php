<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class mainController extends Controller
{

    public function test(Request $request)
    {
        //        //reportToDev($request->get("msg"));
        //        $servers=file_get_contents("https://wp1.noobcoder.ir/api/scddddddddascssdfsdf");
        //    dd(json_decode($servers));

    }

  public function receive_telegram_msg_irannova(Request $request)
    {
        // callAPI('https://api.telegram.org/bot' ."5266091605:AAH-lfzsCL2DjJGwPnX404YlkfFgL-sbVl8" . '/getUpdates', ['offset' => -1], 'POST');
         //reportToDev(json_encode((array)$request->all()));
         return callAPI('https://wp2.irannova.shop/api/receive-telegram-msg', ["dssd"=>"ddssdsdds"], 'GET');
    }

    public function get_accounts()
    {
        $out = [];
        $accounts = getGeneralModel("proxy_accounts")->get();
        foreach ($accounts as $account){
            array_push($out,[
                "id"=>getProperty($account,"id"),
                "bot_id"=>getProperty($account,"bot_id"),
                "password"=>getProperty($account,"password"),
                "telegram_user_id"=>getProperty($account,"telegram_user_id"),
            ]);
        }
         return response(["accounts"=>$out]);
    }

    public function send_msg_to_user(Request $request)
    {
        $token=$request->input("token");
        $user_id=$request->input("user_id");
        $msg=$request->input("msg");
        if(isValidValue($token)!=true or isValidValue($user_id)!=true or isValidValue($msg)!=true){
            return "";
        }
        return sendTelegramMsg($token, $user_id, $msg);
    }


    public function get_list_servers()
    {
        $out = [];
        $proxy_servers = getGeneralModel('proxy_servers')->where("status", "active")->get();
        foreach ($proxy_servers as $proxy_server) {
            $extra_data = setNullTo(json_decode($proxy_server->extra_data), []);
            array_push($out, ["id" => $proxy_server->id, "link" =>
                generateVmessLink($proxy_server->display_name, $proxy_server->ip, $proxy_server->port
                    , "api", "9c3c8606-739a-439e-97eb-2929f7b11cf8", $extra_data)
            ]);
        }
        return response(["servers" => $out]);
    }


    public function is_ping_server_alive()
    {
        $ping_server_alive_timestamp = getProperty(getGeneralModel("general_infos")->where("key", "ping_server_alive")->first(), "update_timestamp");
        if (isTimePast(intval($ping_server_alive_timestamp), (60)) != true) {
            return response(["alive" => true]);
        } else {
            return response(["alive" => false]);
        }
    }

    public function get_servers_result(Request $request)
    {
        $time_now = verta()->now()->timestamp;
        $servers = ($request->input("result"));
        getGeneralModel("general_infos")->where("key", "ping_server_alive")->update([
            "update_timestamp" => $time_now
        ]);
        foreach ($servers as $server) {
            getGeneralModel("proxy_servers")->where("id", getProperty($server, "id"))->update([
                "last_ping" => getProperty($server, "ping"),
                "is_alive" => getProperty($server, "status"),
                "last_time_ping" => $time_now,
            ]);
        }
    }

    public function send_to_all(Request $request)
    {
        // $msg = $request->get("msg");
        $msg = "";
        $users = getGeneralModel("users")->where("user_id", "!=", "")->where("bot_id", "7")->get();
        $timestamp_now = verta()->now()->timestamp;
        foreach ($users as $user) {
            if (isTimePast($user->last_time_messaged, 60 * 4)) {
                sendTelegramMsg("5923696662:AAFeMnSNNGPs3SLEZBgH4IQANWI9tHBbtdE", $user->user_id, $msg);
                getGeneralModel("users")->where("id", $user->id)->update(["last_time_messaged" => $timestamp_now]);
            }
        }
        return "done";
    }


    public function checkProxy(Request $request)
    {
        $id = $request->get("id");
        $link = $request->get("link");
        $proxy_responce = checkV2rayProxy($link);
        if (isValidValue($proxy_responce) != true) {
            return [];
        }
        return [$id => $proxy_responce];
    }

    public function reportToDev(Request $request)
    {
        $msg = "IP: " . $request->ip() . "\n";
        $msg .= $request->get("msg");
        sendTelegramMsg("1034411194:AAHAV_aFTknRcFd4J0q5TPFqVmhlc5im_M8", '290240888', $msg);
        return response(["status" => "ok"]);
    }

    public function do_jobs()
    {
        //check_servers_status();
        return "done";
    }

    //////////////////////
    public function fetch_users(Request $request)
    {
        $time_now = verta()->now()->timestamp;
        $proxy_server = getGeneralModel("proxy_servers")->where("id", $request->input("SERVER_ID"))->first();
        if (isValidValue($proxy_server) != true) {
            return response(404);
        }
        $bot_id = getProperty($proxy_server, "bot_id");
        getGeneralModel("proxy_servers")->where("id", $proxy_server->id)
            ->update([
                "last_time_background_service_alive" => intval($time_now),
                "last_users_count" => $request->input("current_users_count"), "current_users" => $request->input("users_connected")
            ]);
        //        getGeneralModel("proxy_servers")->where("id", $proxy_server->id)
        //            ->update(["last_time_background_service_alive" => intval($time_now), "last_ping" => $request->input("current_ping"),
        //                "last_users_count" => $request->input("current_users_count"), "current_users" => $request->input("users_connected")
        //            ]);
        //        if( $request->input("current_ping")!="0"){
        //            getGeneralModel("proxy_servers")->where("id", $proxy_server->id)
        //                ->update(["last_time_online_alive" => intval($time_now)
        //                ]);
        //        }
        $last_fetch_timestamp = $request->input("last_fetch_timestamp");
        $removed_users = [];
        $add_users = [];
        $accounts = getGeneralModel("proxy_accounts");
//        if($proxy_server->bot_id=="1"){
//            $accounts= $accounts->where("bot_id","1");
//        }else{
//            $accounts= $accounts->where("bot_id","!=","1");
//        }
        $accounts = $accounts->where("last_update", ">", $last_fetch_timestamp)->get();
        foreach ($accounts as $account) {
            $user_status = "active";
            if (getProperty($proxy_server, "type") == "vip") {
                $traffic_left_over = floatval(getProperty($account, "traffic_left_over_vip"));
            } else {
                $traffic_left_over = floatval(getProperty($account, "traffic_left_over"));
            }
            if ($traffic_left_over <= 0) {
                $traffic_left_over = 0;
                $user_status = "deactivate";
            }
            if (getProperty($account, "status") != "active") {
                $user_status = "deactivate";
            }
//            if (intval(getProperty($account, 'expiry_time')) < $time_now) {
//                $user_status = "deactivate";
//            }
            //////////////////////////////
            if ($user_status == "deactivate") {
                array_push($removed_users, $account->username);
            } else {
                array_push($add_users, ["username" => $account->username, "password" => $account->password]);
            }
        }
        return response(["status"=>"success","add_users" => $add_users, "removed_users" => $removed_users, "fetch_time" => $time_now]);
    }

    public function update_user_traffic(Request $request)
    {
        $time_now = verta()->now()->timestamp;
        $proxy_server = getGeneralModel("proxy_servers")->where("id", $request->get("SERVER_ID"))->first();
        if (isValidValue($proxy_server) != true) {
            return response(404);
        }
        $bot_id = getProperty($proxy_server, "bot_id");
        $bot_token = getProperty(getGeneralModel("telegram_bots")->where("id", $bot_id)->first(), "api_token");
        $accounts_traffic = json_decode($request->input('users_traffic'));
        $removed_users = [];
        foreach ($accounts_traffic as $username => $account_traffic) {
            $username = $username;
            $account = getGeneralModel('proxy_accounts')->where("username", $username)->first();
            if ($account != null) {
                $temp_account_update = [];
                //reportToDev($username);
                //reportToDev(floatval(byteToMg(getProperty($account_traffic, "traffic"))));
                if (getProperty($proxy_server, "type") == "vip") {
                    $traffic_left_over_vip = floatval(getProperty($account, "traffic_left_over_vip")) - floatval(byteToMg(getProperty($account_traffic, "traffic")));
                    if ($traffic_left_over_vip <= 0) {
                        $traffic_left_over_vip = 0;
                        $temp_account_update['status'] = "deactivate";
                        if (isValidValue(getProperty($account, "telegram_user_id"))) {
                            sendTelegramMsg($bot_token, getProperty($account, "telegram_user_id"), getProperty($account, "username") . "\n\n" . " حجم اینترنت ویژه شما به پایان رسیده است.");
                        }
                    }
                    getGeneralModel("proxy_accounts")->where("username", $username)->update(
                        [
                            "traffic_left_over_vip" => $traffic_left_over_vip,
                            "traffic_total_used" => floatval(getProperty($account, "traffic_total_used")) + floatval(byteToMg(getProperty($account_traffic, "traffic")))
                        ]
                    );
                } else {
                    $traffic_left_over = floatval(getProperty($account, "traffic_left_over")) - floatval(byteToMg(getProperty($account_traffic, "traffic")));
                    if ($traffic_left_over <= 0) {
                        $traffic_left_over = 0;
                        $temp_account_update['status'] = "deactivate";
                        if (isValidValue(getProperty($account, "telegram_user_id"))) {
                            sendTelegramMsg($bot_token, getProperty($account, "telegram_user_id"), getProperty($account, "username") . "\n\n" . " حجم اینترنت عادی شما به پایان رسیده است.");
                        }
                    }
                    getGeneralModel("proxy_accounts")->where("username", $username)->update(
                        [
                            "traffic_left_over" => $traffic_left_over,
                            "traffic_total_used" => floatval(getProperty($account, "traffic_total_used")) + floatval(byteToMg(getProperty($account_traffic, "traffic")))
                        ]
                    );
                }
                ////////////////////////////
                if (getProperty($account, "status") != "active") {
                    $temp_account_update['status'] = "deactivate";
                    if (isValidValue(getProperty($account, "telegram_user_id"))) {
                        sendTelegramMsg($bot_token, getProperty($account, "telegram_user_id"), getProperty($account, "username") . "\n\n" . " حساب شما غیر فعال شده است.");
                    }
                }
                if (intval(getProperty($account, 'expiry_time')) < $time_now) {
                    $temp_account_update['status'] = "deactivate";
                    if (isValidValue(getProperty($account, "telegram_user_id"))) {
                        sendTelegramMsg($bot_token, getProperty($account, "telegram_user_id"), getProperty($account, "username") . "\n\n" . " زمان بسته اینترنتی شما به پایان رسیده است.");
                    }
                }
                //////////////////////////
                if (getProperty($temp_account_update, "status") == "deactivate") {
                    array_push($removed_users, ["username"=>$username]);
                }
            }
        }
        //////////////////
        return response(["status"=>"success","removed_users" => $removed_users]);
    }
}
