<?php

namespace Modules\Users\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    public function rules()
    {
        return [
            'address' => 'string|required',
            'radius' => 'numeric|required',
            'categories' => 'array|required|min:1',
        ];
    }
}
