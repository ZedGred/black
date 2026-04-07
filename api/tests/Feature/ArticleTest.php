<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    protected string $baseUrl = '/api/v1';
    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Spatie Permission adds primaryKey to $guarded, so we insert via DB directly
        $permissionNames = [
            'articles.create', 'articles.update', 'articles.delete',
            'articles.like', 'comments.create', 'comments.update',
            'comments.delete', 'comments.like',
        ];

        $now = now()->toDateTimeString();
        $permissionTableName = config('permission.table_names.permissions');
        $roleTableName       = config('permission.table_names.roles');

        $permissionRecords = [];
        $permissionUUIDs   = [];
        foreach ($permissionNames as $name) {
            $uuid = (string) \Illuminate\Support\Str::uuid();
            $permissionUUIDs[$name] = $uuid;
            $permissionRecords[] = [
                'id'         => $uuid,
                'name'       => $name,
                'guard_name' => 'api',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        \Illuminate\Support\Facades\DB::table($permissionTableName)->insert($permissionRecords);

        $roleUUID = (string) \Illuminate\Support\Str::uuid();
        \Illuminate\Support\Facades\DB::table($roleTableName)->insert([
            'id'         => $roleUUID,
            'name'       => 'writer',
            'guard_name' => 'api',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Sync permissions to role via pivot
        $roleHasPermissionsTable = config('permission.table_names.role_has_permissions');
        // Spatie config has null pivot keys — default column names are 'permission_id' and 'role_id'
        $rolePermissionsRecords = [];
        foreach ($permissionUUIDs as $uuid) {
            $rolePermissionsRecords[] = [
                'permission_id' => $uuid,
                'role_id'       => $roleUUID,
            ];
        }
        \Illuminate\Support\Facades\DB::table($roleHasPermissionsTable)->insert($rolePermissionsRecords);

        // Refresh permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->user = User::factory()->create();
        $this->user->assignRole('writer');
        $this->token = auth('api')->login($this->user);
    }

    // ================================================
    // Index (public)
    // ================================================

    public function test_anyone_can_list_published_articles(): void
    {
        Article::factory()->count(3)->create([
            'user_id'      => $this->user->id,
            'status'       => 'published',
            'published_at' => now(),
        ]);

        $response = $this->getJson("{$this->baseUrl}/articles");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['data']);
    }

    public function test_index_does_not_return_drafts(): void
    {
        Article::factory()->create([
            'user_id' => $this->user->id,
            'status'  => 'draft',
        ]);

        $response = $this->getJson("{$this->baseUrl}/articles");
        $response->assertStatus(200);

        $articles = $response->json('data.data');
        $this->assertEmpty($articles);
    }

    // ================================================
    // Store
    // ================================================

    public function test_authenticated_user_can_create_a_draft(): void
    {
        $response = $this->withToken($this->token)->postJson("{$this->baseUrl}/articles", [
            'title'   => 'My Draft',
            'content' => 'Draft content here',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'draft');
    }

    public function test_authenticated_user_can_publish_article_directly(): void
    {
        $response = $this->withToken($this->token)->postJson("{$this->baseUrl}/articles", [
            'title'   => 'My Published Article',
            'content' => 'Published content',
            'status'  => 'published',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'published');
    }

    public function test_create_article_fails_with_missing_title(): void
    {
        $response = $this->withToken($this->token)->postJson("{$this->baseUrl}/articles", [
            'content' => 'Content without title',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_unauthenticated_user_cannot_create_article(): void
    {
        // Ensure no user is authenticated in this request
        auth('api')->logout();

        $response = $this->postJson("{$this->baseUrl}/articles", [
            'title'   => 'Hacked Article',
            'content' => 'Bad content',
        ]);

        $response->assertStatus(401);
    }

    // ================================================
    // Show
    // ================================================

    public function test_anyone_can_view_a_published_article(): void
    {
        $article = Article::factory()->create([
            'user_id'      => $this->user->id,
            'status'       => 'published',
            'published_at' => now(),
        ]);

        $response = $this->getJson("{$this->baseUrl}/articles/{$article->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $article->id);
    }

    // ================================================
    // Update
    // ================================================

    public function test_author_can_update_own_article(): void
    {
        $article = Article::factory()->create([
            'user_id' => $this->user->id,
            'status'  => 'draft',
        ]);

        $response = $this->withToken($this->token)->putJson("{$this->baseUrl}/articles/{$article->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title');
    }

    public function test_non_author_cannot_update_others_article(): void
    {
        $otherUser    = User::factory()->create();
        $otherArticle = Article::factory()->create([
            'user_id' => $otherUser->id,
            'status'  => 'draft',
        ]);

        $response = $this->withToken($this->token)->putJson("{$this->baseUrl}/articles/{$otherArticle->id}", [
            'title' => 'Hijacked Title',
        ]);

        $response->assertStatus(403);
    }

    // ================================================
    // Delete
    // ================================================

    public function test_author_can_delete_own_article(): void
    {
        $article = Article::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withToken($this->token)->deleteJson("{$this->baseUrl}/articles/{$article->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    public function test_non_author_cannot_delete_others_article(): void
    {
        $otherUser    = User::factory()->create();
        $otherArticle = Article::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->withToken($this->token)->deleteJson("{$this->baseUrl}/articles/{$otherArticle->id}");

        $response->assertStatus(403);
    }

    // ================================================
    // My Drafts & My Articles
    // ================================================

    public function test_user_can_get_their_own_drafts(): void
    {
        Article::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status'  => 'draft',
        ]);

        $response = $this->withToken($this->token)->getJson("{$this->baseUrl}/articles/my/drafts");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    public function test_user_can_publish_their_draft(): void
    {
        $draft = Article::factory()->create([
            'user_id' => $this->user->id,
            'status'  => 'draft',
        ]);

        $response = $this->withToken($this->token)->postJson("{$this->baseUrl}/articles/{$draft->id}/publish");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'published');
    }

    public function test_cannot_publish_an_already_published_article(): void
    {
        $article = Article::factory()->create([
            'user_id'      => $this->user->id,
            'status'       => 'published',
            'published_at' => now(),
        ]);

        $response = $this->withToken($this->token)->postJson("{$this->baseUrl}/articles/{$article->id}/publish");

        $response->assertStatus(400);
    }
}
