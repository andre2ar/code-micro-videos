<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;
use App\Rules\GenresHasCategoriesRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'categories_id' => [
                'required',
                'array',
                'exists:categories,id,deleted_at,NULL',
            ],
            'genres_id' => [
                'required',
                'array',
                'exists:genres,id,deleted_at,NULL',
            ],
        ];
    }

    public function store(Request $request)
    {
        $this->addRuleIfGenresHasCategories($request);
        $validated = $request->validate($this->rulesStore());

        $self = $this;
        $video = DB::transaction(function () use (&$request, &$validated, &$self) {
            /** @var Video $video */
            $video = $this->model()::create($validated);
            $self->handleRelations($video, $request);

            return $video;
        });

        return $video->refresh();
    }

    public function update(Request $request, $id)
    {
        $this->addRuleIfGenresHasCategories($request);
        $validated = $request->validate($this->rulesUpdate());

        /** @var Video $video */
        $video = $this->model()::findOrFail($id);

        $self = $this;
        DB::transaction(function () use (&$video, &$request, &$validated, &$self) {
            $video->update($validated);
            $self->handleRelations($video, $request);
        });

        return $video;
    }

    protected function addRuleIfGenresHasCategories(Request $request)
    {
        if(!is_array($request->categories_id)) {
            return;
        }
        $this->rules['genres_id'][] = new GenresHasCategoriesRule($request->categories_id);
    }

    protected function handleRelations(Video &$video, Request &$request)
    {
        $video->categories()->sync($request->categories_id);
        $video->genres()->sync($request->genres_id);
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
