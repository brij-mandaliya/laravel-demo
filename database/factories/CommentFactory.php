<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
            'parent_id' => null,
            'depth' => 0,
            'body' => fake()->sentences(rand(1, 4), true),
        ];
    }

    /**
     * Make this comment a reply to the given parent comment.
     */
    public function replyTo(Comment $parent): static
    {
        return $this->state(fn () => [
            'parent_id' => $parent->id,
            'post_id' => $parent->post_id,
            'depth' => $parent->depth + 1,
        ]);
    }
}
