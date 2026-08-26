<?php

namespace Modules\Events\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

/**
 * @property string $title
 * @property string $description
 * @property UploadedFile $thumbnail
 * @property string $address
 * @property int $category_id
 * @property int $slots
 * @property int|null $user_id
 * @property string[] $tags
 * @property int $planing_time
 */
class EventRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required|string',
            'description' => 'required|string',
            'thumbnail' => 'required_without:thumb_path|file|mimes:webp|max:1024',
            'thumbnail_url' => 'required_without:thumbnail|string',
            'address' => 'required|array',
            'category_id' => 'required|numeric',
            'user_id' => 'numeric|exists:users,id',
            'slots' => 'numeric',
            'tags' => 'array',
            'planing_time' => 'required|date_format:U'
        ];
    }
}
