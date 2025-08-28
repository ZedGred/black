<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Ambil semua user dengan role writer saja
        $writers = User::role('writer')->get();

        // Buat 50 artikel
        for ($i = 1; $i <= 50; $i++) {
            $user = $writers->random(); // pilih writer random

            Article::create([
                'id' => Str::uuid(),
                'user_id' => $user->id,
                'title' => $faker->sentence(6, true),
                'excerpt' => $faker->paragraph(2, true),
                'content' => $faker->paragraphs(5, true),
                'image' => null, // bisa diisi atau pakai faker image
                'meta_title' => $faker->sentence(6, true),
                'meta_description' => $faker->sentence(12, true),
                'tags' => json_encode($faker->words(3)),
                'published_at' => $faker->dateTimeBetween('-6 months', 'now'),
            ]);
        }
    }
}
