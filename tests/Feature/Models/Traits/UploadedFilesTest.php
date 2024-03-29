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
        Storage::fake();
    }

    public function testUploadFile()
    {
        $file = UploadedFile::fake()->create('video.mp4');
        $this->uploadFileStub->uploadFile($file);
        Storage::assertExists("1/{$file->hashName()}");
    }

    public function testUploadFiles()
    {
        $file1 = UploadedFile::fake()->create('video1.mp4');
        $file2 = UploadedFile::fake()->create('video2.mp4');
        $this->uploadFileStub->uploadFiles([
            $file1,
            $file2
        ]);

        Storage::assertExists("1/{$file1->hashName()}");
        Storage::assertExists("1/{$file2->hashName()}");
    }

    public function testDeleteOldFiles()
    {
        Storage::fake();
        $file1 = UploadedFile::fake()->create('video1.mp4')->size(1);
        $file2 = UploadedFile::fake()->create('video2.mp4')->size(1);
        $this->uploadFileStub->uploadFiles([$file1, $file2]);
        $this->uploadFileStub->deleteOldFiles();
        $this->assertCount(2, Storage::allFiles());

        $this->uploadFileStub->oldFiles = [$file1->hashName()];
        $this->uploadFileStub->deleteOldFiles();
        Storage::assertMissing("1/{$file1->hashName()}");
        Storage::assertExists("1/{$file2->hashName()}");
    }

    public function testMakeOldFieldsOnSaving()
    {
        UploadFileStub::dropTable();
        UploadFileStub::makeTable();

        $this->uploadFileStub->fill([
            'name' => 'test',
            'file1' => 'test1.mp4',
            'file2' => 'test2.mp4'
        ]);
        $this->uploadFileStub->save();

        $this->assertCount(0, $this->uploadFileStub->oldFiles);

        $this->uploadFileStub->update([
            'name' => 'test_name',
            'file2' => 'test3.mp4'
        ]);

        $this->assertEqualsCanonicalizing(['test2.mp4'], $this->uploadFileStub->oldFiles);
    }

    public function testMakeOldFilesNullOnSaving() {
        UploadFileStub::dropTable();
        UploadFileStub::makeTable();

        $this->uploadFileStub->fill([
            'name' => 'test',
        ]);
        $this->uploadFileStub->save();

        $this->uploadFileStub->update([
            'name' => 'test_name',
            'file2' => 'test3.mp4'
        ]);

        $this->assertEqualsCanonicalizing([], $this->uploadFileStub->oldFiles);
    }

    public function testDeleteFile()
    {
        $file = UploadedFile::fake()->create('video.mp4');
        $this->uploadFileStub->uploadFile($file);
        $this->uploadFileStub->deleteFile($file->hashName());
        Storage::assertMissing("1/{$file->hashName()}");

        $file = UploadedFile::fake()->create('video.mp4');
        $this->uploadFileStub->uploadFile($file);
        $this->uploadFileStub->deleteFile($file);
        Storage::assertMissing("1/{$file->hashName()}");
    }

    public function testDeleteFiles()
    {
        $file1 = UploadedFile::fake()->create('video1.mp4');
        $file2 = UploadedFile::fake()->create('video2.mp4');
        $this->uploadFileStub->deleteFiles([
            $file1,
            $file2->hashName()
        ]);

        Storage::assertMissing("1/{$file1->hashName()}");
        Storage::assertMissing("1/{$file2->hashName()}");
    }

    public function testExtractFiles()
    {
        $attributes = [];
        $files = UploadFileStub::extractFiles($attributes);
        $this->assertCount(0, $attributes);
        $this->assertCount(0, $files);

        $attributes = [
            'file1' => 'test'
        ];
        $files = UploadFileStub::extractFiles($attributes);
        $this->assertCount(1, $attributes);
        $this->assertCount(0, $files);

        $attributes = [
            'file1' => 'test',
            'file2' => 'test',
        ];
        $files = UploadFileStub::extractFiles($attributes);
        $this->assertCount(2, $attributes);
        $this->assertCount(0, $files);

        $file1 = UploadedFile::fake()->create('video1.mp4');
        $attributes = [
            'file1' => $file1,
            'file2' => 'test',
        ];
        $files = UploadFileStub::extractFiles($attributes);
        $this->assertCount(2, $attributes);
        $this->assertEquals([
            'file1' => $file1->hashName(),
            'file2' => 'test',
        ], $attributes);
        $this->assertCount(1, $files);
        $this->assertEquals([$file1], $files);

        $file2 = UploadedFile::fake()->create('video2.mp4');
        $attributes = [
            'file1' => $file1,
            'file2' => $file2,
            'other' => 'test'
        ];
        $files = UploadFileStub::extractFiles($attributes);
        $this->assertCount(3, $attributes);
        $this->assertEquals([
            'file1' => $file1->hashName(),
            'file2' => $file2->hashName(),
            'other' => 'test'
        ], $attributes);
        $this->assertCount(2, $files);
        $this->assertEquals([$file1, $file2], $files);
    }
}
