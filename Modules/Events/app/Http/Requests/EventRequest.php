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
        $slotsRules = ['numeric'];

        if ($this->isMethod('post')) {
            $slotsRules[] = 'min:1';
        } elseif ($this->isMethod('patch') || $this->isMethod('put')) {
            $event = $this->route('event');

            if ($event) {
                $membersCount = $event->members_count ?? $event->loadCount('members')->members_count;

                $slotsRules[] = "min:{$membersCount}";
            }
        }

        return [
            'title' => 'required|string',
            'description' => 'required|string',
            'thumbnail' => 'required_without:thumbnail_url|file|mimes:webp|max:1024',
            'thumbnail_url' => 'required_without:thumbnail|string',
            'address' => 'required|array',
            'category_id' => 'required|numeric',
            'user_id' => 'numeric|exists:users,id',
            'slots' => $slotsRules,
            'tags' => 'array',
            'planing_time' => 'required|date_format:U',
            'condition' => 'required|in:' . implode(',', array_keys(config('events.conditions'))),
            'price' => 'numeric',
            'is_public' => 'required|in:0,1'
        ];
    }
}
