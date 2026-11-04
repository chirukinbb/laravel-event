<?php

namespace Modules\Events\Http\Resource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Events\Models\Member;

class MemberResource extends JsonResource
{
    /**
     * @var Member
     */
    public $resource;

    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'comment' => $this->resource->comment,
            'mark' => $this->resource->mark
        ];
    }
}
