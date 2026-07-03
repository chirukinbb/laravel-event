<?php

namespace Modules\Events\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Modules\Events\Models\Comment;
use Modules\Events\Models\Event;

class EventRepository
{
    protected Builder $eventQueryBuilder;

    public function __construct()
    {
        $this->eventQueryBuilder = Event::query();
    }

    public function getList(array $attrs)
    {
        foreach ($attrs as $key => $value) {
            if (method_exists($this, $key)) {
                call_user_func([$this, $key], $value);
            }
        }

        return $this->eventQueryBuilder->where('planing_time', '>=', Carbon::now()->timestamp * 1000)
            ->get();
    }

    protected function search(string $query)
    {
        $this->eventQueryBuilder->where('title', 'like', '%' . $query . '%')
            ->orWhere('title', 'like', '%' . $query . '%');
    }

    protected function countries(array $countries)
    {
        $this->eventQueryBuilder->whereIn('country_id', $countries);
    }

    protected function points(array $cities)
    {
        $this->eventQueryBuilder->whereIn('point_id', $cities);
    }

    protected function categories(array $categories)
    {
        $this->eventQueryBuilder->whereIn('category_id', $categories);
    }

    protected function authors(array $authors)
    {
        $this->eventQueryBuilder->whereIn('user_id', $authors);
    }

    protected function dates(array $dates)
    {
        $dates = array_map(function ($date) {
            return Carbon::parse($date / 1000)->toString();
        }, $dates);

        $this->eventQueryBuilder->whereBetween('planing_time', $dates);
    }

    protected function tags(array $tags)
    {
        $this->eventQueryBuilder->whereRelation('tags', function (Builder $builder) use ($tags) {
            $builder->whereIn('tags.id', $tags);
        });
    }

    public function get(int $id)
    {
        return Event::whereId($id)->first();
    }

    public function subscribe(int $eventId)
    {
        Event::whereId($eventId)->subscribers()->create(['user_id' => \Auth::id()]);
    }

    public function unsubscribe(int $eventId)
    {
        Event::whereId($eventId)->subscribers()->where('user_id', \Auth::id())->delete();
    }

    public function addComment(int $eventId, array $attrs)
    {
        Event::whereId($eventId)->comments->create([
            'user_id' => \Auth::id(),
            'content' => $attrs['content'],
            'parent_comment_id' => $attrs['parent_comment_id'] ?? 0
        ]);
    }

    public function editComment(int $commentId, string $content)
    {
        $comment = Event::whereId($commentId)->first();

        if ($comment->user_id === \Auth::id() && Comment::whereParentCommentId($commentId)->doesntExist()) {
            $comment->update(['content' => $content]);
        }
    }

    public function deleteComment(int $commentId)
    {
        $comment = Event::whereId($commentId)->first();

        if ($comment->user_id === \Auth::id() && Comment::whereParentCommentId($commentId)->doesntExist()) {
            $comment->delete();
        }
    }

    public function earlyEventDate()
    {
        return $this->eventQueryBuilder->min('planing_time');
    }

    public function latestEventDate()
    {
        return $this->eventQueryBuilder->max('planing_time');
    }
}
