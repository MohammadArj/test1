<?php

namespace App\Http\Controllers;

use App\Models\GeneralModel as model_class;
use Illuminate\Http\Request;

class JsonFormController extends GeneralController
{

    public function edit($id)
    {
        if (isValidValue($id) != true) {
            return back()->withErrors("خطایی رخ داده است");
        }
        $item = model_class::where("id", $id)->first();
        if ($item != null) {
            $path_structure = base_path(getProperty($this->structure, "forms_path") . $item->form_file);
            if (file_exists($path_structure)) {
                $structure = json_decode(file_get_contents($path_structure));
                $data = json_decode($item->data);
                if ($data == null) {
                    $data = [];
                }
                return view(getProperty($this->structure, 'view_path_edit'), compact("item", "structure", "data"));
            } else {
                return back()->withErrors("خطایی رخ داده است");
            }

        } else {
            return back()->withErrors("خطایی رخ داده است");
        }

    }

    public function edit_submit($id, Request $request)
    {
        if (isValidValue($id) != true) {
            return back()->withErrors("خطایی رخ داده است");
        }
        $item = model_class::where("id", $id)->first();
        if ($item != null) {
            $json = json_decode($request->input('json'));
            if ($json == null) {
                $json = [];
            }
            model_class::where("id", $id)->update(["data"=>json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)]);
            return redirect(route(getProperty($this->structure, "route_name")))->with(['msg' => "تنظیمات با موفقیت ویرایش یافت."]);

        } else {
            return back()->withErrors("خطایی رخ داده است");
        }
    }

}
