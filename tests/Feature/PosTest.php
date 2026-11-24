<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PosTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attributes = []): Product
    {
        return Product::create($attributes + ['name' => 'Market tote', 'price' => '149.95', 'stock_quantity' => 5]);
    }

    public function test_all_management_routes_require_staff_authentication(): void
    {
        foreach (['dashboard', 'products', 'products/create', 'customers', 'customers/create', 'users', 'users/create', 'sales', 'sales/create'] as $url) {
            $this->get('/'.$url)->assertRedirect('/login');
        }
        foreach (['products', 'customers', 'users', 'sales'] as $url) {
            $this->post('/'.$url)->assertRedirect('/login');
        }
        $this->get('/register')->assertOk();
        $this->get('/staff/register')->assertOk();
    }

    public function test_staff_can_sign_in_with_a_username_and_removed_staff_cannot_sign_in(): void
    {
        $user = User::factory()->create();
        $this->post('/login', ['login' => $user->username, 'password' => 'password'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->post('/logout');
        $user->delete();
        $this->post('/login', ['login' => $user->username, 'password' => 'password'])->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_pages_render_for_signed_in_staff_including_details_forms_and_empty_states(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $customer = Customer::create(['full_name' => 'A Customer', 'email' => 'customer@example.test']);
        $this->actingAs($user);
        foreach (['/', '/about', '/dashboard', '/products', '/products/create', '/products/'.$product->id.'/edit', '/customers', '/customers/create', '/customers/'.$customer->id, '/customers/'.$customer->id.'/edit', '/users', '/users/create', '/users/'.$user->id, '/users/'.$user->id.'/edit', '/sales', '/sales/create'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_sale_uses_server_price_updates_stock_and_records_authenticated_staff(): void
    {
        $staff = User::factory()->create();
        $product = $this->product();
        $customer = Customer::create(['full_name' => 'Test Customer', 'email' => 'buyer@example.test']);
        $this->actingAs($staff)->post('/sales', ['product_id' => $product->id, 'customer_id' => $customer->id, 'quantity' => 3, 'total_price' => '.01', 'sold_by' => 999])->assertRedirect('/sales')->assertSessionHasNoErrors();
        $this->assertSame(2, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('sales', ['product_id' => $product->id, 'customer_id' => $customer->id, 'sold_by' => $staff->id, 'quantity' => 3, 'total_price' => '449.85']);
        $product->update(['name' => 'Renamed product', 'price' => 999]);
        $customer->delete();
        $this->get('/sales')->assertOk()->assertSee('Market tote')->assertSee('Test Customer')->assertSee('449.85');
    }

    public function test_overselling_and_invalid_quantities_leave_database_unchanged(): void
    {
        $staff = User::factory()->create();
        $product = $this->product();
        foreach ([6, 0, -2, 1.5, 'invalid'] as $quantity) {
            $this->actingAs($staff)->post('/sales', ['product_id' => $product->id, 'quantity' => $quantity])->assertSessionHasErrors('quantity');
        }
        $this->assertSame(5, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_walk_in_sales_can_use_last_unit_and_archived_products_cannot_be_sold(): void
    {
        $staff = User::factory()->create();
        $product = $this->product(['stock_quantity' => 1]);
        $this->actingAs($staff)->post('/sales', ['product_id' => $product->id, 'quantity' => 1])->assertSessionHasNoErrors()->assertRedirect('/sales');
        $this->assertSame(0, $product->fresh()->stock_quantity);
        $this->assertNull(Sale::first()->customer_id);
        $this->post('/sales', ['product_id' => $product->id, 'quantity' => 1])->assertSessionHasErrors('quantity');
        $this->delete('/products/'.$product->id)->assertRedirect('/products');
        $this->post('/sales', ['product_id' => $product->id, 'quantity' => 1])->assertSessionHasErrors('product_id');
        $this->assertDatabaseCount('sales', 1);
        $this->get('/sales')->assertSee('Market tote');
    }

    public function test_products_validate_prices_stock_and_prepare_images_for_display(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $this->post('/products', ['name' => 'Invalid', 'price' => '-5', 'stock_quantity' => -1, 'image' => UploadedFile::fake()->create('unsafe.php', 1)])->assertSessionHasErrors(['price', 'stock_quantity', 'image']);
        $this->post('/products', ['name' => 'Test product', 'price' => '19.99', 'stock_quantity' => 10, 'image' => UploadedFile::fake()->image('photo.png', 800, 400)])->assertRedirect('/products')->assertSessionHasNoErrors();
        $product = Product::first();
        Storage::disk('public')->assertExists($product->image);
        $this->assertSame([640, 640], array_slice(getimagesize(Storage::disk('public')->path($product->image)), 0, 2));
        $this->get('/media/'.$product->image)->assertOk();
        $oldImage = $product->image;
        $this->put('/products/'.$product->id, ['name' => 'Updated', 'price' => '20', 'stock_quantity' => 3, 'image' => UploadedFile::fake()->image('next.jpg')])->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($oldImage);
        $this->assertDatabaseHas('products', ['name' => 'Updated', 'stock_quantity' => 3]);
    }

    public function test_staff_crud_hashes_password_prepares_avatar_and_prevents_self_removal(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $this->actingAs($admin)->post('/users', ['full_name' => 'New Staff', 'username' => 'newstaff', 'password' => 'new-password', 'password_confirmation' => 'new-password', 'avatar' => UploadedFile::fake()->image('profile.jpg', 800, 600)])->assertRedirect('/users')->assertSessionHasNoErrors();
        $staff = User::where('username', 'newstaff')->firstOrFail();
        $this->assertTrue(Hash::check('new-password', $staff->password));
        $this->assertSame([256, 256], array_slice(getimagesize(Storage::disk('public')->path($staff->avatar)), 0, 2));
        $this->put('/users/'.$staff->id, ['full_name' => 'Updated Staff', 'username' => 'newstaff', 'password' => 'another-password', 'password_confirmation' => 'another-password'])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('another-password', $staff->fresh()->password));
        $this->delete('/users/'.$admin->id)->assertSessionHasErrors('user');
        $this->assertNotNull($admin->fresh());
        $this->delete('/users/'.$staff->id)->assertRedirect('/users');
        $this->assertSoftDeleted('users', ['id' => $staff->id]);
    }

    public function test_customer_crud_and_input_validation(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post('/customers', ['full_name' => '', 'email' => 'bad'])->assertSessionHasErrors(['full_name', 'email']);
        $this->post('/customers', ['full_name' => 'Customer', 'email' => 'customer@example.test', 'phone' => '09123456789'])->assertRedirect('/customers');
        $customer = Customer::first();
        $this->put('/customers/'.$customer->id, ['full_name' => 'Updated Customer', 'email' => 'updated@example.test'])->assertRedirect('/customers');
        $this->assertDatabaseHas('customers', ['full_name' => 'Updated Customer']);
        $this->delete('/customers/'.$customer->id)->assertRedirect('/customers');
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}
