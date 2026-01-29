<?php

namespace Tests\Feature\Api;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->token = $user->createToken('test')->plainTextToken;
    }

    public function test_index_returns_tags(): void
    {
        Tag::factory()->count(2)->create();
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/tags');
        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_store_creates_tag(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/tags', ['name' => 'api']);
        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'api');
        $this->assertDatabaseHas('tags', ['name' => 'api']);
    }

    public function test_show_returns_tag(): void
    {
        $tag = Tag::factory()->create();
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/tags/' . $tag->id);
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $tag->id);
    }

    public function test_update_modifies_tag(): void
    {
        $tag = Tag::factory()->create(['name' => 'old']);
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson('/api/v1/tags/' . $tag->id, ['name' => 'new']);
        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'new');
    }

    public function test_destroy_deletes_tag(): void
    {
        $tag = Tag::factory()->create();
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/tags/' . $tag->id);
        $response->assertStatus(204);
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }
}
