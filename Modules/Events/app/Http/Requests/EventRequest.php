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
    {// Базовые правила для slots в зависимости от HTTP-метода
        $slotsRules = ['numeric'];

        if ($this->isMethod('post')) {
            // При POST число мест должно быть строго больше 0
            $slotsRules[] = 'min:1';
        } elseif ($this->isMethod('patch') || $this->isMethod('put')) {
            // Получаем модель события из роута (например, /events/{event})
            $event = $this->route('event');

            if ($event) {
                // Загружаем актуальное количество участников
                $membersCount = $event->members_count ?? $event->loadCount('members')->members_count;

                // Поле slots не может быть меньше текущего кол-ва участников
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
            'planing_time' => 'required|date_format:U'
        ];
    }
}
