<?php


namespace Tests\Stubs\Models;


use App\Models\Traits\UploadFiles;
use Illuminate\Database\Eloquent\Model;

class UploadFileStub extends Model
{
    use UploadFiles;

    protected function uploadDir(): string {
        return '1';
    }
}
