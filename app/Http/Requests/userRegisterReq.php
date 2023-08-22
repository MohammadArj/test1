<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class userRegisterReq extends FormRequest
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
            "display_name" => 'required|max:250',
            "email" => 'required|email|max:250',
            "phone_number" => 'max:250',
            "password" => 'required|min:8|max:100',
            "password_repeat" => 'required|min:8|max:100',
        ];
    }
    public function messages()
    {
        return [
            "display_name.max" => "نام نمایشی به درستی وارد نشده است",
            "display_name.required"=>"نام نمایشی به درستی وارد نشده است",
            "email.required"=>"نام کاربری به درستی وارد نشده است",
            "email.email"=>"نام کاربری به درستی وارد نشده است",
            "email.max" => "نام کاربری به درستی وارد نشده است",
            "password.required"=>"گذرواژه به درستی وارد نشده است",
            "password.min"=>"گذرواژه به درستی وارد نشده است",
            "password.max"=>"گذرواژه به درستی وارد نشده است",
            "phone_number.max"=>"شماره همراه به درستی وارد نشده است",
            "password_repeat.required"=>"تایید گذرواژه  به درستی وارد نشده است",
            "password_repeat.min"=>"تایید گذرواژه به درستی وارد نشده است",
            "password_repeat.max"=>"تایید گذرواژه به درستی وارد نشده است ",
        ];
    }
}
