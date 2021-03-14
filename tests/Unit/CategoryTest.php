<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    private Category $category;
    protected function setUp(): void
    {
        parent::setUp();
        $this->category = new Category();
    }

    public function testFillableAttributes()
    {
        $fillable = [
            'name',
            'description',
            'is_active'
        ];

        $this->assertEquals($fillable, $this->category->getFillable());
    }

    public function testIfUseTraits()
    {
        $traits = [
            HasFactory::class,
            SoftDeletes::class,
            Uuid::class
        ];
        $categoryTraits = array_keys(class_uses(Category::class));
        $this->assertEquals($traits, $categoryTraits);
    }

    public function testCastsAttribute()
    {
        $casts = [
            'deleted_at' => 'datetime'
        ];

        $this->assertEquals($casts, $this->category->getCasts());
    }

    public function testIncrementingAttribute()
    {
        $this->assertFalse($this->category->incrementing);
    }

    public function testDatesAttribute()
    {
        $dates = [
            'deleted_at',
            'created_at',
            'updated_at'
        ];

        $categoryDates = $this->category->getDates();
        foreach ($dates as $date) {
            $this->assertContains($date, $categoryDates);
        }

        $this->assertCount(count($dates), $categoryDates);
    }
}
