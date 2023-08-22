<?php

namespace App\Http\Controllers;


use App\Models\GeneralModel as model_class;
use Illuminate\Http\Request;

class SettingsController extends JsonFormController
{
    public function all(Request $request)
    {
        $model = new model_class();
        $model=$model->orderBy('name');
        $init = self::all_init($request, $model);
        $items = $init['items'];
        $pagination = $init["pagination"];
        return view(getProperty($this->structure, "view_path_all"), compact("items", "pagination"));
    }
}
