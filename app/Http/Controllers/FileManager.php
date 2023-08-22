<?php

namespace App\Http\Controllers;

use App\Http\Requests\GeneralReq;
use App\Models\GeneralModel as model_class;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class FileManager extends GeneralController
{

    public function add_post_api(Request $request)
    {
        if ( $request->file('file') != null) {
            $path="/uploads/".uploadFile($request->file("file"), "/public/uploads","public");
            return json_encode(['location' => getPublicAsset($path),"path"=>$path]);
        }else{
            return abort(500);
        }
    }


    public function delete(GeneralReq $request)
    {
        if (getCurrentStructure(['sections', 'delete']) != true) {
            return back()->withErrors('این بخش غیر فعال می باشد');
        }
        $id = $request->get("id");
        if (isValidValue($id) != true) {
            return back()->withErrors(["خطایی رخ داده است"]);
        }
        $model = model_class::where("id", $id);
        if ($model->first() != null) {
            delete_file($model->first()->folder,$model->first()->path);
            $model->delete();
            return redirect(route(getProperty($this->structure,"route_name")))->with(["msg" => getProperty($this->structure,"item_name") . " با شماره " . $id . " با موفقیت حذف شد"]);
        } else {
            return back()->withErrors(["خطایی رخ داده است"]);
        }

    }
    public function multiple_delete(Request $request)
    {
        if (getCurrentStructure(['sections', 'multiple_delete']) != true) {
            return back()->withErrors('این بخش غیر فعال می باشد');
        }
        $ides = strToArray($request->input("ids"));
        foreach ($ides as $id) {
            $model = model_class::where("id", $id);
            if ($model->first() != null) {
                delete_file($model->first()->folder,$model->first()->path);
                $model->delete();
            }
        }
        return redirect(route(getProperty($this->structure,"route_name")))->with(["msg" => getProperty($this->structure,"item_name_sum") . " با موفقیت حذف شدند"]);
    }



}
