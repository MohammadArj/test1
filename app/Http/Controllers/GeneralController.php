<?php

namespace App\Http\Controllers;


use App\Http\Requests\GeneralReq;
use \App\Models\GeneralModel as model_class;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;

class GeneralController extends Controller
{
    public function __construct()
    {
        $this->structure = initStructure(getStructureNameFromRoute());
    }

    protected function all_init($request, $model)
    {
        $search = $request->get("search");
        $filters = getCurrentStructure('all_filter');
        if (isValidValue($filters)) {
            foreach ($filters as $filter) {
                if (getProperty($filter, "type") == "where") {
                    $model = $model->where(getProperty($filter, "column")
                        , getProperty($filter, "operator")
                        , getProperty($filter, "value"));
                }
            }
        }
        if (getCurrentStructure(['sections', 'advancedSearch']) == true) {
            $advancedSearch_options = getCurrentStructure(['advancedSearch']);
            $advancedSearch_inputs = json_decode($request->get('advancedSearch'));
            if (isValidValue($advancedSearch_inputs)) {
                foreach ($advancedSearch_inputs as $advancedSearch_input_kay => $advancedSearch_input_value) {
                    $advancedSearch_field = getProperty($advancedSearch_options, $advancedSearch_input_kay);
                    if (isValidValue($advancedSearch_field)) {
                        if (getProperty($advancedSearch_field, "type") == "where") {
                            $model = $model->where(getProperty($advancedSearch_field, "column")
                                , getProperty($advancedSearch_field, "operator")
                                , str_replace("#", $advancedSearch_input_value, getProperty($advancedSearch_field, "format")));
                        } else if (getProperty($advancedSearch_field, "type") == "orWhere") {
                            $model = $model->where(getProperty($advancedSearch_field, "column")
                                , getProperty($advancedSearch_field, "operator")
                                , str_replace("#", $advancedSearch_input_value, getProperty($advancedSearch_field, "format")));
                        } elseif (getProperty($advancedSearch_field, "type") == "orderBy") {
                            $model = $model->orderBy(getProperty($advancedSearch_field, "column"), $advancedSearch_input_value);
                        }
                    }
                }
            }
        }
        if (isValidValue($search)) {
            if (str_contains($search, "#")) {
                $model = $model->where("id", str_replace("#", "", $search))->get();
            } else {
                $model = $model->where(
                    function ($query) use ($search) {
                        $query->orWhere("id", $search);
                        foreach (getProperty($this->structure, "fields") as $field_kay => $field_value) {
                            if (getProperty($field_value, ['sections', 'search'])) {
                                $query = $query->orWhere($field_kay, "LIKE", "%" . $search . "%");
                            }
                        }
                    }
                )->get();
            }
        } else {
            $model = $model->get();
        }
        $pageItems = getPagePagination($model, $request->get("page"));
        $items = $pageItems["items"];
        $pagination = $pageItems["pagination"];
        return ["items" => $items, "pagination" => $pagination];
    }

    //////////////////
    public function all(Request $request)
    {
        $model = new model_class();
        $init = self::all_init($request, $model);
        $items = $init['items'];
        $pagination = $init["pagination"];
        return view(getProperty($this->structure, "view_path_all"), compact("items", "pagination"));
    }

    public function add()
    {
        if (getCurrentStructure(['sections', 'add']) != true) {
            return back()->withErrors('این بخش غیر فعال می باشد');
        }
        return view(getProperty($this->structure, "view_path_add"));
    }

    public function add_submit(GeneralReq $request)
    {
        if (getCurrentStructure(['sections', 'add']) != true) {
            return back()->withErrors('این بخش غیر فعال می باشد');
        }
        $model = new model_class();
        $fields = getCurrentStructure('fields');
        foreach ($fields as $field_key => $field_value) {
            if (getProperty($field_value, ['sections', 'add']) === true) {
                ///////////
                $field_type = getProperty($field_value, ['type']);
                $value = '';
                ////////////
                if ($field_type == 'text') {
                    $value = $request->input($field_key);
                    $model->$field_key = $value;
                } else if ($field_type == 'phone_number') {
                    $value = $request->input($field_key);
                    if (validatePhoneNumber($value) != true) {
                        return back()->withErrors('شماره همراه به درستی وارد نشده است.');
                    }
                    $model->$field_key = $value;
                } elseif ($field_type == 'slug') {
                    $value = $request->input($field_key);
                    if (isValidValue($value) == true) {
                        $value = makeSlug(new model_class(), "slug", $value);
                        $model->$field_key = $value;
                    } else {
                        $value = makeSlug(new model_class(), "slug", $request->input(getCurrentStructure(['fields', $field_key, "slug_to"])));
                        $model->$field_key = $value;
                    }
                } elseif ($field_type == 'select') {
                    $value = $request->input($field_key);
                    $model->$field_key = $value;
                } elseif ($field_type == 'select-ref') {
                    $value = $request->input($field_key);
                    $model->$field_key = $value;
                } elseif ($field_type == 'multiple-select') {
                    $value = $request->input($field_key);
                    $model->$field_key = $value;
                } elseif ($field_type == 'json') {
                    $value = $request->input($field_key);
                    $model->$field_key = $value;
                } elseif ($field_type == 'img') {
                    if (isValidValue($request->file($field_key))) {
                        $value = getCurrentStructure(['fields', $field_key, "path_db"]) . uploadFile($request->file($field_key), getCurrentStructure(['fields', $field_key, "path_folder"]), getCurrentStructure(['fields', $field_key, "path_folder_type"]));
                        $model->$field_key = $value;
                    }
                } elseif ($field_type == 'str-array') {
                    $value = jsonEncode(strToArray($request->input($field_key)));
                    $model->$field_key = $value;
                } elseif ($field_type == 'password') {
                    if (isValidValue($request->input($field_key))) {
                        $value = Hash::make($request->input($field_key));
                        $model->$field_key = $value;
                    }
                }

            }
        }
        $model->date = verta()->now()->format('Y-m-d');
        $model->time = verta()->now()->format('H:i:s');
        //$model->timestamp = verta()->now()->timestamp;
        $model->save();
        return redirect(route(getProperty($this->structure, "route_name")))->with(["msg" => getProperty($this->structure, "item_name") . " با موفقیت ایجاد شد"]);
    }

    public function edit($id)
    {
        if (getCurrentStructure(['sections', 'edit']) != true) {
            return back()->withErrors('این بخش غیر فعال می باشد');
        }
        if (isValidValue($id) != true) {
            return back()->withErrors("خطایی رخ داده است");
        }
        $item = model_class::where("id", $id)->first();
        if ($item != null) {
            return view(getProperty($this->structure, "view_path_edit"), compact("item"));
        } else {
            return back()->withErrors("خطایی رخ داده است");
        }
    }

    public function edit_submit($id, GeneralReq $request)
    {
        if (getCurrentStructure(['sections', 'edit']) != true) {
            return back()->withErrors('این بخش غیر فعال می باشد');
        }
        if (isValidValue($id) != true) {
            return back()->withErrors("خطایی رخ داده است");
        }
        $model = model_class::where("id", $id);
        if ($model->first() != null) {
            $data = [];
            $fields = getCurrentStructure('fields');
            foreach ($fields as $field_key => $field_value) {
                if (getProperty($field_value, ['sections', 'edit']) === true) {
                    ///////////
                    $field_type = getProperty($field_value, ['type']);
                    $value = '';
                    ////////////
                    if ($field_type == 'text') {
                        $value = $request->input($field_key);
                        $data = array_merge($data, [$field_key => $value]);
                    } else if ($field_type == 'phone_number') {
                        $value = $request->input($field_key);
                        if (validatePhoneNumber($value) != true) {
                            return back()->withErrors('شماره همراه به درستی وارد نشده است.');
                        }
                        $data = array_merge($data, [$field_key => $value]);
                    } elseif ($field_type == 'slug') {
                        $value = $request->input($field_key);
                        if (isValidValue($value) == true) {
                            $value = makeSlug(new model_class(), "slug", $value, "id", $id);
                            $data = array_merge($data, [$field_key => $value]);
                        } else {
                            $value = makeSlug(new model_class(), "slug", $request->input(getCurrentStructure(['fields', $field_key, "slug_to"])), "id", $id);
                            $data = array_merge($data, [$field_key => $value]);
                        }
                    } elseif ($field_type == 'select') {
                        $value = $request->input($field_key);
                        $data = array_merge($data, [$field_key => $value]);
                    } elseif ($field_type == 'select-ref') {
                        $value = $request->input($field_key);
                        $data = array_merge($data, [$field_key => $value]);
                    } elseif ($field_type == 'multiple-select') {
                        $value = $request->input($field_key);
                        $data = array_merge($data, [$field_key => $value]);
                    } elseif ($field_type == 'json') {
                        $value = $request->input($field_key);
                        $data = array_merge($data, [$field_key => $value]);
                    } elseif ($field_type == 'img') {
                        if (isValidValue($request->file($field_key))) {
                            $value = getCurrentStructure(['fields', $field_key, "path_db"]) . uploadFile($request->file($field_key), getCurrentStructure(['fields', $field_key, "path_folder"]), getCurrentStructure(['fields', $field_key, "path_folder_type"]), $id);
                            $data = array_merge($data, [$field_key => $value]);
                        }
                    } elseif ($field_type == 'str-array') {
                        $value = jsonEncode(strToArray($request->input($field_key)));
                        $data = array_merge($data, [$field_key => $value]);
                    } elseif ($field_type == 'password') {
                        if (isValidValue($request->input($field_key))) {
                            $value = Hash::make($request->input($field_key));
                            $data = array_merge($data, [$field_key => $value]);
                        }
                    }


                    /////////////

                }
            }
            $model->update($data);
            $redirect_link=route(getProperty($this->structure, "route_name"));
            if(getProperty($this->structure, "redirect_back_edit")===true){
                $redirect_link=url()->previous();
            }
            return redirect($redirect_link)->with(["msg" => getProperty($this->structure, "item_name") . " " . $id . " با موفقیت ویرایش یافت"]);
        } else {
            return back()->withErrors("خطایی رخ داده است");
        }
    }

    public function single($id)
    {
        if (getCurrentStructure(['sections', 'single']) != true) {
            return back()->withErrors('این بخش غیر فعال می باشد');
        }
        if (isValidValue($id) != true) {
            return back()->withErrors(["خطایی رخ داده است"]);
        }
        $item = model_class::where("id", $id)->first();
        if ($item != null) {
            return view(getProperty($this->structure, "view_path_single"), compact('item'));
        } else {
            return back()->withErrors(["خطایی رخ داده است"]);
        }
    }

    public function edit_single_field(GeneralReq $request)
    {
        $id = $request->input('id');
        $field_key = $request->input("field");
        $value = $request->input("value");
        if (isValidValue($id) != true or isValidValue($field_key) != true) {
            return response(['errMsg' => "خطایی رخ داده است"], 400);
        }
        $field = getProperty($this->structure, ["fields", $field_key]);
        if (getProperty($field, ["templates", "all", "required"]) == true) {
            if (isValidValue($value) != true) {
                return response(['errMsg' => "وارد کردن مقدار اجباری است"], 400);
            }
        }
        $model = model_class::where("id", $id);
        if ($model->first() != null) {
            $model->update([$field_key => $value]);
            return response(['res' => "ok", "msg" => "ایتم شماره " . $id . " با موفقیت ویرایش یافت"], 200);
        } else {
            return response(['errMsg' => "ایتمی یافت نشد"], 400);
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
            $model->delete();
            return redirect(route(getProperty($this->structure, "route_name")))->with(["msg" => getProperty($this->structure, "item_name") . " با شماره " . $id . " با موفقیت حذف شد"]);
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
                $model->delete();
            }
        }
        return redirect(route(getProperty($this->structure, "route_name")))->with(["msg" => getProperty($this->structure, "item_name_sum") . " با موفقیت حذف شدند"]);
    }

}
