<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_creating_a_post(): void
    {
        $this->get(route('posts.create'))->assertRedirect(route('login'));
    }

    public function test_guest_cannot_store_a_post(): void
    {
        $this->post(route('posts.store'), [
            'title' => 'My post',
            'body' => 'This is the body of the post.',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_user_can_create_a_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('posts.store'), [
            'title' => 'My first post',
            'body' => 'This is the body of my first post.',
        ]);

        $post = Post::firstOrFail();
        $response->assertRedirect(route('posts.show', $post));

        $this->assertDatabaseHas('posts', [
            'title' => 'My first post',
            'slug' => 'my-first-post',
            'user_id' => $user->id,
        ]);

        $this->get(route('posts.index'))->assertSee('My first post');
    }

    public function test_title_must_be_between_3_and_255_characters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('posts.create'))
            ->post(route('posts.store'), ['title' => 'ab', 'body' => str_repeat('a', 20)])
            ->assertRedirect(route('posts.create'))
            ->assertSessionHasErrors('title');

        $this->actingAs($user)
            ->post(route('posts.store'), ['title' => str_repeat('a', 256), 'body' => str_repeat('a', 20)])
            ->assertSessionHasErrors('title');

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_body_requires_at_least_10_characters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('posts.store'), ['title' => 'A valid title', 'body' => 'short'])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_duplicate_titles_get_unique_slugs(): void
    {
        $user = User::factory()->create();
        $payload = ['title' => 'Same Title', 'body' => 'Enough body content here.'];

        $this->actingAs($user)->post(route('posts.store'), $payload);
        $this->actingAs($user)->post(route('posts.store'), $payload);

        $this->assertSame(
            ['same-title', 'same-title-2'],
            Post::orderBy('id')->pluck('slug')->all()
        );
    }

    public function test_index_lists_newest_posts_first(): void
    {
        $oldest = Post::factory()->create(['created_at' => now()->subDays(3)]);
        $newest = Post::factory()->create();

        $content = $this->get(route('posts.index'))->assertOk()->getContent();

        $this->assertTrue(
            strpos($content, $newest->title) < strpos($content, $oldest->title)
        );
    }

    public function test_index_is_paginated(): void
    {
        Post::factory()->count(15)->create();

        $page1 = $this->get(route('posts.index'))->assertOk();
        $page2 = $this->get(route('posts.index').'?page=2')->assertOk();

        $this->assertSame(15, $page1->viewData('posts')->total());
        $this->assertCount(10, $page1->viewData('posts')->items());
        $this->assertCount(5, $page2->viewData('posts')->items());
    }

    public function test_anyone_can_view_a_post(): void
    {
        $post = Post::factory()->create();

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee($post->body);
    }

    public function test_owner_can_edit_and_update_their_post(): void
    {
        $post = Post::factory()->create();

        $this->actingAs($post->user)->get(route('posts.edit', $post))->assertOk();

        $this->actingAs($post->user)
            ->put(route('posts.update', $post), [
                'title' => 'Updated Title',
                'body' => 'Updated body content here.',
            ])
            ->assertRedirect(route('posts.show', $post->fresh()));

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
            'slug' => 'updated-title',
        ]);
    }

    public function test_non_owner_cannot_edit_or_update_a_post(): void
    {
        $post = Post::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($other)->get(route('posts.edit', $post))->assertForbidden();

        $this->actingAs($other)
            ->put(route('posts.update', $post), [
                'title' => 'Hijacked title',
                'body' => 'A very long body to pass validation.',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => $post->title]);
    }

    public function test_owner_can_delete_a_post_and_its_comments_are_removed(): void
    {
        $post = Post::factory()->create();
        $top = Comment::factory()->create(['post_id' => $post->id]);
        Comment::factory()->create([
            'post_id' => $post->id,
            'parent_id' => $top->id,
            'depth' => 1,
        ]);

        $this->actingAs($post->user)
            ->delete(route('posts.destroy', $post))
            ->assertRedirect(route('posts.index'));

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_non_owner_cannot_delete_a_post(): void
    {
        $post = Post::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($other)
            ->delete(route('posts.destroy', $post))
            ->assertForbidden();

        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }
}
