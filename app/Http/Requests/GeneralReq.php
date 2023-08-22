<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


class GeneralReq extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    private $rules;
    private $messages;

    public function __construct()
    {
        $rules = [];
        $messages = [];
        $fields = getCurrentStructure('fields');
        foreach ($fields as $field_key => $field_value) {
            $method = Route::getCurrentRoute()->getActionMethod();
            if (getProperty($field_value, ["requests", $method, 'status']) === true) {
                $types = getProperty($field_value, ["requests", $method, 'types']);
                $rules[$field_key] = $types;
                $name_fa = getProperty($field_value, 'name_fa');
                foreach (explode("|", $types) as $type) {
                    $messages[$field_key . '.' . explode(':', $type)[0]] =  $name_fa . " وارد نشده است یا به درستی وارد نشده است. ";
                }
            }
        }
        $this->rules = $rules;
        $this->messages = $messages;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return $this->rules;
    }

    public function messages()
    {
        return $this->messages;
    }

}
