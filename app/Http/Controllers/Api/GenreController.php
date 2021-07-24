<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\GenreResource;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenreController extends BasicCrudController
{
    private array $rules = [
        'name' => 'required|max:255',
        'is_active' => 'boolean',
        'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL'
    ];

    public function store(Request $request)
    {
        $validated = $request->validate($this->rulesStore());

        $self = $this;
        $genre = DB::transaction(function () use (&$request, &$validated, &$self) {
            /** @var Genre $genre */
            $genre = $this->model()::create($validated);
            $self->handleRelations($genre, $request);

            return $genre;
        });

        $genre->refresh();

        $resource = $this->resource();
        return new $resource($genre);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate($this->rulesUpdate());

        /** @var Genre $genre */
        $genre = $this->model()::findOrFail($id);

        $self = $this;
        DB::transaction(function () use (&$genre, &$request, &$validated, &$self) {
            $genre->update($validated);
            $self->handleRelations($genre, $request);
        });

        $resource = $this->resource();
        return new $resource($genre);
    }

    protected function handleRelations(Genre &$genre, Request &$request) {
        $genre->categories()->sync($request->categories_id);
    }

    protected function model()
    {
        return Genre::class;
    }

    protected function rulesStore(): array
    {
        return $this->rules;
    }

    protected function rulesUpdate(): array
    {
        return $this->rules;
    }

    protected function resourceCollection()
    {
        return $this->resource();
    }

    protected function resource()
    {
        return GenreResource::class;
    }
}
