<?php

namespace Modules\Users\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $fillable = [
        'user_id', 'text'
    ];
    protected $table = 'feedbacks';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}