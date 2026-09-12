<?php

namespace Modules\Chat\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Chat\Models\Message;
use Modules\Users\Http\Resources\ProfileResource;

class MessageResource extends JsonResource
{
    /**
     * @param Message $resource
     */
    public function __construct($resource)
    {
        $resource->loadMissing(['user.profile', 'chat.chatable']);

        parent::__construct($resource);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'content' => $this->resource->content,
            'author' => [
                'id' => $this->resource->user_id,
                'profile' => ProfileResource::make($this->resource->user->profile)
            ],
            'created_at' => Carbon::parse($this->resource->created_at)->timestamp,
        ];
    }
}
