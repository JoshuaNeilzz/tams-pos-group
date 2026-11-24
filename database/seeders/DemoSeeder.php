<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Demo accounts are only available in local or testing environments.');
        }

        User::withTrashed()->firstOrCreate(['username' => 'demo_staff'], [
            'full_name' => 'Demo Staff', 'email' => 'staff@example.test',
            'password' => 'DemoStaff123!', 'role' => 'staff', 'status' => 'active',
        ]);

        if (! User::withTrashed()->where('username', 'demo_user')->exists()) {
            $customer = Customer::create(['full_name' => 'Demo User', 'email' => 'user@example.test', 'phone' => '09000000000']);
            User::create([
                'username' => 'demo_user', 'full_name' => 'Demo User', 'email' => 'user@example.test',
                'password' => 'DemoUser123!', 'role' => 'user', 'status' => 'active', 'customer_id' => $customer->id,
            ]);
        }

        foreach ([['Notebook', '85.00', 20], ['Gel Pen', '25.50', 40], ['Desk Organizer', '199.00', 5]] as [$name, $price, $stock]) {
            Product::firstOrCreate(['name' => $name], ['price' => $price, 'stock_quantity' => $stock]);
        }

        $this->command?->info('Demo staff: demo_staff / DemoStaff123!');
        $this->command?->info('Demo user: demo_user / DemoUser123!');
        $this->command?->info('Demo accounts are for local evaluation only. Existing account credentials are preserved.');
    }
}
