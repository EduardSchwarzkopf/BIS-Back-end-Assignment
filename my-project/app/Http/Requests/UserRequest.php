<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;


class UserRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {

        $user = $this->user();
        return [
            'name' => 'required_if:content,=,null|string',
            'email' => 'required_if:content,=,null|string|email|unique:users',
            'password' => [Rules\Password::defaults(), 'required_if:content,=,null|string'],
            'is_admin' =>  ['boolean', Rule::prohibitedIf($user == null || $user->is_admin == false)]
        ];
    }
}
