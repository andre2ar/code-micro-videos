<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations;
    public function testIndex()
    {
        $genre = Genre::factory()->create();
        $response = $this->get(route('genres.index'));

        $response->assertStatus(200)
            ->assertJson([$genre->toArray()]);
    }

    public function testShow()
    {
        $genre = Genre::factory()->create();
        $response = $this->get(route('genres.show', ['genre' => $genre->id]));

        $response->assertStatus(200)
            ->assertJson($genre->toArray());
    }

    public function testInvalidationData()
    {
        // Create
        $response = $this->json('POST', route('genres.store'), []);
        $this->assertInvalidationRequired($response);

        $response = $this->json('POST', route('genres.store'), [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);
        $this->assertInvalidationFields($response, ['name'], 'max.string', ['max' => 255]);
        $this->assertInvalidationFields($response, ['is_active'], 'boolean');

        // Update
        $genre = Genre::factory()->create();
        $response = $this->json('PUT', route('genres.update', ['genre' => $genre->id]), []);
        $this->assertInvalidationRequired($response);

        $response = $this->json('PUT', route('genres.update', ['genre' => $genre->id]), [
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
        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test',
        ]);
        $genre = Genre::find($response->json('id'));
        $response->assertStatus(201)
            ->assertJson($genre->toArray());
        $this->assertTrue($response->json('is_active'));
        $this->assertNull($response->json('description'));

        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test',
            'is_active' => false
        ]);
        $response->assertJsonFragment([
            'is_active' => false,
        ]);
    }

    public function testUpdate() {
        $genre = Genre::factory()->create([
            'is_active' => false
        ]);
        $response = $this->json('PUT', route('genres.update', [
            'genre' => $genre->id
        ]), [
            'name' => 'test',
            'is_active' => true
        ]);
        $genre = Genre::find($response->json('id'));
        $response->assertStatus(200)
            ->assertJson($genre->toArray())
            ->assertJsonFragment([
                'name' => 'test',
                'is_active' => true,
            ]);
    }

    public function testDestroy()
    {
        $genre = Genre::factory()->create();
        $response = $this->json('DELETE', route('genres.destroy', [
            'genre' => $genre->id,
        ]));
        $response->assertStatus(204);
        $this->assertNull(Genre::find($genre->id));
        $this->assertNotNull(Genre::withTrashed()->find($genre->id));
    }
}
