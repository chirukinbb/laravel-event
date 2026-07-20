<?php

namespace Modules\Events\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Events\Http\Resource\CategoryResource;
use Modules\Events\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();

        return CategoryResource::collection($categories);
    }
}