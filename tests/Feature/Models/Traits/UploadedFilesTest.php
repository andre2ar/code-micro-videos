<?php

namespace Tests\Feature\Models\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Stubs\Models\UploadFileStub;
use Tests\TestCase;

class UploadedFilesTest extends TestCase
{
    private UploadFileStub $uploadFileStub;
    protected function setUp(): void
    {
        parent::setUp();
        $this->uploadFileStub = new UploadFileStub();
    }

    public function testUploadFile()
    {
        Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4');
        $this->uploadFileStub->uploadFile($file);
        Storage::assertExists("1/{$file->hashName()}");
    }

    public function testUploadFiles()
    {
        Storage::fake();
        $file1 = UploadedFile::fake()->create('video1.mp4');
        $file2 = UploadedFile::fake()->create('video2.mp4');
        $this->uploadFileStub->uploadFiles([
            $file1,
            $file2
        ]);

        Storage::assertExists("1/{$file1->hashName()}");
        Storage::assertExists("1/{$file2->hashName()}");
    }
}
