<?php

namespace Modules\Users\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'country_phone_code' => 'required|string|max:255',
            'country_phone_iso' => 'required|string|max:255',
            'languages' => 'required|array',
            'bio' => 'required|string'
        ];
    }
}
