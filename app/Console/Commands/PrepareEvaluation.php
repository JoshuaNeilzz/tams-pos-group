<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\SnackCatalogSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PrepareEvaluation extends Command
{
    protected $signature = 'pos:prepare-evaluation {--reset-passwords : Reset only the two named demo passwords} {--archive-stationery : Archive the three former sample stationery products}';

    protected $description = 'Prepare the shared classroom snack-store dataset; requires POS_EVALUATION_MODE=true on a hosted production app';

    public function handle(): int
    {
        if (app()->environment('production') && ! config('store.evaluation_enabled')) {
            $this->error('Refusing shared accounts in a non-evaluation production store. Set POS_EVALUATION_MODE=true only for the classroom demo.');

            return self::FAILURE;
        }
        DB::transaction(function () {
            foreach ([['demo_staff', 'Demo Staff', 'staff', 'DemoStaff123!'], ['demo_user', 'Demo Shopper', 'user', 'DemoUser123!']] as [$username, $name, $role, $password]) {
                $account = User::withTrashed()->where('username', $username)->lockForUpdate()->first();
                if ($account && ($account->trashed() || $account->role !== $role)) {
                    throw new \RuntimeException('The reserved demo username '.$username.' belongs to an incompatible account. No accounts were changed.');
                }
                $customerId = $account?->customer_id;
                if ($role === 'user' && ! $customerId) {
                    $customerId = Customer::create(['full_name' => $name, 'email' => 'shopper@example.test'])->id;
                }
                if (! $account) {
                    User::create(['username' => $username, 'full_name' => $name, 'password' => $password, 'role' => $role, 'status' => 'active', 'customer_id' => $customerId]);
                } else {
                    $account->status = 'active';
                    $account->customer_id = $customerId;
                    if ($this->option('reset-passwords')) {
                        $account->password = $password;
                        $account->remember_token = null;
                    }
                    $account->save();
                    if ($this->option('reset-passwords')) {
                        DB::table('sessions')->where('user_id', $account->id)->delete();
                    }
                }
            }
            (new SnackCatalogSeeder)->run();
            if ($this->option('archive-stationery')) {
                Product::whereIn('name', ['Notebook', 'Gel Pen', 'Desk Organizer'])->delete();
            }
        });
        $this->info('Classroom snack store prepared. Shared account details are in README.md. Existing snack prices, stock, sales, and other staff accounts were preserved.');

        return self::SUCCESS;
    }
}
