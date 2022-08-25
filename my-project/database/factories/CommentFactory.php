<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        if (Post::count() == 0) {
            Post::factory(10)->create();
        };

        return [
            'post_id' => Post::all()->random()->id,
            'content' => fake()->text(300),
        ];
    }
}
