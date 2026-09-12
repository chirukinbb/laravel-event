<?php

namespace Modules\Chat\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MessageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'content' => 'required|string'
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}