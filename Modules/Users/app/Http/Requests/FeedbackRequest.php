<?php

namespace Modules\Users\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FeedbackRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'text' => 'required|string'
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}