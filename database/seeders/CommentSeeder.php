<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\User;
use App\Models\Article;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Ambil semua user
        $users = User::all();

        // Ambil semua artikel
        $articles = Article::all();

        // Buat 100 komentar random
        for ($i = 1; $i <= 100; $i++) {
            $user = $users->random();      // user random
            $article = $articles->random(); // article random

            Comment::create([
                'id' => Str::uuid(),
                'user_id' => $user->id,
                'article_id' => $article->id,
                'content' => $faker->paragraph(2, true),
                'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
                'updated_at' => now(),
            ]);
        }
    }
}
