<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\adminLoginReq;
use App\Models\adminUser;
use App\Models\file_storage;
use App\Models\generalInfo;
use App\Models\GeneralModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class adminApiController extends Controller
{

    public function dark_mood(Request $request)
    {

        $dark_mood_status=$request->get('status');
        if(isValidValue($dark_mood_status)!=true){
            return response(["status"=>"error"]);
        }
        $cookie = cookie('dark_mood', $dark_mood_status);
        return response(["status"=>"success"])->withCookie($cookie);
    }
    public function generate_uuid4()
    {
        return response(["uuid"=>generateUuidv4()]);
    }

    public function sync_user_info(Request $request)
    {
        $id=$request->get("id");
        $time = verta()->now()->timestamp;
        getGeneralModel("proxy_accounts")->where("id", $id)->update(["last_update" => $time]);
        return back()->with(["msg"=>"درخاست همگام سازی کابر ".$id." با سرور ها داده شد"]);
    }

}
