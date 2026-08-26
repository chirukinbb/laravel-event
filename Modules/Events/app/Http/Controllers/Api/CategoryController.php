<?php

namespace Modules\Events\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Events\Http\Resource\CategoryResource;
use Modules\Events\Http\Resource\TagResource;
use Modules\Events\Models\Category;
use Modules\Events\Models\Tag;

class CategoryController extends Controller
{
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $categories = Category::all();

        return CategoryResource::collection($categories);
    }

    public function tags(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $tags = Tag::all();

        return TagResource::collection($tags);
    }
}