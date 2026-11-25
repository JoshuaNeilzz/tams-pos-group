<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Use pos:prepare-evaluation for an explicitly enabled hosted classroom demo.');
        }
        Artisan::call('pos:prepare-evaluation');
        $this->command?->info('Demo staff: demo_staff / DemoStaff123!');
        $this->command?->info('Demo shopper: demo_user / DemoUser123!');
        $this->command?->info('Snack samples added. Existing credentials and stock were preserved.');
    }
}
