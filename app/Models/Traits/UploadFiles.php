<?php


namespace App\Models\Traits;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

trait UploadFiles
{
    public array $oldFiles = [];
    protected abstract function uploadDir(): string;
    protected static abstract function fileFields(): array;

    public static function bootUploadFiles()
    {
        static::updating(function(Model $model) {
            $fieldsUpdated = array_keys($model->getDirty());
            $filesUpdated = array_intersect($fieldsUpdated, self::fileFields());
            $filesFiltered = Arr::where($filesUpdated, function ($fileField) use ($model) {
                return $model->getOriginal($fileField);
            });
            $model->oldFiles = array_map(function ($fileField) use ($model) {
                return $model->getOriginal($fileField);
            }, $filesFiltered);
        });
    }

    /**
     * @param UploadedFile[] $files
     */
    public function uploadFiles(array $files): void
    {
        foreach ($files as $file) {
            $this->uploadFile($file);
        }
    }

    public function uploadFile(UploadedFile $file): void
    {
        $file->store($this->uploadDir());
    }

    public function deleteOldFiles()
    {
        $this->deleteFiles($this->oldFiles);
    }

    public function deleteFiles(array $files): void
    {
        foreach ($files as $file) {
            $this->deleteFile($file);
        }
    }

    public function deleteFile(string|UploadedFile $file): void
    {
        $filename = $file instanceof UploadedFile ? $file->hashName() : $file;
        Storage::delete("{$this->uploadDir()}/$filename");
    }

    public static function extractFiles(array &$attributes = []): array
    {
        $files = [];
        foreach (self::fileFields() as $file) {
            if(isset($attributes[$file]) && $attributes[$file] instanceof UploadedFile) {
                $files[] = $attributes[$file];
                $attributes[$file] = $attributes[$file]->hashName();
            }
        }

        return $files;
    }

    protected function getFileUrl(string $filename): string
    {
        return Storage::url("{$this->uploadDir()}/$filename");
    }
}
