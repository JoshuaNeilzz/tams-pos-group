<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable();
            $table->string('email')->nullable()->unique();
            $table->rememberToken();
            $table->softDeletes();
        });
        // Keep existing login credentials while tying every login to a staff record.
        foreach (DB::table('login_accounts')->get() as $account) {
            $staff = DB::table('users')->where('full_name', $account->name)->whereNull('email')->first();
            $data = ['email' => $account->email, 'password' => $account->password];
            if ($staff) {
                DB::table('users')->where('id', $staff->id)->update($data);
            } else {
                DB::table('users')->insert($data + ['username' => 'staff_'.Str::lower(Str::random(12)), 'full_name' => $account->name, 'created_at' => $account->created_at]);
            }
        }
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->string('image')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();
        });
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sold_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('total_price', 10, 2);
            $table->string('product_name', 100);
            $table->string('customer_name', 100)->nullable();
            $table->string('staff_name', 100);
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
        Schema::dropIfExists('products');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['password', 'email', 'remember_token', 'deleted_at']));
    }
};
