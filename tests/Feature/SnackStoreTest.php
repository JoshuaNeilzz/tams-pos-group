<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SnackStoreTest extends TestCase
{
    use RefreshDatabase;

    private function shopper(): User
    {
        $customer = Customer::create(['full_name' => 'Snack Shopper', 'email' => 'snacks@example.test']);

        return User::factory()->create(['role' => 'user', 'customer_id' => $customer->id]);
    }

    private function snack(array $overrides = []): Product
    {
        return Product::create($overrides + ['name' => 'Nova Multigrain Chips', 'price' => '18.00', 'stock_quantity' => 10]);
    }

    private function checkout(Product $snack, int $quantity = 2): array
    {
        return ['product_id' => $snack->id, 'quantity' => $quantity, 'request_key' => (string) Str::uuid()];
    }

    public function test_customer_can_reserve_snacks_at_server_price_without_recording_unpaid_revenue(): void
    {
        $buyer = $this->shopper();
        $snack = $this->snack();
        $this->actingAs($buyer)->post('/orders', $this->checkout($snack) + ['total_price' => '0.01', 'customer_id' => 999, 'user_id' => 999, 'status' => 'completed'])->assertRedirect('/account')->assertSessionHasNoErrors();
        $this->assertDatabaseHas('orders', ['user_id' => $buyer->id, 'customer_id' => $buyer->customer_id, 'quantity' => 2, 'unit_price' => '18.00', 'total_price' => '36.00', 'status' => 'pending']);
        $this->assertSame(8, $snack->fresh()->stock_quantity);
        $this->assertDatabaseCount('sales', 0);
        $this->get('/account')->assertOk()->assertSee('Buy / reserve')->assertSee('My pickup orders')->assertSee('Nova Multigrain Chips');
    }

    public function test_checkout_retries_never_reserve_the_same_stock_twice(): void
    {
        $snack = $this->snack();
        $payload = $this->checkout($snack);
        $this->actingAs($this->shopper())->post('/orders', $payload)->assertSessionHasNoErrors();
        $this->post('/orders', $payload)->assertSessionHasNoErrors();
        $this->post('/orders', array_replace($payload, ['quantity' => 3]))->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(8, $snack->fresh()->stock_quantity);
    }

    public function test_checkout_rejects_invalid_stock_archived_snacks_and_wrong_account_types(): void
    {
        $snack = $this->snack(['stock_quantity' => 1]);
        $this->post('/orders', $this->checkout($snack))->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->post('/orders', $this->checkout($snack))->assertForbidden();
        $this->actingAs($this->shopper());
        foreach ([2, 0, -1, 1.5] as $quantity) {
            $this->post('/orders', ['product_id' => $snack->id, 'quantity' => $quantity, 'request_key' => (string) Str::uuid()])->assertSessionHasErrors('quantity');
        }
        $snack->delete();
        $this->post('/orders', $this->checkout($snack, 1))->assertSessionHasErrors('product_id');
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(1, Product::withTrashed()->find($snack->id)->stock_quantity);
    }

    public function test_staff_completion_uses_reserved_price_and_is_idempotent_without_double_stock_deduction(): void
    {
        $buyer = $this->shopper();
        $snack = $this->snack();
        $this->actingAs($buyer)->post('/orders', $this->checkout($snack))->assertSessionHasNoErrors();
        $order = Order::firstOrFail();
        $this->patch('/orders/'.$order->id.'/complete')->assertForbidden();
        $snack->update(['price' => '999.00', 'name' => 'Changed snack name']);
        $staff = User::factory()->create();
        $this->actingAs($staff)->get('/orders')->assertOk()->assertSee('Paid &amp; collected', false);
        $this->patch('/orders/'.$order->id.'/complete')->assertRedirect('/orders')->assertSessionHasNoErrors();
        $this->patch('/orders/'.$order->id.'/complete')->assertSessionHasNoErrors();
        $this->assertDatabaseCount('sales', 1);
        $this->assertDatabaseHas('sales', ['product_name' => 'Nova Multigrain Chips', 'total_price' => '36.00', 'sold_by' => $staff->id, 'customer_id' => $buyer->customer_id]);
        $this->assertSame(8, $snack->fresh()->stock_quantity);
        $this->assertSame('completed', $order->fresh()->status);
        $this->patch('/orders/'.$order->id.'/cancel')->assertSessionHasErrors('order');
        $this->actingAs($buyer)->get('/account')->assertSee('36.00');
    }

    public function test_cancellation_returns_packs_once_even_for_archived_products_and_protects_other_buyers(): void
    {
        $buyer = $this->shopper();
        $snack = $this->snack();
        $this->actingAs($buyer)->post('/orders', $this->checkout($snack))->assertSessionHasNoErrors();
        $order = Order::firstOrFail();
        $this->actingAs($this->shopper())->patch('/orders/'.$order->id.'/cancel')->assertForbidden();
        $snack->delete();
        $this->actingAs($buyer)->patch('/orders/'.$order->id.'/cancel')->assertSessionHasNoErrors();
        $this->patch('/orders/'.$order->id.'/cancel')->assertSessionHasNoErrors();
        $this->assertSame(10, Product::withTrashed()->find($snack->id)->stock_quantity);
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_only_approved_staff_can_add_stock_and_every_restock_preserves_a_receipt(): void
    {
        $snack = $this->snack();
        $this->actingAs($this->shopper())->post('/products/'.$snack->id.'/restock', ['quantity' => 4, 'request_key' => (string) Str::uuid()])->assertForbidden();
        $this->actingAs(User::factory()->create(['status' => 'pending']))->post('/products/'.$snack->id.'/restock', ['quantity' => 4, 'request_key' => (string) Str::uuid()])->assertForbidden();
        $staff = User::factory()->create();
        $this->actingAs($staff)->post('/products/'.$snack->id.'/restock', ['quantity' => 4, 'note' => 'Supplier delivery', 'request_key' => (string) Str::uuid()])->assertRedirect('/products')->assertSessionHasNoErrors();
        $this->assertSame(14, $snack->fresh()->stock_quantity);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $snack->id, 'user_id' => $staff->id, 'quantity' => 4, 'balance_after' => 14, 'note' => 'Supplier delivery']);
        foreach ([0, -2, 1.5, 1000000000] as $quantity) {
            $this->post('/products/'.$snack->id.'/restock', ['quantity' => $quantity, 'request_key' => (string) Str::uuid()])->assertSessionHasErrors('quantity');
        }
        $this->assertSame(14, $snack->fresh()->stock_quantity);
        $this->assertDatabaseCount('stock_movements', 1);
    }

    public function test_restock_retries_cannot_count_one_delivery_twice(): void
    {
        $snack = $this->snack();
        $payload = ['quantity' => 5, 'request_key' => (string) Str::uuid()];
        $this->actingAs(User::factory()->create())->post('/products/'.$snack->id.'/restock', $payload)->assertSessionHasNoErrors();
        $this->post('/products/'.$snack->id.'/restock', $payload)->assertSessionHasNoErrors();
        $this->post('/products/'.$snack->id.'/restock', array_replace($payload, ['quantity' => 6]))->assertSessionHasErrors('quantity');
        $this->assertSame(15, $snack->fresh()->stock_quantity);
        $this->assertDatabaseCount('stock_movements', 1);
    }

    public function test_stale_inventory_edit_cannot_overwrite_an_order_reservation(): void
    {
        $snack = $this->snack();
        $this->actingAs($this->shopper())->post('/orders', $this->checkout($snack));
        $this->actingAs(User::factory()->create())->put('/products/'.$snack->id, ['name' => $snack->name, 'price' => '18.00', 'stock_quantity' => 10, 'stock_before' => 10])->assertSessionHasErrors('stock_quantity');
        $this->assertSame(8, $snack->fresh()->stock_quantity);
    }

    public function test_evaluation_setup_is_repeatable_preserves_stock_and_only_resets_named_demo_accounts_explicitly(): void
    {
        $this->artisan('pos:prepare-evaluation')->assertSuccessful();
        $this->assertDatabaseCount('products', 6);
        $this->assertDatabaseCount('users', 2);
        $staff = User::where('username', 'demo_staff')->firstOrFail();
        $staff->update(['password' => 'different-secret']);
        $snack = Product::first();
        $snack->update(['stock_quantity' => 3]);
        $this->artisan('pos:prepare-evaluation')->assertSuccessful();
        $this->assertTrue(Hash::check('different-secret', $staff->fresh()->password));
        $this->assertSame(3, $snack->fresh()->stock_quantity);
        $this->artisan('pos:prepare-evaluation', ['--reset-passwords' => true])->assertSuccessful();
        $this->assertTrue(Hash::check('DemoStaff123!', $staff->fresh()->password));
        $this->post('/login', ['login' => 'demo_staff', 'password' => 'DemoStaff123!', 'account_type' => 'staff'])->assertRedirect('/dashboard');
        $this->post('/logout');
        $this->post('/login', ['login' => 'demo_user', 'password' => 'DemoUser123!', 'account_type' => 'user'])->assertRedirect('/account');
    }

    public function test_production_evaluation_accounts_require_an_explicit_evaluation_flag(): void
    {
        $this->app->instance('env', 'production');
        config(['store.evaluation_enabled' => false]);
        $this->artisan('pos:prepare-evaluation')->assertFailed();
        $this->assertDatabaseCount('users', 0);
        config(['store.evaluation_enabled' => true]);
        $this->artisan('pos:prepare-evaluation')->assertSuccessful();
        $this->assertDatabaseCount('users', 2);
    }
}
