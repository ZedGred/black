<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\User;
use App\Models\Role;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        // Ambil role user dan writer yang sudah ada
        $userRole = Role::where('name', 'user')->first();
        $writerRole = Role::where('name', 'writer')->first(); // optional

        for ($i = 1; $i <= 20; $i++) {
            $user = User::firstOrCreate(
                ['email' => "user{$i}@gmail.com"],
                [
                    'name' => "user{$i}",
                    'password' => Hash::make('password'),
                    'about' => $faker->paragraph(2),
                ]
            );

            // Assign role "user"
            $user->syncRoles($userRole);

            // Untuk separuh pertama (1–10), assign role writer juga
            if ($i <= 10 && $writerRole) {
                $user->syncRoles($writerRole);
            }
        }
    }
}
