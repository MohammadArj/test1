<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\GeneralController;
use App\Http\Requests\GeneralReq;
use App\Models\GeneralModel as model_class;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class proxyAccount extends GeneralController
{

    public function all(Request $request)
    {
        $model = new model_class();
        adminLog("", "all_accounts", "مشاهده اکانت ها");
        $bot_id = Route::getCurrentRoute()->parameter("bot_id");
        if (isValidValue($bot_id) != true) {
            return abort(422);
        }
        if (adminHaveAccessToBot($bot_id) != true) {
            return abort(403);
        }
        if (Auth::guard("admin")->user()->role != "master_admin") {
            if (Auth::guard("admin")->user()->access_to_all_users != "active") {
                $model = $model->where("admin_id", Auth::guard("admin")->user()->id);
            }
        }
        $model = $model->where("bot_id", $bot_id);
        $init = self::all_init($request, $model);
        $items = $init['items'];
        $pagination = $init["pagination"];
        return view(getProperty($this->structure, "view_path_all"), compact("items", "pagination"));
    }

    public function add_submit(GeneralReq $request)
    {
        $model = new model_class();
        $bot_id = Route::getCurrentRoute()->parameter("bot_id");
        if (isValidValue($bot_id) != true) {
            return abort(422);
        }
        if (adminHaveAccessToBot($bot_id) != true) {
            return abort(403);
        }
        $username = $request->input("username");
        $model->username = $username;
        if (getGeneralModel("proxy_accounts")->where("username", $username)->first() != null) {
            return back()->withErrors("حسابی با این نام کاربری وجود دارد.");
        }
        $model->bot_id = $bot_id;
        $model->status = $request->input("status");
        $model->password = $request->input("password");
        $model->type_account = $request->input("type_account");

        $traffic_left_over_vip = $request->input("traffic_left_over_vip");
        $traffic_left_over = $request->input("traffic_left_over");

        $model->traffic_left_over_vip = $traffic_left_over_vip;
        $model->traffic_left_over = $traffic_left_over;
        /////////////////////
        $expiry_time = verta()->now();
        if (isValidValue($request->input("expiry_time_year"), true)) {
            $expiry_time = $expiry_time->addYears(intval($request->input("expiry_time_year")));
        }
        if (isValidValue($request->input("expiry_time_month"), true)) {
            $expiry_time = $expiry_time->addMonth(intval($request->input("expiry_time_month")));
        }
        if (isValidValue($request->input("expiry_time_day"), true)) {
            $expiry_time = $expiry_time->addDays(intval($request->input("expiry_time_day")));
        }
        if (isValidValue($request->input("expiry_time_hour"), true)) {
            $expiry_time = $expiry_time->addHours(intval($request->input("expiry_time_hour")));
        }
        if (isValidValue($request->input("expiry_time_min"), true)) {
            $expiry_time = $expiry_time->addMinutes(intval($request->input("expiry_time_min")));
        }
        $model->expiry_time = $expiry_time->timestamp;
        //////////////////
        $model->admin_id = Auth::guard("admin")->user()->id;

        $model->admin_display_name = Auth::guard("admin")->user()->display_name;
        $model->date = verta()->now()->format('Y-m-d');
        $model->time = verta()->now()->format('H:i:s');
        $model->timestamp = verta()->now()->timestamp;
        $model->last_update = verta()->now()->timestamp;
        $model->save();
        /////////////////
        if (floatval($traffic_left_over_vip) > 0) {
            $traffic_log_vip = getGeneralModel("traffic_logs");
            $traffic_log_vip->bot_id = $bot_id;
            $traffic_log_vip->admin_id = Auth::guard("admin")->user()->id;
            $traffic_log_vip->admin_display_name = Auth::guard("admin")->user()->display_name;
            $traffic_log_vip->admin_username = Auth::guard("admin")->user()->username;
            $traffic_log_vip->user_id = $model->id;
            $traffic_log_vip->user_username = $model->username;
            $traffic_log_vip->traffic_type = "vip";
            $traffic_log_vip->operator = "+";
            $traffic_log_vip->amount = $traffic_left_over_vip;
            $traffic_log_vip->date = verta()->now()->format('Y-m-d');
            $traffic_log_vip->time = verta()->now()->format('H:i:s');
            $traffic_log_vip->timestamp = verta()->now()->timestamp;
            $traffic_log_vip->save();
        }
        ////////////////////////////////
        if (floatval($traffic_left_over) > 0) {
            $traffic_log_normal = getGeneralModel("traffic_logs");
            $traffic_log_normal->bot_id = $bot_id;
            $traffic_log_normal->admin_id = Auth::guard("admin")->user()->id;
            $traffic_log_normal->admin_display_name = Auth::guard("admin")->user()->display_name;
            $traffic_log_normal->admin_username = Auth::guard("admin")->user()->username;
            $traffic_log_normal->user_id = $model->id;
            $traffic_log_normal->user_username = $model->username;
            $traffic_log_normal->traffic_type = "normal";
            $traffic_log_normal->operator = "+";
            $traffic_log_normal->amount = $traffic_left_over;
            $traffic_log_normal->date = verta()->now()->format('Y-m-d');
            $traffic_log_normal->time = verta()->now()->format('H:i:s');
            $traffic_log_normal->timestamp = verta()->now()->timestamp;
            $traffic_log_normal->save();
        }

        ///////////////
        adminLog($model->id, "add_account_submit",
            "add_account", "user_id: " . $model->id .
            "username: " . $model->username .
            " type_account: " . $model->type_account .
            " traffic_left_over_vip: " . $model->traffic_left_over_vip .
            " traffic_left_over: " . $model->traffic_left_over .
            " expiry_time: " . strval($expiry_time->timestamp)
        );
        return redirect(route(getProperty($this->structure, "route_name") . "_single", [Route::getCurrentRoute()->parameter("bot_id"), $model->id]))->with(["msg" => getProperty($this->structure, "item_name") . " با موفقیت ایجاد شد"]);
    }

    public function edit($id)
    {
        $id = Route::getCurrentRoute()->parameter("id");
        if (getCurrentStructure(['sections', 'edit']) != true) {
            return back()->withErrors('این بخش غیر فعال می باشد');
        }
        if (isValidValue($id) != true) {
            return back()->withErrors("خطایی رخ داده است");
        }
        $bot_id = Route::getCurrentRoute()->parameter("bot_id");
        if (isValidValue($bot_id) != true) {
            return abort(422);
        }
        if (adminHaveAccessToBot($bot_id) != true) {
            return abort(403);
        }
        $item = model_class::where("id", $id)->where("bot_id", $bot_id);
        if (Auth::guard("admin")->user()->role != "master_admin") {
            if (Auth::guard("admin")->user()->access_to_all_users != "active") {
                $item = $item->where("admin_id", Auth::guard("admin")->user()->id);
            }
        }
        $item = $item->first();
        if ($item != null) {
            adminLog($item->id, "edit_account",
                "username: " . $item->username);
            return view(getProperty($this->structure, "view_path_edit"), compact("item"));
        } else {
            return back()->withErrors("خطایی رخ داده است");
        }
    }

    public function edit_submit($id, GeneralReq $request)
    {
        $id = Route::getCurrentRoute()->parameter("id");
        if (isValidValue($id) != true) {
            return back()->withErrors("خطایی رخ داده است");
        }
        $bot_id = Route::getCurrentRoute()->parameter("bot_id");
        if (isValidValue($bot_id) != true) {
            return abort(422);
        }
        if (adminHaveAccessToBot($bot_id) != true) {
            return abort(403);
        }
        $model = model_class::where("id", $id)->where("bot_id", $bot_id);
        if (Auth::guard("admin")->user()->role != "master_admin") {
            if (Auth::guard("admin")->user()->access_to_all_users != "active") {
                $model = $model->where("admin_id", Auth::guard("admin")->user()->id);
            }
        }
        if ($model->first() != null) {
            $proxy_account = $model->first();
            $data = [];
            $time_now = verta()->now()->timestamp;
            $username = $request->input("username");
            $model->username = $username;
            if (getGeneralModel("proxy_accounts")->where("username", $username)->where("id", "!=", $id)->first() != null) {
                return back()->withErrors("حسابی با این نام کاربری وجود دارد.");
            }
            $data['status'] = $request->input("status");
            $data['password'] = $request->input("password");
            $data['type_account'] = $request->input("type_account");
            ///////////////
            $traffic_left_over_vip = $request->input("traffic_left_over_vip");
            $traffic_left_over = $request->input("traffic_left_over");
            $data['traffic_left_over_vip'] = $traffic_left_over_vip;
            $data['traffic_left_over'] = $traffic_left_over;
            /////////////////////
            if ($proxy_account->traffic_left_over_vip != $traffic_left_over_vip) {
                $traffic_log_vip = getGeneralModel("traffic_logs");
                $traffic_log_vip->bot_id = $bot_id;
                $traffic_log_vip->admin_id = Auth::guard("admin")->user()->id;
                $traffic_log_vip->admin_display_name = Auth::guard("admin")->user()->display_name;
                $traffic_log_vip->admin_username = Auth::guard("admin")->user()->username;
                $traffic_log_vip->user_id = $proxy_account->id;
                $traffic_log_vip->user_username = $proxy_account->username;
                $traffic_log_vip->traffic_type = "vip";
                ////////////
                $amount_traffic_left_over_vip = floatval($traffic_left_over_vip) - floatval($proxy_account->traffic_left_over_vip);
                if ($amount_traffic_left_over_vip > 0) {
                    $traffic_log_vip->operator = "+";
                    $traffic_log_vip->amount = $amount_traffic_left_over_vip;
                } else {
                    $traffic_log_vip->operator = "-";
                    $traffic_log_vip->amount = ($amount_traffic_left_over_vip * (-1));
                }
                ////////////////
                $traffic_log_vip->date = verta()->now()->format('Y-m-d');
                $traffic_log_vip->time = verta()->now()->format('H:i:s');
                $traffic_log_vip->timestamp = verta()->now()->timestamp;
                $traffic_log_vip->save();
            }
            ////////////////////////////////
            if ($proxy_account->traffic_left_over != $traffic_left_over) {
                $traffic_log_normal = getGeneralModel("traffic_logs");
                $traffic_log_normal->bot_id = $bot_id;
                $traffic_log_normal->admin_id = Auth::guard("admin")->user()->id;
                $traffic_log_normal->admin_display_name = Auth::guard("admin")->user()->display_name;
                $traffic_log_normal->admin_username = Auth::guard("admin")->user()->username;
                $traffic_log_normal->user_id = $proxy_account->id;
                $traffic_log_normal->user_username = $proxy_account->username;
                $traffic_log_normal->traffic_type = "normal";
                /////////////////
                $amount_traffic_log_normal = floatval($traffic_left_over) - floatval($proxy_account->traffic_left_over);
                if ($amount_traffic_log_normal > 0) {
                    $traffic_log_normal->operator = "+";
                    $traffic_log_normal->amount = $amount_traffic_log_normal;
                } else {
                    $traffic_log_normal->operator = "-";
                    $traffic_log_normal->amount = ($amount_traffic_log_normal * (-1));
                }
                /////////////
                $traffic_log_normal->date = verta()->now()->format('Y-m-d');
                $traffic_log_normal->time = verta()->now()->format('H:i:s');
                $traffic_log_normal->timestamp = verta()->now()->timestamp;
                $traffic_log_normal->save();
            }
            /////////////////////
            $expiry_time = verta()->now();

            if (isValidValue($request->input("expiry_time_year"), true) and $request->input("expiry_time_year") != "0") {
                $expiry_time = $expiry_time->addYears(intval($request->input("expiry_time_year")));
            }
            if (isValidValue($request->input("expiry_time_month"), true) and $request->input("expiry_time_month") != "0") {
                $expiry_time = $expiry_time->addMonth(intval($request->input("expiry_time_month")));
            }
            if (isValidValue($request->input("expiry_time_day"), true) and $request->input("expiry_time_day") != "0") {
                $expiry_time = $expiry_time->addDays(intval($request->input("expiry_time_day")));
            }
            if (isValidValue($request->input("expiry_time_hour"), true) and $request->input("expiry_time_hour") != "0") {
                $expiry_time = $expiry_time->addHours(intval($request->input("expiry_time_hour")));
            }
            if (isValidValue($request->input("expiry_time_min"), true) and $request->input("expiry_time_min") != "0") {
                $expiry_time = $expiry_time->addMinutes(intval($request->input("expiry_time_min")));
            }
            $data['expiry_time'] = $expiry_time->timestamp;
            $data['last_update'] = verta()->now()->timestamp;
            //////////////////
            $item = getGeneralModel("proxy_accounts")->where("id", $id)->first();
            ////////////////
            if ($item->status != $data['status']) {
                adminLog($item->id, "edit_account_submit_change_status", "username: " . $item->username
                    . " old_status: " . $item->status . " current_status: " . $data['status']
                );
            }
            //////////////
            if ($item->password != $data['password']) {
                adminLog($item->id, "edit_account_submit_change_password", "username: " . $item->username
                    . " old_password: " . $item->password . " current_password: " . $data['password']
                );
            }
            ///////////////
            if ($item->traffic_left_over_vip != $data['traffic_left_over_vip']) {
                adminLog($item->id, "edit_account_submit_change_traffic_left_over_vip", "username: " . $item->username
                    . " old_traffic_left_over_vip: " . $item->traffic_left_over_vip . " current_traffic_left_over_vip: " . $data['traffic_left_over_vip']
                );
            }
            //////////////////
            if ($item->traffic_left_over != $data['traffic_left_over']) {
                adminLog($item->id, "edit_account_submit_change_traffic_left_over", "username: " . $item->username
                    . " old_traffic_left_over: " . $item->traffic_left_over . " current_traffic_left_over: " . $data['traffic_left_over']
                );
            }
            //////////////////
            if ($item->expiry_time != $data['expiry_time']) {
                adminLog($item->id, "edit_account_submit_change_expiry_time", "username: " . $item->username
                    . " old_expiry_time: " . $item->expiry_time . " current_expiry_time: " . $data['expiry_time']
                );
            }
            //////////////////
            $model->update($data);
            return redirect(route(getProperty($this->structure, "route_name") . "_single", [Route::getCurrentRoute()->parameter("bot_id"), $id]))->with(["msg" => getProperty($this->structure, "item_name") . " " . $id . " با موفقیت ویرایش یافت"]);
        } else {
            return back()->withErrors("خطایی رخ داده است");
        }
    }


    public function single($id)
    {
        $id = Route::getCurrentRoute()->parameter("id");
        if (getCurrentStructure(['sections', 'single']) != true) {
            return back()->withErrors('این بخش غیر فعال می باشد');
        }
        if (isValidValue($id) != true) {
            return back()->withErrors(["خطایی رخ داده است"]);
        }
        $bot_id = Route::getCurrentRoute()->parameter("bot_id");
        if (isValidValue($bot_id) != true) {
            return abort(422);
        }
        if (adminHaveAccessToBot($bot_id) != true) {
            return abort(403);
        }
        $item = model_class::where("id", $id)
            ->where("bot_id", $bot_id)
        ;
        if (Auth::guard("admin")->user()->role != "master_admin") {
            if (Auth::guard("admin")->user()->access_to_all_users != "active") {
                $item = $item->where("admin_id", Auth::guard("admin")->user()->id);
            }
        }
        $item = $item->first();
        if ($item != null) {
            adminLog($item->id, "single_account", "username: " . $item->username);
            return view(getProperty($this->structure, "view_path_single"), compact('item'));
        } else {
            return back()->withErrors(["خطایی رخ داده است"]);
        }
    }


    public function list_servers($id)
    {
        $id = Route::getCurrentRoute()->parameter("id");
        if (isValidValue($id) != true) {
            return back()->withErrors("خطایی رخ داده است");
        }
        adminLog($id, "single_account");
        $bot_id = Route::getCurrentRoute()->parameter("bot_id");
        if (isValidValue($bot_id) != true) {
            return abort(422);
        }
        if (adminHaveAccessToBot($bot_id) != true) {
            return abort(403);
        }
        $proxy_servers = getGeneralModel("proxy_servers")->where("status", "active")
           // ->where("bot_id", $bot_id)
        ;
        $proxy_account = getGeneralModel("proxy_accounts")->where("id", $id)
            ->where("bot_id", $bot_id)
        ;
        if (Auth::guard("admin")->user()->role != "master_admin") {
            if (Auth::guard("admin")->user()->access_to_all_users != "active") {
                $proxy_account = $proxy_account->where("admin_id", Auth::guard("admin")->user()->id);
            }
        }
        $proxy_account = $proxy_account->first();
        if (isValidValue($proxy_account) != true) {
            return back()->withErrors("خطایی رخ داده است");
        }
        adminLog($proxy_account->id, "list_servers", "username: " . $proxy_account->username);
        $proxy_servers = $proxy_servers
           // ->where("bot_id", $proxy_account->bot_id)
            ->orderBy('index_num', 'ASC')->get();
        return view("admin.pages.proxyAccounts.single.servers", compact('proxy_servers', 'proxy_account'));
    }

}
