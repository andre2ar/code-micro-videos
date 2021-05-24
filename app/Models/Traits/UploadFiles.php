<?php


namespace App\Models\Traits;


use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait UploadFiles
{
    protected abstract function uploadDir(): string;

    /**
     * @param UploadedFile[] $files
     */
    public function uploadFiles(array $files)
    {
        foreach ($files as $file) {
            $this->uploadFile($file);
        }
    }

    public function uploadFile(UploadedFile $file)
    {
        $file->store($this->uploadDir());
    }

    public function deleteFiles(array $files)
    {
        foreach ($files as $file) {
            $this->deleteFile($file);
        }
    }

    public function deleteFile(string|UploadedFile $file)
    {
        $filename = $file instanceof UploadedFile ? $file->hashName() : $file;
        Storage::delete("{$this->uploadDir()}/$filename");
    }
}
