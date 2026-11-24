<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/** Backward-compatible entry point for optional generic demo records. */
class PosAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DemoSeeder::class);
    }
}
