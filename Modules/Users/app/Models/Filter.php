<?php

namespace Modules\Users\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Events\Models\Category;

class Filter extends Model
{
    protected $fillable = ['user_id', 'center', 'radius', 'categories'];

    protected $casts = [
        'radius' => 'integer',
        'categories' => 'array',
        'center' => 'array',

    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): string
    {
        return Category::whereIn('id', $this->categories)->get()->map(function (Category $category) {
            return $category->title;
        })->join(',');
    }
}