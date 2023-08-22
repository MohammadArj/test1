<?php

function activePage($route, $out = "active")
{
    if (getType($route) == "array") {
        foreach ($route as $value) {
            if (str_contains(\Request::route()->getName(), $value)) {
                return $out;
            }
        }
        return "";

    } else if (getType($route) == "string") {
        if (str_contains(\Request::route()->getName(), $route)) {
            return $out;
        } else {
            return "";
        }
    }
}
function secondsToTime($seconds) {
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT);
}

function isTimePast($time,$sec=0){
    return intval($time)+$sec < verta()->now()->timestamp;
}

function fillStr($str,$length,$fill_char=" "){
    $left_over=$length-strlen($str);
    if($left_over>0){
        for($index=0;$index<$left_over;$index++){
            $str.=$fill_char;
        }
    }
    return $str;
}

function round_to_6dp($number)
{
    return number_format((float)$number, 6, '.', '');
}

function byteToMg($mb)
{
    return round_to_6dp(($mb) / 1000000);
}

function getJSONFile($fileName)
{
    return json_decode(file_get_contents(app_path('Jsons/' . $fileName . ".json")));
}

function jsonEncode($value)
{
    return json_encode($value, JSON_UNESCAPED_UNICODE);
}

function inArray($needle, $array)
{
    $array = setNullTo($array, []);
    foreach ($array as $item) {
        if ((array)$item == $needle) {
            return true;
        }
    }
    return false;
}

function phoneNumberWithZero($str)
{
    if ($str[0] != '0') {
        $str = '0' . $str;
    }
    return $str;
}


function obj_base64_encode($obj){
   return base64_encode(json_encode($obj));
}
function obj_base64_decode($obj){
    return json_decode(base64_decode($obj));
}


function phoneNumberWithOutZero($str)
{
    if ($str[0] == '0') {
        $str = substr_replace($str, '', 0, 1);
    }
    return $str;
}

function sessionUpdate($array, $update = true)
{
    foreach ($array as $kay => $value) {
        $session = session()->get($kay);
        if ($session == null) {
            $session = [];
        }
        if ($update and gettype($value) == 'array') {
            updateProperty($session, $value);
            $value = $session;
        }
        session()->put($kay, $value);
        session()->save();
    }
}

function sessionForget($key)
{
    session()->forget($key);
    session()->save();
}


function validatePhoneNumber($item)
{
    if (isValidValue($item) != true) {
        return false;
    }
    $item = phoneNumberWithZero($item);
    if (strlen($item)) {
        if (!preg_match("/^(09([0-9]{1}[0-9]{1})([0-9]{7}))*$/", $item)) {
            return false;
        }
        return true;
    } else {
        return false;
    }
}

function validateEmail($email)
{
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    } else {
        return false;
    }
}


function updateProperty(&$array, $values)
{
    if (isValidValue($values) != true) {
        $values = [];
    }
    foreach ($values as $key => $value) {
        if (array_key_exists($key, $array)) {
            $array[$key] = $value;
        } else {
            $array += [$key => $value];
        }
    }
}


function getLastKeyFieldForm($array, $name)
{
    if (str_contains($name, "_") != true) {
        return false;
    }
    $listKeys = [];
    $name_key = explode('_', $name)[0];
    foreach ($array as $itemKey => $itemValue) {
        if (str_contains($itemKey, $name_key . "_")) {
            array_push($listKeys, $itemKey);
        }
    }
    if ($name == $listKeys[count($listKeys) - 1]) {
        return true;
    } else {
        return false;
    }
}


function formatBytes($size, $precision = 2)
{
    $base = log($size, 1024);
    $suffixes = array('', 'Kb', 'Mb', 'Gb', 'Tb');

    return round(pow(1024, $base - floor($base)), $precision) . '' . $suffixes[floor($base)];
}


function makeSlug($model, $field_slug, $str, $field_id = "id", $id = null)
{

    $str = trim($str, $characters = " \n\r\t\v\0");
    $str = str_replace(' ', "-", $str);
    $index = 1;
    while (true) {
        $res = $model->where($field_slug, $str);
        if (isValidValue($id)) {
            $res = $res->where($field_id, "!=", $id);
        }
        $res = $res->first();
        if ($res == null) {
            return $str;
        } else {
            $str = $str . "-" . strval($index);
            $index += 1;
        }
    }
}

function randomStr($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randString = '';
    for ($i = 0; $i < intval($length); $i++) {
        $randString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randString;
}

function isValidValue($value, $number = false)
{
    if ($value === 0) {
        return true;
    }
    if ($value != null and $value != "") {
        if ($number) {
            if (is_numeric($value)) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    } else {
        return false;
    }
}


function strToArray($string, $separator = ',')
{
    $out = [];
    $arrayStr = explode($separator, $string);
    foreach ($arrayStr as $value) {
        if (isValidValue($value)) {
            $value = str_replace(' ', "", $value);
            array_push($out, $value);
        }
    }
    return $out;
}

function arrayToStr($array)
{
    $array = (array)$array;
    $out = '';
    for ($index = 0; $index < count($array); $index++) {
        $out .= $array[$index];
        if ((count($array) - 1) != $index) {
            $out .= ",";
        }
    }
    return $out;
}

function authAttempt($login, $password, $remember = false)
{
    return Auth::attempt(['user_login' => $login, 'password' => $password], $remember);
}

function authLogout()
{
    Auth::logout();
    session()->flush();
    session()->save();
}


function floatDecimalPlaces($number, $formatNumber = false, $decimals = 2)
{
    $out = (float)number_format((float)$number, $decimals, '.', '');
    if ($formatNumber) {
        $out = number_format($out, 2, '.', '');
    }
    return $out;
}

function updateObject($object, $properties, $values)
{
    $temp = &$object;
    foreach ($properties as $property) {
        $temp =& $temp->$property;
    }
    if ($values != null) {
        try {
            foreach ($values as $key => $value) {
                $temp->$key = $value;
            }
        } catch (\Exception $e) {
            array_merge((array)$temp, $values);
        }
    } else {
        $temp = null;
    }
    return json_decode(json_encode($object));
}

function getProperty($object, $properties = [], $notFoundValue = null)
{
    if (gettype($properties) == "string") {
        $properties = [$properties];
    }
    try {
        $out = $object;
        foreach ($properties as $property) {
            if (gettype($out) == "object") {
                $out = $out->$property;
            } else {
                $out = $out[$property];
            }
        }
        return $out;
    } catch (\Exception $e) {
        return $notFoundValue;
    }
}

function setNullTo($value, $nullValue)
{
    if (isValidValue($value)) {
        return $value;
    } else {
        return $nullValue;
    }
}

function validateIranPhoneNumber($mobile)
{
    if (preg_match("/^(09([0-9]{1}[0-9]{1})([0-9]{7}))*$/", $mobile) == 1) {
        return true;
    } else {
        return false;
    }
}

function getPagePagination($allItems, $pageNum, $eachPage = 15)
{
    $num = intval($pageNum);
    if ($num <= 0) {
        $num = 1;
    }
    $allPages = ceil($allItems->count() / $eachPage);
    if ($allPages == 0) {
        $allPages = 1;
    }
    if ($allPages < $num) {
        $num = 1;
    }
    if ($num > 0 && $num <= $allPages) {
        $items = $allItems->reverse()->skip(($eachPage * $num) - $eachPage)->take(($eachPage));
    } else {
        $items = [];
    }
    //////////////////
    $start = $num - 15;
    $end = $num + 15;
    if ($start <= 1) {
        $start = 1;
    }
    if ($end >= $allPages) {
        $end = $allPages;
    }
    if ($end <= $start) {
        $end = $start;
    }
    $eee = ['items' => $items, "pagination" => ['allPages' => intval($allPages), 'currentPage' => $num, "start" => $start, "end" => $end]];
    return $eee;
}

function uniqueModelIdStr($model, $field, $length = 60)
{
    $out = randomStr($length);
    while (true) {
        if ($model::where($field, $out)->first() == null) {
            return strval($out);
        }
        $out = randomStr($length);
    }
}

function uniqueModelId($model, $field, $end = 999999999)
{
    $out = str_pad(strval(rand(1, $end)), 9, 0, STR_PAD_LEFT);
    while (true) {
        if ($model::where($field, $out)->first() == null) {
            return strval($out);
        }
        $out = str_pad(strval(rand(1, $end)), 9, 0, STR_PAD_LEFT);
    }
}

function nullToStr($value)
{
    if ($value == null) {
        return "";
    } else {
        return $value;
    }
}


function arrayToJson($array)
{
    return json_decode(json_encode($array));
}


function callAPI($url, $data = [], $method = "GET")
{
    $curl = curl_init();
    switch ($method) {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        case "GET":
            if (count((array)$data) != 0) {
                $url = sprintf("%s?%s", $url, http_build_query($data));
            }

    }
    // Optional Authentication:
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    ///curl_setopt($curl, CURLOPT_USERPWD, "username:password");
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    curl_close($curl);
    return json_decode($result);
}


function appendToArray(&$array, $value)
{
    if (gettype($array) != 'array') {
        $array = [];
    }
    if (in_array($value, $array) != true) {
        array_push($array, $value);
    }
    return $array;
}

function removeFromArray(&$array, $value)
{
    if (gettype($array) != 'array') {
        $array = [];
    }
    if (in_array($value, $array)) {
        $key = array_search($value, $array);
        if ($key !== false) {
            unset($array[$key]);
        }
        $array = array_values($array);
    }
    return $array;
}

function filterNullArray($array)
{
    $out = [];
    foreach ($array as $item) {
        if (isValidValue($item)) {
            array_push($out, $item);
        }
    }
    return $out;
}

/////////////

function generateItemIndex($model_class)
{
    $index = getProperty($model_class::all()->reverse()->first(), "index");
    if (isValidValue($index, true) != true) {
        $index = "1";
    } else {
        $index = strval(intval($index) + 1);
    }
    return $index;
}


function downloadFile($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
    curl_setopt($ch, CURLOPT_BUFFERSIZE, 12800);
    curl_setopt($ch, CURLOPT_NOPROGRESS, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36');
    curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($DownloadSize, $Downloaded, $UploadSize, $Uploaded) {
        return ($Downloaded > 1024 * 4096) ? 1 : 0;
    }); # max 4096kb

    $version = curl_version();
    if ($version !== FALSE && ($version['features'] & CURL_VERSION_SSL)) { // Curl do support SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    }

    $response = curl_exec($ch);

    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    if ($response === false) {
        return null;
    }
    $header_blocks = array_filter(preg_split('#\n\s*\n#Uis', substr($response, 0, $header_size)));
    $header_array = explode("\n", array_pop($header_blocks));
    $body = substr($response, $header_size);
    $headers = [];
    foreach ($header_array as $header_value) {
        $header_pieces = explode(':', $header_value);
        if (count($header_pieces) == 2) {
            $headers[strtolower($header_pieces[0])] = trim($header_pieces[1]);
        }
    }
    return ['header' => $headers, "body" => $body];
}

//function uploadFile($file, $file_path, $folder = "public", $table = null, $attach_id = null, $file_name = null)
//{
//    if (isValidValue($folder) != true) {
//        $folder = "public";
//    }
//    if ($file != null) {
//        if (isValidValue($file_name) != true) {
//            $file_name = randomStr(60);
//        }
//        $file_full_name = $file_name . '.' . strtolower($file->getClientOriginalExtension());
//        $file_full_path = $file_path . "/" . $file_full_name;
//        Storage::disk('minio')->put($file_full_path, $file->getContent());
//        //////////
//        $file_storage = new \App\Models\file_storage();
//        $file_storage->name = $file_full_name;
//        $file_storage->original_name = $file->getClientOriginalName();
//        $file_storage->mime_type = $file->getClientMimeType();
//        $file_storage->original_extension = strtolower($file->getClientOriginalExtension());
//        $file_storage->size = $file->getSize();
//        $file_storage->path =str_replace("//", '/',  str_replace("/public", '', $file_full_path));
//        $file_storage->folder = $folder;
//        $file_storage->attach_table = $table;
//        $file_storage->attach_id = $attach_id;
//        $file_storage->date = verta()->now()->format('Y-m-d');
//        $file_storage->time = verta()->now()->format('H:i:s');
//        $file_storage->timestamp = verta()->now()->timestamp;
//        $file_storage->save();
//        ///////////
//        return $file_full_name;
//    } else {
//        return null;
//    }
//
//}

function uploadFile($file, $file_path, $folder = "public", $table = null, $attach_id = null, $file_name = null)
{
    if ($file != null) {
        if (isValidValue($file_name) != true) {
            $file_name = randomStr(60);
        }
        $file_full_name = $file_name . '.' . strtolower($file->getClientOriginalExtension());
        $file_full_path = $file_path . "/" . $file_full_name;
        if(isValidValue($folder)!=true){
            $folder = "public";
        }
        if ($folder == "storage") {
            $file->move(storage_path($file_path), $file_full_name);
        } elseif ($folder == "public") {
            $file->move(public_path($file_path), $file_full_name);
        }
        if (isValidValue($table) != true) {
            $table = getCurrentStructure('table');
        }
        //////////
        $file_storage = new \App\Models\file_storage();
        $file_storage->name = $file_full_name;
        $file_storage->original_name = $file->getClientOriginalName();
        $file_storage->mime_type = $file->getClientMimeType();
        $file_storage->original_extension = strtolower($file->getClientOriginalExtension());
        //$file_storage->size = $file->getSize();
        $file_storage->path = $file_full_path;
        $file_storage->folder = $folder;
        $file_storage->attach_table = $table;
        $file_storage->attach_id = $attach_id;
        $file_storage->date = verta()->now()->format('Y-m-d');
        $file_storage->time = verta()->now()->format('H:i:s');
        $file_storage->timestamp = verta()->now()->timestamp;
        $file_storage->save();
        ///////////
        return $file_full_name;
    } else {
        return null;
    }
}


//function uploadFile($file, $file_path, $folder = "public",$table=null, $attach_id = null, $file_name = null)
//{
//
//    if ($file != null) {
//        if (isValidValue($file_name) != true) {
//            $file_name = randomStr(60);
//        }
//        $file_full_name = $file_name . '.' . strtolower($file->getClientOriginalExtension());
//        $file_full_path = $file_path . "/" . $file_full_name;
//        if ($folder == "storage") {
//            $file->move(storage_path($file_path), $file_full_name);
//        } elseif ($folder == "public") {
//            $file->move(public_path($file_path), $file_full_name);
//        }
//        if(isValidValue($table)!=true){
//            $table=getCurrentStructure('table');
//        }
//        //////////
//        $file_storage = new \App\Models\file_storage();
//        $file_storage->name = $file_full_name;
//        $file_storage->original_name = $file->getClientOriginalName();
//        $file_storage->mime_type = $file->getClientMimeType();
//        $file_storage->original_extension = strtolower($file->getClientOriginalExtension());
//        $file_storage->size = $file->getSize();
//        $file_storage->path = $file_full_path;
//        $file_storage->folder = $folder;
//        $file_storage->attach_table = $table;
//        $file_storage->attach_id = $attach_id;
//        $file_storage->date = verta()->now()->format('Y-m-d');
//        $file_storage->time = verta()->now()->format('H:i:s');
//        $file_storage->timestamp = verta()->now()->timestamp;
//        $file_storage->save();
//        ///////////
//        return $file_full_name;
//    } else {
//        return null;
//    }
//
//}


function delete_file($folder, $path)
{
    if ($folder == "storage") {
        \Illuminate\Support\Facades\File::delete(storage_path($path));

    } elseif ($folder == "public") {
        \Illuminate\Support\Facades\File::delete(public_path($path));
    }

}

function getPublicAsset($path)
{
    return route('admin.get_public_file', ['path' => $path]);
}

function getJsonStorageData($name)
{
    return json_decode(getProperty(\App\Models\json_storage::where('name', $name)->first(), "data"));
}


function getLisRoutesAdminMenu($lis)
{
    $out = [];
    foreach ($lis as $li) {
        $route = getProperty($li, 'route');
        if (isValidValue($route)) {
            array_push($out, $route);
        }
    }
    return $out;
}

function getAdminController($section_name)
{
    $path = base_path('app/Http/Controllers/admin/');
    if (file_exists($path . $section_name . ".php")) {
        return "App\Http\Controllers\admin\\" . $section_name;
    } else {
        return "App\Http\Controllers\GeneralController";
    }
}


function setDefaultValueObject(&$object, $key, $value)
{
    if (isValidValue(getProperty($object, $key)) != true) {
        $object->$key = $value;
    }
}


function getStructureFromFile($name)
{
    $path = base_path('app/Structures/' . $name . '.json');
    if (file_exists($path)) {
        return json_decode(file_get_contents($path));
    } else {
        return null;
    }
}

function initStructure($name)
{
    $structure = getStructureFromFile($name);
    ///////////////
    setDefaultValueObject($structure, "main_name", $name);
    /////////////
    setDefaultValueObject($structure, "view_path_all", "admin.vendors.sections.all");
    setDefaultValueObject($structure, "view_path_add", "admin.vendors.sections.add");
    setDefaultValueObject($structure, "view_path_edit", "admin.vendors.sections.edit");
    setDefaultValueObject($structure, "view_path_single", "admin.vendors.sections.single");
    ////////////
    setDefaultValueObject($structure, "item_name", "ایتم");
    setDefaultValueObject($structure, "item_name_sum", "ایتم ها");
    ////////////
    setDefaultValueObject($structure, "route_name", "admin." . $name);
    /////////////
    return $structure;
}


function getCurrentStructure($properties = null)
{
    $structure = getProperty(Illuminate\Support\Facades\Route::getCurrentRoute()->getController(), 'structure');
    if (isValidValue($properties)) {
        return getProperty($structure, $properties);
    }
    return $structure;
}


function getStructureNameFromRoute($route_name = null)
{
    if (isValidValue($route_name) != true) {
        $route_name = Illuminate\Support\Facades\Route::getCurrentRoute()->getName();
    }
    $route_name = str_replace('admin.', "", $route_name);
    return explode("_", $route_name)[0];
}

////////////


function jsonToObject($json)
{
    $res = json_decode($json);
    if ($res == "" or $res == null or is_numeric($res)) {
        return [];
    }
    return $res;
}

function sumNumbersArray($str)
{
    $res = 0;
    $numbers = strToArray($str);
    foreach ($numbers as $number) {
        $res += $number;
    }
    return $res;
}

function appendTextFile($path, $text, $endLine = "\n")
{
    $myfile = fopen($path, "a+") or die("Unable to open file!");
    fwrite($myfile, $text . $endLine);
    fclose($myfile);
}

function getAdminComponent($name)
{
    return 'admin.pages.' . getCurrentStructure("main_name") . "." . $name;
}

function existAdminComponent($name)
{
    $name = str_replace('.', '/', $name);
    if (file_exists(base_path('resources/views/admin/pages/' . getCurrentStructure("main_name") . "/" . $name . ".blade.php"))) {
        return true;
    } else {
        return false;
    }

}

function getBasicAdminRoutes($section_name, $class_path = null)
{
    if (isValidValue($class_path) != true) {
        $class_path = getAdminController($section_name);
    }
    \Illuminate\Support\Facades\Route::prefix('/')->middleware("generalMiddleware")->group(function () use ($section_name, $class_path) {
        $route_name = "admin." . $section_name;
        \Illuminate\Support\Facades\Route::get("/", $class_path . "@all")->name($route_name);
        \Illuminate\Support\Facades\Route::get("/add", $class_path . "@add")->name($route_name . "_add");
        \Illuminate\Support\Facades\Route::post("/add-submit", $class_path . "@add_submit")->name($route_name . "_add_submit");
        \Illuminate\Support\Facades\Route::get("/edit/{id}", $class_path . "@edit")->name($route_name . "_edit");
        \Illuminate\Support\Facades\Route::post("/edit-submit/{id}", $class_path . "@edit_submit")->name($route_name . "_edit_submit");
        \Illuminate\Support\Facades\Route::get("/delete", $class_path . "@delete")->name($route_name . "_delete");
        \Illuminate\Support\Facades\Route::get("/multiple-delete", $class_path . "@multiple_delete")->name($route_name . "_multiple_delete");
        //////////////
        \Illuminate\Support\Facades\Route::post("/edit-single-field", $class_path . "@edit_single_field")->name($route_name . "_edit_single_field");
        \Illuminate\Support\Facades\Route::get("/single/{id}", $class_path . "@single")->name($route_name . "_single");
    });
    return $class_path;
}

function getGeneralModel($model_name = null)
{

    if (isValidValue($model_name) != true) {
        $model_name = getCurrentStructure(['table']);
    }
    return new \App\Models\GeneralModel($model_name);
}

function getItemId($item)
{
    $primary_key = getCurrentStructure(['primary_key']);
    if (isValidValue($primary_key) != true) {
        $primary_key = "id";
    }
    return $item->$primary_key;
}


function getAdminRoutesPermissions()
{
    $out = (array)getJSONFile('admin_permissions');
    $routes = \Illuminate\Support\Facades\Route::getRoutes()->getRoutes();
    foreach ($routes as $route) {
        $route_name = $route->getName();
        if (str_contains($route_name, "admin.")) {
            if (isValidValue(getProperty($out, $route_name))) {
                break;
            }
            $main_name = getStructureNameFromRoute($route_name);
            $name_fa = getProperty(getStructureFromFile($main_name), "template_title");
            if (isValidValue($name_fa) == true) {
                if ("admin." . $main_name == $route_name) {
                    $name_fa .= ' - ' . " مشاهده همه ";
                } elseif ("admin." . $main_name . "_add" == $route_name) {
                    $name_fa .= ' - ' . "افزودن" . " - " . "مشاهده";
                } elseif ("admin." . $main_name . "_add_submit" == $route_name) {
                    $name_fa .= ' - ' . "افزودن" . " - " . "ثبت اطلاعات";
                } elseif ("admin." . $main_name . "_edit" == $route_name) {
                    $name_fa .= ' - ' . "ویرایش" . " - " . "مشاهده";
                } elseif ("admin." . $main_name . "_edit_submit" == $route_name) {
                    $name_fa .= ' - ' . "ویرایش" . " - " . "ثبت اطلاعات";
                } elseif ("admin." . $main_name . "_delete" == $route_name) {
                    $name_fa .= ' - ' . "حذف";
                } elseif ("admin." . $main_name . "_multiple_delete" == $route_name) {
                    $name_fa .= ' - ' . "حذف دست جمعی";
                } elseif ("admin." . $main_name . "_edit_single_field" == $route_name) {
                    $name_fa .= ' - ' . "ویرایش یک فیلد خاص";
                } elseif ("admin." . $main_name . "_single" == $route_name) {
                    $name_fa .= ' - ' . "صفحه تکی";
                } else {
                    $name_fa = str_replace('admin.', '', $route_name);
                }
            } else {
                $name_fa = str_replace('admin.', '', $route_name);
            }
            $out[$route_name] = ['name_fa' => $name_fa];
        }
    }
    return $out;
}

function viewExist($path)
{
    return file_exists(base_path('resources/views/' . $path . ".blade.php"));
}
