<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class adminLoginReq extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "username" => 'required|max:100',
            "password" => 'required|min:8|max:100',
        ];
    }
    public function messages()
    {
        return [
            "email.required"=>"نام کاربری به درستی وارد نشده است",
            "email.email"=>"نام کاربری به درستی وارد نشده است",
            "email.max" => "نام کاربری به درستی وارد نشده است",
            "password.required"=>"گذرواژه به درستی وارد نشده است",
            "password.min"=>"گذرواژه به درستی وارد نشده است",
            "password.max"=>"گذرواژه به درستی وارد نشده است",

        ];
    }
}
