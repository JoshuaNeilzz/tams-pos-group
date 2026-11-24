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

class AccountRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function registration(array $overrides = []): array
    {
        return $overrides + ['full_name' => 'Registered User', 'username' => 'registered_user', 'email' => 'registered@example.test', 'password' => 'new-password', 'password_confirmation' => 'new-password'];
    }

    public function test_both_account_types_have_visible_registration_and_login_links(): void
    {
        $this->get('/')->assertOk()->assertSee('Create user account')->assertSee('Create staff account')->assertSee('Group Project')->assertDontSee('beautifully connected')->assertDontSee('A considered catalog');
        $this->get('/login')->assertOk()->assertSee('User sign in')->assertSee('Create user account');
        $this->get('/staff/login')->assertOk()->assertSee('Staff sign in')->assertSee('Create staff account');
        $this->get('/register')->assertOk()->assertSee('User registration');
        $this->get('/staff/register')->assertOk()->assertSee('Staff registration')->assertSee('Avatar');
    }

    public function test_user_registration_creates_a_linked_customer_and_ignores_forged_staff_permissions(): void
    {
        $this->post('/register', $this->registration(['role' => 'staff', 'status' => 'active', 'customer_id' => 99]))->assertRedirect('/account')->assertSessionHasNoErrors();
        $user = User::where('username', 'registered_user')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('user', $user->role);
        $this->assertSame('active', $user->status);
        $this->assertTrue(Hash::check('new-password', $user->password));
        $this->assertDatabaseHas('customers', ['id' => $user->customer_id, 'email' => $user->email]);
        $this->get('/account')->assertOk()->assertSee('Purchase history')->assertSee('Product catalog');
    }

    public function test_user_accounts_cannot_access_or_modify_any_staff_module(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $staff = User::factory()->create();
        $this->actingAs($user);
        foreach (['dashboard', 'products', 'products/create', 'customers', 'users', 'sales', 'sales/create'] as $url) {
            $this->get('/'.$url)->assertForbidden();
        }
        foreach (['products', 'customers', 'users', 'sales'] as $url) {
            $this->post('/'.$url, [])->assertForbidden();
        }
        $this->put('/users/'.$staff->id, $this->registration())->assertForbidden();
        $this->delete('/users/'.$staff->id)->assertForbidden();
        $this->patch('/users/'.$staff->id.'/approve')->assertForbidden();
    }

    public function test_staff_registration_stays_pending_until_an_existing_staff_member_approves_it(): void
    {
        Storage::fake('public');
        $this->post('/staff/register', $this->registration(['status' => 'active', 'avatar' => UploadedFile::fake()->image('avatar.png', 800, 400)]))->assertRedirect('/account');
        $pending = User::where('username', 'registered_user')->firstOrFail();
        $this->assertSame('staff', $pending->role);
        $this->assertSame('pending', $pending->status);
        $this->assertNull($pending->customer_id);
        Storage::disk('public')->assertExists($pending->avatar);
        $this->get('/account')->assertOk()->assertSee('Staff approval pending');
        $this->get('/dashboard')->assertForbidden();
        $this->post('/sales')->assertForbidden();
        $this->post('/logout');
        $staff = User::factory()->create();
        $this->actingAs($staff)->get('/users')->assertOk()->assertSee('Awaiting approval')->assertSee('Approve');
        $this->patch('/users/'.$pending->id.'/approve')->assertRedirect('/users');
        $this->assertSame('active', $pending->fresh()->status);
        $this->post('/logout');
        $this->post('/login', ['login' => $pending->username, 'password' => 'new-password', 'account_type' => 'staff'])->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertOk();
    }

    public function test_sign_in_routes_enforce_account_type_and_customer_redirects_to_own_account(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->post('/login', ['login' => $user->username, 'password' => 'password', 'account_type' => 'staff'])->assertSessionHasErrors('login');
        $this->assertGuest();
        $this->post('/login', ['login' => $user->username, 'password' => 'password', 'account_type' => 'user'])->assertRedirect('/account');
    }

    public function test_registration_rejects_duplicate_accounts_invalid_email_and_unconfirmed_passwords(): void
    {
        User::factory()->create(['username' => 'registered_user', 'email' => 'registered@example.test']);
        $this->post('/register', $this->registration())->assertSessionHasErrors(['username', 'email']);
        $this->post('/register', $this->registration(['username' => 'second_user', 'email' => 'invalid', 'password_confirmation' => 'wrong']))->assertSessionHasErrors(['email', 'password']);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('customers', 0);
    }

    public function test_purchase_history_is_limited_to_the_linked_customer_not_email_or_request_parameters(): void
    {
        $staff = User::factory()->create();
        $customer = Customer::create(['full_name' => 'Customer A', 'email' => 'shared@example.test']);
        $other = Customer::create(['full_name' => 'Customer B', 'email' => 'shared@example.test']);
        $user = User::factory()->create(['role' => 'user', 'customer_id' => $customer->id]);
        $product = Product::create(['name' => 'Product', 'price' => '25.00', 'stock_quantity' => 5]);
        foreach ([[$customer->id, 'Own purchase'], [$other->id, 'Private other purchase'], [null, 'Walk-in transaction']] as [$customerId, $label]) {
            Sale::create(['product_id' => $product->id, 'customer_id' => $customerId, 'sold_by' => $staff->id, 'quantity' => 1, 'total_price' => '25.00', 'product_name' => $label, 'staff_name' => $staff->full_name]);
        }
        $this->actingAs($user)->get('/account?customer_id='.$other->id)->assertOk()->assertSee('Own purchase')->assertDontSee('Private other purchase')->assertDontSee('Walk-in transaction');
        $user->update(['customer_id' => null]);
        $this->get('/account')->assertOk()->assertDontSee('Walk-in transaction')->assertDontSee('Own purchase');
    }

    public function test_staff_management_does_not_treat_customer_accounts_as_staff(): void
    {
        $staff = User::factory()->create();
        $user = User::factory()->create(['role' => 'user', 'full_name' => 'Customer-only account']);
        $this->actingAs($staff)->get('/users')->assertOk()->assertDontSee('Customer-only account');
        $this->get('/users/'.$user->id)->assertNotFound();
        $this->put('/users/'.$user->id, $this->registration())->assertNotFound();
        $this->delete('/users/'.$user->id)->assertNotFound();
    }
}
