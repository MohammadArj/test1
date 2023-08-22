<?php

function sendTelegramMsg($telegram_api_key, $chat_id, $text, $extra_data = [])
{
    #$telegram_api_key="1034411194:AAHAV_aFTknRcFd4J0q5TPFqVmhlc5im_M8";
    //$telegram_api_key="5601924177:AAEk9F7hfP0cyteVM4IqK90S4kJFDP8uc7Q";
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'disable_notification' => false,
    ];

    foreach ($extra_data as $item_key => $item_value) {
        if (gettype($item_value) == "object" or gettype($item_value) == "array") {
            $data[$item_key] = json_encode($item_value);
        } else {
            $data[$item_key] = $item_value;
        }
    }
    if (isValidValue($telegram_api_key) != true) {
        $telegram_api_key = env('TELEGRAM_API_KEY');
    }
    return callAPI('https://api.telegram.org/bot' . $telegram_api_key . '/sendMessage', $data, 'POST');

}

function split_traffic_number($traffic)
{
    $traffic = intval($traffic);
    $traffic = strval($traffic);
    $traffic = str_pad($traffic, 6, '0', STR_PAD_LEFT);
    $before = $traffic[0] . $traffic[1] . $traffic[2];
    $after = $traffic[3] . $traffic[4] . $traffic[5];
    return ["before_kama" => $before, "after_kama" => $after];
}

function getServerPingStatus($ping)
{
    $ping = intval($ping);
    if ($ping > 0 and $ping < 300) {
        return "🟢";
    } elseif ($ping >= 300 and $ping < 350) {
        return "🟡";
    } elseif ($ping >= 350 and $ping < 500) {
        return "🟠";
    } elseif ($ping >= 500 and $ping < 5000) {
        return "🔴";
    } elseif ($ping >= 5000) {
        return "⚫️";
    }
}


function adminLog($userId = null, $log_type, $content = "")
{

    $admin_id = \Illuminate\Support\Facades\Auth::guard("admin")->user()->id;
    $admin_name = \Illuminate\Support\Facades\Auth::guard("admin")->user()->username;
    $adminLog = getGeneralModel("admin_logs");
    $adminLog->admin_id = $admin_id;
    $adminLog->admin_username = $admin_name;
    $adminLog->user_id = $userId;
    $adminLog->log_type = $log_type;
    $adminLog->content = $content;
    $adminLog->date = strval(verta()->now()->format('Y-m-d'));
    $adminLog->time = strval(verta()->now()->format('H:i:s'));
    $adminLog->timestamp = verta()->now()->timestamp;
    $adminLog->save();
}

function generateUuidv4($data = null)
{
    // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
    $data = $data ?? random_bytes(16);
    assert(strlen($data) == 16);

    // Set version to 0100
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // Output the 36 character UUID.
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function generateServerLink($server_id, $proxy_account_id)
{
    $proxy_server = getGeneralModel("proxy_servers")->where("id", $server_id)->where("status", "active")->first();
    $proxy_account = getGeneralModel("proxy_accounts")->where("id", $proxy_account_id)->first();
    if (isValidValue($proxy_server) and isValidValue($proxy_account)) {
        $bot = getGeneralModel("telegram_bots")->where("id", $proxy_server->bot_id)->first();
        $link = "";
        if(getProperty($proxy_server, "protocol")=="vmess"){
            $extra_data = setNullTo(json_decode($proxy_server->extra_data), []);
            $link = generateLinkVmess($proxy_server->display_name, $proxy_server->addres, $proxy_server->port
                , $proxy_account->username, $proxy_account->password, getProperty($proxy_server, "protocol"), $extra_data, $bot->name);
        }elseif (getProperty($proxy_server, "protocol")=="vless"){
            $extra_data = ($proxy_server->extra_data);
            $link = generateLinkVless($proxy_server->display_name, $proxy_server->addres, $proxy_server->port
                , $proxy_account->username, $proxy_account->password, getProperty($proxy_server, "protocol"), $extra_data, $bot->name);
        }
        return $link;
    } else {
        return "";
    }

}

////////////
function generateLinkVmess($title, $host, $port, $username, $uuid, $protocol, $extra_data = [], $bot_name = "")
{
    $v = [
        "add" => $host,
        "aid" => "0",
        "host" => "",
        "id" => strval($uuid),
        "net" => "tcp",
        "path" => "",
        "port" => strval($port),
        "ps" => $title . ' [' . $username . '] ',
        "scy" => "auto",
        "sni" => "",
        "tls" => "",
        "type" => "none",
        "v" => "2"
    ];

    foreach ($extra_data as $key => $value) {
        $v[$key] = $value;
    }

    return $protocol . "://" . base64_encode(json_encode($v));
}

function generateLinkVless($title, $host, $port, $username, $uuid, $protocol, $extra_data = "", $bot_name = "")
{
    return   "vless://" . $uuid . "@" . $host . ":" . $port . "?" .
        $extra_data ."#" . $title."[".$username."]";
}
//////////////

function sendTelegramCommand($telegram_api_key, $commands)
{

    $data = [
        "commands" => json_encode($commands)
    ];
    return callAPI('https://api.telegram.org/bot' . $telegram_api_key . '/setMyCommands', $data, 'POST');

}


function check_servers_status()
{
    $proxeis = getGeneralModel("proxy_servers")->where("status", "active")->get();
    $ping_server_alive_timestamp = getProperty(getGeneralModel("general_infos")->where("key", "ping_server_alive")->first(), "update_timestamp");
    // if (isTimePast(intval($ping_server_alive_timestamp), (60))) {
    //      sendTelegramMsg("1034411194:AAHAV_aFTknRcFd4J0q5TPFqVmhlc5im_M8","290240888", "pinging server is offline \n\n"
    //         . verta()->now()->format('Y-m-d') . "  " . verta()->now()->format('H:i:s')
    //     );
    // }
    foreach ($proxeis as $proxy) {
        // if (isTimePast($proxy->last_time_background_service_alive, (60))) {
        //      sendTelegramMsg("1034411194:AAHAV_aFTknRcFd4J0q5TPFqVmhlc5im_M8","290240888", $proxy->display_name . "  background service is dead\n\n"
        //         . verta()->now()->format('Y-m-d') . "  " . verta()->now()->format('H:i:s')
        //     );
        // }
        /////////////////////
        if (isTimePast(intval($ping_server_alive_timestamp), (60)) != true) {
            if ($proxy->is_alive == "error") {
                sendTelegramMsg("1034411194:AAHAV_aFTknRcFd4J0q5TPFqVmhlc5im_M8", "1034411194:AAHAV_aFTknRcFd4J0q5TPFqVmhlc5im_M8", "290240888", $proxy->display_name . " is offline is_alive\n\n"
                    . verta()->now()->format('Y-m-d') . "  " . verta()->now()->format('H:i:s')
                );
            }
        }
    }
}


function isProxyAlive($ip, $port, $timeout = 3)
{
    $port = intval($port);
    try {
        $sk = fsockopen($ip, $port, $errnum, $errstr, $timeout);
        return is_resource($sk);
    } catch (\Exception $e) {
        return false;
    }

}

function reportToDev($msg)
{
    $msg .= "\nurl: " . "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" . "\n";
    $msg .= "date: " . verta()->now()->format('Y-m-d H:i:s') . "\n";
    try {
        if (\Illuminate\Support\Facades\Auth::guard('admin')->check()) {
            $msg .= "admin: " . \Illuminate\Support\Facades\Auth::guard('admin')->user()->name . "\n";
        }
        if (\Illuminate\Support\Facades\Auth::check()) {
            $msg .= "user: " . \Illuminate\Support\Facades\Auth::user()->ID . ' - ' . \Illuminate\Support\Facades\Auth::user()->user_login . ' - ' . \Illuminate\Support\Facades\Auth::user()->display_name . "\n";
        }
    } catch (\Exception $e) {

    }
    sendTelegramMsg("1034411194:AAHAV_aFTknRcFd4J0q5TPFqVmhlc5im_M8", '290240888', $msg);
}

/////////////////////
function getSessionStepContent($user_id, $session_subject, $session_step)
{
    $telegram_update = getGeneralModel("telegram_updates")->where("user_id", $user_id)->where("session_subject", $session_subject)
        ->where("session_step", $session_step)->latest()->first();

    if (isValidValue($telegram_update)) {
        return getProperty($telegram_update, "content");
    } else {
        return null;

    }
}


/////////////////////////////////////////

function checkSingleSocksProxy($ip, $port)
{
    $user_pass = env("PROXY_USER_PASS", "");
    return CheckSingleProxy($ip, intval($port), $user_pass, 5, false, true, "socks");
}

function checkV2rayProxy($link)
{
    $data = ["link" => $link];
    $res = callAPI('http://194.5.188.175:81/ping-proxy', $data, 'GET');
//    $res=[];
//    for ($index=0;$index<3;$index++){
//        $res=callAPI('http://194.5.188.175:81/ping-proxy', $data, 'GET');
//        if(getProperty($res,"status")=="success"){
//            return $res;
//        }
//    }
    return $res;
}

///////////////////////////////

set_time_limit(100);

function checkMultiSocksProxy($single_link, $proxies)
{
    $data = array();
    foreach ($proxies as $proxy) {
        $url = $single_link;
        $data[] = $url . '?ip=' . $proxy["ip"] . "&port=" . $proxy["port"];
    }
    $results = multiRequest($data);

    $holder = array();
    foreach ($results as $result) {
        $holder[] = json_decode($result, true);
    }
    return $holder;
}

function checkMultiV2rayProxy($single_link, $proxies)
{
    $data = array();
    foreach ($proxies as $proxy) {
        $url = $single_link;
        $data[] = $url . '?id=' . getProperty($proxy, "id") . '&link=' . getProperty($proxy, "link");
    }
    $results = multiRequest($data);
    $holder = array();
    foreach ($results as $result) {
        foreach (json_decode($result, true) as $key => $value) {
            $holder[$key] = $value;
        }
    }
    return $holder;
}

function CheckSingleProxy($ip, $port, $user_pass = null, $timeout, $echoResults = true, $socksOnly = false)
{
    $passByIPPort = $ip . ":" . $port;


    // You can use virtually any website here, but in case you need to implement other proxy settings (show annonimity level)
    // I'll leave you with whatismyipaddress.com, because it shows a lot of info.

    $url = env("PING_URL", "http://whatismyipaddress.com/");

    // Get current time to check proxy speed later on
    $loadingtime = microtime(true);

    $theHeader = curl_init($url);
    curl_setopt($theHeader, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($theHeader, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($theHeader, CURLOPT_PROXY, $passByIPPort);
    if ($user_pass != null) {
        curl_setopt($theHeader, CURLOPT_PROXYUSERPWD, $user_pass);
    }

    //If only socks proxy checking is enabled, use this below.
    if ($socksOnly) {
        curl_setopt($theHeader, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    }

    //This is not another workaround, it's just to make sure that if the IP uses some god-forgotten CA we can still work with it ;)
    //Plus no security is needed, all we are doing is just 'connecting' to check whether it exists!
    curl_setopt($theHeader, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($theHeader, CURLOPT_SSL_VERIFYPEER, 0);

    //Execute the request
    $curlResponse = curl_exec($theHeader);


    if ($curlResponse === false) {
        //If we get a 'connection reset' there's a good chance it's a SOCKS proxy
        //Just as a safety net though, I'm still aborting if $socksOnly is true (i.e. we were initially checking for a socks-specific proxy)
        if (curl_errno($theHeader) == 56 && !$socksOnly) {
            CheckSingleProxy($ip, $port, $timeout, $echoResults, true, "socks");
            return;
        }

        return ["status" => false];
    } else {
        return ["status" => true, "ping" => intval(floor((microtime(true) - $loadingtime) * 1000))];
    }

}

function multiRequest($data, $options = array())
{

    // array of curl handles
    $curly = array();
    // data to be returned
    $result = array();

    // multi handle
    $mh = curl_multi_init();

    // loop through $data and create curl handles
    // then add them to the multi-handle
    foreach ($data as $id => $d) {
        $curly[$id] = curl_init();
        $url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
        curl_setopt($curly[$id], CURLOPT_URL, $url);
        curl_setopt($curly[$id], CURLOPT_HEADER, 0);
        curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);

        // post?
        if (is_array($d)) {
            if (!empty($d['post'])) {
                curl_setopt($curly[$id], CURLOPT_POST, 1);
                curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post']);
            }
        }

        // extra options?
        if (!empty($options)) {
            curl_setopt_array($curly[$id], $options);
        }

        curl_multi_add_handle($mh, $curly[$id]);
    }

    // execute the handles
    $running = null;
    do {
        curl_multi_exec($mh, $running);
    } while ($running > 0);


    // get content and remove handles
    foreach ($curly as $id => $c) {
        $result[$id] = curl_multi_getcontent($c);
        curl_multi_remove_handle($mh, $c);
    }

    // all done
    curl_multi_close($mh);

    return $result;
}

///////////////////////////////////



//////////admin permissions


function getAdminPermissions($userId = null, $str_result = false)
{
    if ($userId == null) {
        $userId = \Illuminate\Support\Facades\Auth::guard("admin")->user()->id;
    }
    $out = getProperty(\App\Models\adminUser::where("id", $userId)->first(), 'permissions');
    if ($str_result) {
        return $out;
    } else {
        return json_decode($out);
    }
}

function adminHaveAccess($permission = null, $user_permissions = null, $userId = null)
{
    if ($userId == null) {
        $userId = \Illuminate\Support\Facades\Auth::guard("admin")->user()->id;
    }
    if (\Illuminate\Support\Facades\Auth::guard("admin")->user()->role == "master_admin") {
        return true;
    }
    if (isValidValue($user_permissions) != true) {
        if (\App\Models\adminUser::where("id", $userId)->where('role', "!=", "master_admin")
                ->where('permissions', "LIKE", "%\"" . $permission . "\"%")->first() != null) {
            return true;
        } else {
            return false;
        }
    } else {
        if (str_contains($user_permissions, "\"" . $permission . "\"")) {
            return true;
        } else {
            return false;
        }
    }
    return false;
}

function existInArray($needle, $array)
{
    if (getType($needle) == "string") {
        if (str_contains(jsonEncode($array), "\"" . $needle . "\"")) {
            return true;
        }
    } elseif (getType($needle) == "array") {
        foreach ($needle as $item) {
            if (str_contains(jsonEncode($array), "\"" . $item . "\"")) {
                return true;
            }
        }

    }
    return false;
}


function filterAdminMenu($menu)
{
    if (\Illuminate\Support\Facades\Auth::guard("admin")->user()->role == "master_admin") {
        return $menu;
    }
    $out = [];
    $adminPermissions = getAdminPermissions();
    foreach ($menu as $item) {
        if (isValidValue(getProperty($item, 'lis'))) {
            if (existInArray(getProperty($item, 'route'), $adminPermissions)) {
                $sub_routes = [];
                $sub_item = (array)$item;
                foreach (getProperty($sub_item, 'lis') as $li) {
                    if (existInArray(getProperty($li, 'route'), $adminPermissions)) {
                        array_push($sub_routes, $li);
                    }
                }
                $sub_item['lis'] = $sub_routes;
                array_push($out, $sub_item);
            }
        } else {
            if (existInArray(getProperty($item, 'route'), $adminPermissions)) {
                array_push($out, $item);
            }
        }
    }
    return $out;
}


//function adminHaveAccessRedirect($permission = null, $userId = null)
//{
//    if (adminHaveAccess($permission, $userId) != true) {
//        return;
//    }
//    $url = route("admin_dashboard");
//    $msg = "به این بخش";
//    if ($permission != null) {
//        $jsonStorage = new jsonStorage();
//        $permissions = $jsonStorage->from("admin_permissions")->all();
//        $msg = getProperty($permissions, [$permission, "display_name"]);
//    }
//    session(["errMsg" => "شما اجازه دسترسی $msg ندارید"]);
//    App::abort(302, '', ['Location' => $url]);
//}

////////////

function adminHaveAccessToBot($bot_id, $admin_id = null)
{
    if (isValidValue($admin_id) != true) {
        $admin_id = \Illuminate\Support\Facades\Auth::guard("admin")->user()->id;
    }
    $admin = getGeneralModel("admin_users")->where("id", $admin_id)->first();
    if (isValidValue($admin) != true) {
        return false;
    }
    if ($admin->role == "master_admin") {
        return true;
    } elseif (str_contains($admin->bot_ids, "\"" . strval($bot_id) . "\"")) {
        return true;
    } else {
        return false;
    }
}


function getTrafficPrice($traffic)
{
    $unit_gb_amount = getProperty(getGeneralModel("general_infos")->where("key", "unit_gb_amount")->first(), "value");
    $admin_id = Route::getCurrentRoute()->parameter("id");
    $gig_price = getProperty(getGeneralModel("admin_users")->where("id", $admin_id)->first(), "gig_price");
    if (isValidValue($gig_price) and $gig_price != 0) {
        $unit_gb_amount = $gig_price;
    }
    return number_format((intval($traffic) / 1000) * intval($unit_gb_amount));
}
