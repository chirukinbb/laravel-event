<?php

namespace Modules\Events\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FeedbackRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'is_happened' => 'required|boolean',
            'comment' => 'required|string',
            'mark' => 'required|integer|min:0|max:10'
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}