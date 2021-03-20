<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations;
    public function testIndex()
    {
        $category = Category::factory()->create();
        $response = $this->get(route('categories.index'));

        $response->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    public function testShow()
    {
        $category = Category::factory()->create();
        $response = $this->get(route('categories.show', ['category' => $category->id]));

        $response->assertStatus(200)
            ->assertJson($category->toArray());
    }

    public function testInvalidationData()
    {
        $data = [
            'name' => ''
        ];
        $this->assertInvalidationInStoreAction($data, 'required');

        $data = [
            'name' => str_repeat('a', 256),
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);

        $data = [
            'is_active' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');

        // Update
        $category = Category::factory()->create();
        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), []);
        $this->assertInvalidationRequired($response);

        $response = $this->json('PUT', route('categories.update', ['category' => $category->id]), [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);
        $this->assertInvalidationFields($response, ['name'], 'max.string', ['max' => 255]);
        $this->assertInvalidationFields($response, ['is_active'], 'boolean');
    }

    private function assertInvalidationRequired(TestResponse $response) {
        $this->assertInvalidationFields($response, ['name'], 'required');
        $response->assertJsonMissingValidationErrors(['is_active']);
    }

    public function testStore() {
        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test',
        ]);
        $category = Category::find($response->json('id'));
        $response->assertStatus(201)
            ->assertJson($category->toArray());
        $this->assertTrue($response->json('is_active'));
        $this->assertNull($response->json('description'));

        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test',
            'description' => 'description',
            'is_active' => false
        ]);
        $response->assertJsonFragment([
            'is_active' => false,
            'description' => 'description'
        ]);
    }

    public function testUpdate() {
        $category = Category::factory()->create([
            'description' => 'description',
            'is_active' => false
        ]);
        $response = $this->json('PUT', route('categories.update', [
            'category' => $category->id
        ]), [
            'name' => 'test',
            'description' => 'test',
            'is_active' => true
        ]);
        $category = Category::find($response->json('id'));
        $response->assertStatus(200)
            ->assertJson($category->toArray())
            ->assertJsonFragment([
                'name' => 'test',
                'is_active' => true,
                'description' => 'test'
            ]);


        $response = $this->json('PUT', route('categories.update', [
            'category' => $category->id
        ]), [
            'name' => 'test',
            'description' => '',
        ]);
        $response->assertJsonFragment([
                'description' => null
            ]);
    }

    public function testDestroy()
    {
        $category = Category::factory()->create();
        $response = $this->json('DELETE', route('categories.destroy', [
            'category' => $category->id,
        ]));
        $response->assertStatus(204);
        $this->assertNull(Category::find($category->id));
        $this->assertNotNull(Category::withTrashed()->find($category->id));
    }

    protected function routeStore()
    {
        return route('categories.store');
    }
}
