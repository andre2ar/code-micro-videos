<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BasicCrudController;
use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends BasicCrudController
{
    private array $rules;
    public function __construct()
    {
        $this->rules = [
            'title' => 'required|max:255',
            'description' => 'required',
            'year_launched' => 'required|date_format:Y',
            'opened' => 'boolean',
            'rating' => 'required|in:'.implode(',', Video::RATING_LIST),
            'duration' => 'required|integer',
            'categories_id' => 'required|array|exists:categories,id',
            'genres_id' => 'required|array|exists:genres,id',
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rulesStore());

        /** @var Video $video */
        $video = $this->model()::create($validated);
        $video->categories()->sync($request->categories_id);
        $video->genres()->sync($request->genres_id);

        return $video->refresh();
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->rulesUpdate());

        /** @var Video $video */
        $video = $this->model()::findOrFail($id);

        $video->update($request->all());

        $video->categories()->sync($request->categories_id);
        $video->genres()->sync($request->genres_id);

        return $video;
    }


    protected function model()
    {
        return Video::class;
    }

    protected function rulesStore(): array
    {
        return $this->rules;
    }

    protected function rulesUpdate(): array
    {
        return $this->rules;
    }
}
