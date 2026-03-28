<?php

declare(strict_types=1);

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Comment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

final class ExampleTest extends TestCase
{
    use DatabaseTransactions;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $response = $this->postJson('/api/login', [
            'email_or_mobile_or_username' => 'testee2@gmail.com',
            'password' => 'admin1',
        ]);
        $this->token = $response->json()['data']['access_token'];
    }

    /**
     * A basic test example.
     */
    public function test_user_can_delete_comment(): void
    {
        $comment = Comment::inRandomOrder()->first();
        $username = $comment?->user?->username;
        $postId = $comment?->post_id;

        $response = $this->delete('/api/posts/'.$username.'/'.$postId.'/comment/'.$comment->id, [], ['Authorization' => 'Bearer '.$this->token]);
        $response->assertStatus(200);
    }
}
