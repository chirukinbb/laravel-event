<?php

namespace Modules\Events\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $search
 * @property int[] $points
 * @property int[] $dates
 * @property int[] $categories
 * @property int[] $countries
 * @property int[] $authors
 * @property int[] $tags
 */
class EventListRequest extends FormRequest
{
    public function rules()
    {
        return [
            'search' => 'string',
            'points' => 'array',
            'dates' => 'array',
            'categories' => 'array',
            'countries' => 'array',
            'authors' => 'array',
            'tags' => 'array'
        ];
    }
}
