<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private $rules = [
        'name' => 'required|max:255',
        'is_active' => 'boolean'
    ];

    public function index()
    {
        return Category::all();
    }

    public function store(Request $request): Category
    {
        $request->validate($this->rules);

        $category = Category::create($request->all());
        return $category->refresh();
    }

    public function show(Category $category): Category
    {
        return $category;
    }

    public function update(Request $request, Category $category): Category
    {
        $request->validate($this->rules);
        $category->update($request->all());

        return $category;
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->noContent();
    }
}
