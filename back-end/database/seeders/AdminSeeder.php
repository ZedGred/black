<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $roleName = 'admin';
        $adminEmail = 'admin@gmail.com';
        $adminName = 'Administrator';
        $password = 'password';

        // Pastikan role admin ada
        Role::firstOrCreate(['name' => $roleName]);

        // Cari user berdasarkan email ATAU name
        $admin = User::where('email', $adminEmail)
            ->orWhere('name', $adminName)
            ->first();

        if ($admin) {
            // Update password dan assign role jika sudah ada
            $admin->update([
                'email' => $adminEmail,
                'name' => $adminName,
                'password' => Hash::make($password),
            ]);
        } else {
            // Buat baru
            $admin = User::create([
                'name' => $adminName,
                'email' => $adminEmail,
                'password' => Hash::make($password),
            ]);
        }

        // Assign role admin jika belum ada
        if (!$admin->hasRole($roleName)) {
            $admin->assignRole($roleName);
        }

        $this->command->info("Admin ready!");
        $this->command->warn("Email: {$adminEmail}");
        $this->command->warn("Password: {$password}");
    }
}
