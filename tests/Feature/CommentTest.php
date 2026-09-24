<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_comment_on_a_post(): void
    {
        $post = Post::factory()->create();

        $this->post(route('comments.store', $post), ['body' => 'Hello there'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_user_can_comment_on_a_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->post(route('comments.store', $post), ['body' => 'Nice post!'])
            ->assertRedirect(route('posts.show', $post));

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'parent_id' => null,
            'depth' => 0,
            'body' => 'Nice post!',
        ]);
    }

    public function test_comment_body_is_required_and_max_2000_chars(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->post(route('comments.store', $post), ['body' => ''])
            ->assertSessionHasErrors('body');

        $this->actingAs($user)
            ->post(route('comments.store', $post), ['body' => str_repeat('a', 2001)])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_user_can_reply_to_a_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $parent = Comment::factory()->create(['post_id' => $post->id]);

        $this->actingAs($user)
            ->post(route('comments.reply', [$post, $parent]), ['body' => 'I agree with you.'])
            ->assertRedirect(route('posts.show', $post));

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'parent_id' => $parent->id,
            'depth' => 1,
            'body' => 'I agree with you.',
        ]);
    }

    public function test_user_can_reply_to_a_reply(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $top = Comment::factory()->create(['post_id' => $post->id]);
        $reply = Comment::factory()->create([
            'post_id' => $post->id,
            'parent_id' => $top->id,
            'depth' => 1,
        ]);

        $this->actingAs($user)
            ->post(route('comments.reply', [$post, $reply]), ['body' => 'A second-level reply.'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'parent_id' => $reply->id,
            'depth' => 2,
        ]);
    }

    public function test_nesting_depth_is_capped_at_three_levels(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $level0 = Comment::factory()->create(['post_id' => $post->id, 'depth' => 0]);
        $level1 = Comment::factory()->create(['post_id' => $post->id, 'parent_id' => $level0->id, 'depth' => 1]);
        $level2 = Comment::factory()->create(['post_id' => $post->id, 'parent_id' => $level1->id, 'depth' => 2]);
        $level3 = Comment::factory()->create(['post_id' => $post->id, 'parent_id' => $level2->id, 'depth' => 3]);

        $this->actingAs($user)
            ->post(route('comments.reply', [$post, $level2]), ['body' => 'One more is fine.'])
            ->assertSessionHasNoErrors()
            ->assertSessionHasNoErrors('parent_id');

        $this->actingAs($user)
            ->post(route('comments.reply', [$post, $level3]), ['body' => 'This is too deep.'])
            ->assertSessionHasErrors('parent_id');

        $this->assertDatabaseMissing('comments', ['post_id' => $post->id, 'body' => 'This is too deep.']);
    }

    public function test_cannot_reply_to_a_comment_from_another_post(): void
    {
        $user = User::factory()->create();
        $postA = Post::factory()->create();
        $postB = Post::factory()->create();
        $commentOnB = Comment::factory()->create(['post_id' => $postB->id]);

        $this->actingAs($user)
            ->post(route('comments.reply', [$postA, $commentOnB]), ['body' => 'Wrong place.'])
            ->assertSessionHasErrors('parent_id');

        $this->assertDatabaseCount('comments', 1);
    }

    public function test_owner_can_delete_their_comment(): void
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        $this->actingAs($comment->user)
            ->delete(route('comments.destroy', [$post, $comment]))
            ->assertRedirect();

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_non_owner_cannot_delete_a_comment(): void
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post->id]);
        $other = User::factory()->create();

        $this->actingAs($other)
            ->delete(route('comments.destroy', [$post, $comment]))
            ->assertForbidden();

        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    public function test_deleting_a_comment_deletes_its_replies(): void
    {
        $post = Post::factory()->create();
        $top = Comment::factory()->create(['post_id' => $post->id]);
        $reply = Comment::factory()->create([
            'post_id' => $post->id,
            'parent_id' => $top->id,
            'depth' => 1,
        ]);
        Comment::factory()->create([
            'post_id' => $post->id,
            'parent_id' => $reply->id,
            'depth' => 2,
        ]);

        $this->actingAs($top->user)
            ->delete(route('comments.destroy', [$post, $top]));

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_post_show_renders_the_threaded_tree_in_order(): void
    {
        $post = Post::factory()->create();
        $top = Comment::factory()->create(['post_id' => $post->id, 'body' => 'First comment.']);
        $reply = Comment::factory()->create([
            'post_id' => $post->id,
            'parent_id' => $top->id,
            'depth' => 1,
            'body' => 'Nested reply.',
        ]);

        $content = $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('First comment.')
            ->assertSee('Nested reply.')
            ->getContent();

        $this->assertTrue(
            strpos($content, $top->body) < strpos($content, $reply->body)
        );
    }
}
