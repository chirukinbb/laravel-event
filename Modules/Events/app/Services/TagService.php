<?php

namespace Modules\Events\Services;


use Modules\Events\Models\Event;
use Modules\Events\Models\Tag;

class TagService
{
    public function __construct(protected Event $event)
    {
        $this->event->tags()->detach();
    }

    public function action(array $tagNames)
    {
        foreach ($this->unique($tagNames) as $name) {
            $tag = $this->getTagModel($name);
            $this->save($tag);
        }
    }

    protected function unique(array $array)
    {
        return array_unique(array_map(function ($el) {
            return mb_strtolower($el);
        }, $array));
    }

    protected function getTagModel(string $name)
    {
        return Tag::firstOrCreate(['name' => $name]);
    }

    protected function save(Tag $tag)
    {
        $this->event->tags()->save($tag);
    }
}
