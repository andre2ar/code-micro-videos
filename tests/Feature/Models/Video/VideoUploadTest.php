<?php


namespace Tests\Feature\Models\Video;


use App\Models\Video;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\Exceptions\TestException;

class VideoUploadTest extends BaseVideoTestCase
{
    public function testCreateWithFile()
    {
        Storage::fake();
        $video = Video::create(
            $this->data + [
                'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                'video_file' => UploadedFile::fake()->image('video.mp4')
            ]
        );
        Storage::assertExists("{$video->id}/{$video->thumb_file}");
        Storage::assertExists("{$video->id}/{$video->video_file}");
    }

    public function testCreateIfRollbackFile()
    {
        Storage::fake();
        Event::listen(TransactionCommitted::class, function () {
            throw new TestException();
        });
        $hasError = false;

        try {
            Video::create(
                $this->data + [
                    'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                    'video_file' => UploadedFile::fake()->image('video.mp4')
                ]
            );
        } catch (TestException $e) {
            $this->assertCount(0, Storage::allFiles());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testUpdateWithFiles()
    {
        Storage::fake();
        $video = Video::factory()->create();
        $thumbFile = UploadedFile::fake()->image('thumb.jpg');
        $videoFile = UploadedFile::fake()->create('video.mp4');
        $video->update($this->data + [
            'thumb_file' => $thumbFile,
            'video_file' => $videoFile
        ]);

        Storage::assertExists("{$video->id}/{$video->thumb_file}");
        Storage::assertExists("{$video->id}/{$video->video_file}");

        $newVideoFile = UploadedFile::fake()->image('video.mp4');
        $video->update($this->data + [
            'video_file' => $newVideoFile,
        ]);
        Storage::assertExists("{$video->id}/{$thumbFile->hashName()}");
        Storage::assertExists("{$video->id}/{$newVideoFile->hashName()}");
        Storage::assertMissing("{$video->id}/{$videoFile->hashName()}");
    }

    public function testUpdateIfRollbackFiles()
    {
        Storage::fake();
        $video = Video::factory()->create();
        Event::listen(TransactionCommitted::class, function () {
            throw new TestException();
        });

        $hasError = false;
        try {
            $video->update($this->data + [
                'video_file' => UploadedFile::fake()->create('video.mp4'),
                'thumb_file' => UploadedFile::fake()->image('thumb.jpg')
            ]);
        } catch (TestException $exception) {
            $this->assertCount(0, Storage::allFiles());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testFileUrlWithLocalDriver()
    {
        $fileFields = [];
        foreach (Video::fileFields() as $field) {
            $fileFields[$field] = "$field.test";
        }

        $video = Video::factory()->create($fileFields);
        $localDriver = config('filesystems.default');
        $baseUrl = config('filesystems.disks.' . $localDriver)['url'];

        foreach ($fileFields as $field => $value) {
            $fileUrl = $video->{"{$field}_url"};
            $this->assertEquals("{$baseUrl}/$video->id/$value", $fileUrl);
        }
    }

    public function testFileUrlsIfNullWhenFiledAreNull()
    {
        $video = Video::factory()->create();
        foreach (Video::fileFields() as $field) {
            $fileUrl = $video->{"{$field}_url"};
            $this->assertNull($fileUrl);
        }
    }
}
