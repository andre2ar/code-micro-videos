<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\VideoResource;
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
            'thumb_file' => 'image|max:'.Video::THUMB_FILE_MAX_SIZE,
            'banner_file' => 'image|max:'.Video::BANNER_FILE_MAX_SIZE,
            'trailer_file' => 'mimetypes:video/mp4|max:'.Video::TRAILER_FILE_MAX_SIZE,
            'video_file' => 'mimetypes:video/mp4|max:'.Video::VIDEO_FILE_MAX_SIZE
        ];
    }

    public function store(Request $request)
    {
        $this->addRuleIfGenresHasCategories($request);
        $validated = $request->validate($this->rulesStore());

        $video = $this->model()::create($validated);

        $video->refresh();

        $resource = $this->resource();
        return new $resource($video);
    }

    public function update(Request $request, $id)
    {
        $this->addRuleIfGenresHasCategories($request);
        $validated = $request->validate($this->rulesUpdate());

        /** @var Video $video */
        $video = $this->model()::findOrFail($id);
        $video->update($validated);

        $resource = $this->resource();
        return new $resource($video);
    }

    protected function addRuleIfGenresHasCategories(Request $request)
    {
        if(!is_array($request->categories_id)) {
            return;
        }
        $this->rules['genres_id'][] = new GenresHasCategoriesRule($request->categories_id);
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

    protected function resourceCollection()
    {
        return $this->resource();
    }

    protected function resource()
    {
        return VideoResource::class;
    }
}
