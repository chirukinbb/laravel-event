<?php

namespace Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Events\Http\Requests\CategoryRequest;
use Modules\Events\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::paginate(15);

        return view('events::categories.index', compact('categories'));
    }

    public function create()
    {
        return view('events::categories.create');
    }

    public function store(CategoryRequest $request)
    {
        $model = Category::getModel();

        $model->title = $request->input('title');

        $model->save();

        return redirect()->route('events::categories.index');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $category = Category::find($id);

        return view('events::categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, $id)
    {
        $model = Category::find($id);

        $model->title = $request->input('title');

        $model->save();

        return redirect()->route('events::categories.index');
    }

    public function destroy($id)
    {
        Category::find($id)->delete();

        return redirect()->route('events::categories.index');
    }
}
