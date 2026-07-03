<?php

namespace Modules\Events\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'parent_comment_id',
        'content'
    ];

    public function author()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function childComments()
    {
        return $this->belongsToMany(self::class, 'comment', 'parent_comment_id', 'parent_comment_id');
    }

    public function parentComment()
    {
        return $this->hasOne(self::class, 'id', 'parent_comment_id');
    }
}
