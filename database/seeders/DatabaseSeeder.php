<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $demo = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
        ]);

        $others = User::factory()->count(5)->create();

        $authors = $others->push($demo);

        $posts = Post::factory()->count(20)->create([
            'user_id' => fn () => $authors->random()->id,
        ]);

        foreach ($posts as $post) {
            $topComments = Comment::factory()->count(rand(1, 6))->create([
                'user_id' => fn () => $authors->random()->id,
                'post_id' => $post->id,
            ]);

            foreach ($topComments as $comment) {
                if (rand(0, 3) === 0) {
                    continue;
                }

                $reply = Comment::factory()->replyTo($comment)->create([
                    'user_id' => fn () => $authors->random()->id,
                ]);

                if (rand(0, 1) === 0) {
                    Comment::factory()->replyTo($reply)
                        ->count(1)
                        ->create([
                            'user_id' => fn () => $authors->random()->id,
                        ]);
                }
            }
        }
    }
}
