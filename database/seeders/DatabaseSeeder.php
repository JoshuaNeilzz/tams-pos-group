<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('role', 'staff')->where('status', 'active')->exists()) {
            $this->command?->info('Existing staff accounts preserved. No initial account was created.');

            return;
        }
        $password = env('POS_ADMIN_PASSWORD') ?: Str::password(16);
        $username = User::withTrashed()->where('username', 'admin')->exists() ? 'admin_'.Str::lower(Str::random(6)) : 'admin';
        User::create(['username' => $username, 'full_name' => 'Project Administrator', 'password' => $password, 'role' => 'staff', 'status' => 'active']);
        $this->command?->warn('Initial username: '.$username.' | Password: '.$password);
        $this->command?->info('Save this password, then change it through Staff > Edit.');
    }
}
