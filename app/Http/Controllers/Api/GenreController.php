<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    private $rules = [
        'name' => 'required|max:255',
        'is_active' => 'boolean'
    ];

    public function index()
    {
        return Genre::all();
    }

    public function store(Request $request): Genre
    {
        $request->validate($this->rules);
        $genre = Genre::create($request->all());
        return $genre->refresh();
    }

    public function show(Genre $genre): Genre
    {
        return $genre;
    }

    public function update(Request $request, Genre $genre): Genre
    {
        $request->validate($this->rules);
        $genre->update($request->all());

        return $genre;
    }

    public function destroy(Genre $genre)
    {
        $genre->delete();
        return response()->noContent();
    }
}
