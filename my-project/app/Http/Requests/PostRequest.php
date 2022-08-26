<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
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
        return [
            'subject' => 'required_if:content,=,null|string|max:64',
            'content'  => 'required_if:content,=,null|string'
        ];
    }

    public function filters()
    {
        return [
            'subject' => 'trim|escape',
            'content' => 'trim|escape'
        ];
    }
}
