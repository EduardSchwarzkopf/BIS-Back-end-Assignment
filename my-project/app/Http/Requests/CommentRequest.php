<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
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
            'post_id'  => 'required_if:post_id,=,null|int',
            'content'  => 'required_if:content,=,null|string|max:254'
        ];
    }

    public function filters()
    {
        return [
            'content' => 'trim|escape'
        ];
    }
}
