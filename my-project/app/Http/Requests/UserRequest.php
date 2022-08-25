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

        switch ($this->method()) {
            case 'POST': {
                    $user = $this->user();
                    return [
                        'name' => 'required',
                        'email' => 'required|email|unique:users',
                        'password' => [Rules\Password::defaults(), 'required'],
                        'is_admin' =>  ['boolean', Rule::prohibitedIf($user == null || $user->is_admin == false)]
                    ];
                }
            case 'PUT':
            case 'PATCH': {
                    return [
                        'password' => Rules\Password::defaults(),
                        'email' => Rule::unique('users')->ignore($this->route()->user->id),
                        'is_admin' =>  ['boolean', Rule::prohibitedIf($this->user()->is_admin == false)] // we always expect a user on PUT/PATCH
                    ];
                }
            default:
                break;
        }
    }
}
