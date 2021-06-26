<?php

namespace App\Models;

use App\Models\Traits\UploadFiles;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Video extends Model
{
    use HasFactory, SoftDeletes, Uuid, UploadFiles;
    public $incrementing = false;
    protected $keyType = 'string';

    const RATING_LIST = [
        'L', '10', '12', '14', '16', '18'
    ];
    const THUMB_FILE_MAX_SIZE = 1024 * 5; // 5MB
    const BANNER_FILE_MAX_SIZE = 1024 * 10; // 10MB
    const TRAILER_FILE_MAX_SIZE = 1024 * 100; // 100MB
    const VIDEO_FILE_MAX_SIZE = 1024 * 50; // 50MB

    protected $fillable = [
        'title',
        'description',
        'opened',
        'rating',
        'duration',
        'year_launched',
        'video_file',
        'thumb_file',
    ];

    protected $dates = [
        'deleted_at',
    ];

    protected $casts = [
        'id' => 'string',
        'opened' => 'boolean',
        'year_launched' => 'integer',
        'duration' => 'integer',
    ];

    public static function create(array $attributes = [])
    {
        $files = self::extractFiles($attributes);
        try {
            DB::beginTransaction();

            /** @var Video $video */
            $video = static::query()->create($attributes);
            static::handleRelations($video, $attributes);
            $video->uploadFiles($files);

            DB::commit();

            return $video;
        } catch (\Exception $exception) {
            if(isset($video)) {
                $video->deleteFiles($files);
            }

            DB::rollBack();
            throw $exception;
        }
    }

    public function update(array $attributes = [], array $options = [])
    {
        $files = self::extractFiles($attributes);
        try {
            DB::beginTransaction();
            $saved = parent::update($attributes, $options);
            static::handleRelations($this, $attributes);
            if($saved) {
                $this->uploadFiles($files);
            }
            DB::commit();

            if ($saved && count($files)) {
                $this->deleteOldFiles();
            }

            return $saved;
        } catch (\Exception $exception) {
            $this->deleteFiles($files);
            DB::rollBack();
            throw $exception;
        }
    }

    public static function handleRelations(Video &$video, array $attributes)
    {
        if (isset($attributes['categories_id'])) {
            $video->categories()->sync($attributes['categories_id']);
        }

        if (isset($attributes['genres_id'])) {
            $video->genres()->sync($attributes['genres_id']);
        }
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTrashed();
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class)->withTrashed();
    }

    protected function uploadDir(): string
    {
        return $this->id;
    }

    public static function fileFields(): array
    {
        return [
            'video_file',
            'thumb_file',
        ];
    }

    public function getThumbFileUrlAttribute(): ?string
    {
        return $this->thumb_file ? $this->getFileUrl($this->thumb_file) : null;
    }

    public function getBannerFileUrlAttribute(): ?string
    {
        return $this->banner_file ? $this->getFileUrl($this->banner_file) : null;
    }

    public function getTrailerFileUrlAttribute(): ?string
    {
        return $this->trailer_file ? $this->getFileUrl($this->trailer_file) : null;
    }

    public function getVideoFileUrlAttribute(): ?string
    {
        return $this->video_file ? $this->getFileUrl($this->video_file) : null;
    }
}
