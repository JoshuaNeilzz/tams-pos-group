<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\UploadedImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HostedStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_prepared_product_images_are_persisted_served_and_replaced_from_the_database(): void
    {
        config(['media.driver' => 'database']);
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $this->post('/products', ['name' => 'Hosted product', 'price' => '12.50', 'stock_quantity' => 3, 'image' => UploadedFile::fake()->image('photo.png', 800, 400)])->assertSessionHasNoErrors()->assertRedirect('/products');
        $product = Product::firstOrFail();
        $stored = UploadedImage::findOrFail($product->image);
        Storage::disk('public')->assertMissing($product->image);
        $this->assertSame([640, 640], array_slice(getimagesizefromstring(base64_decode($stored->content)), 0, 2));
        $this->get('/media/'.$product->image)->assertOk()->assertHeader('Content-Type', 'image/webp')->assertHeader('X-Content-Type-Options', 'nosniff')->assertContent(base64_decode($stored->content));
        $old = $product->image;
        $this->put('/products/'.$product->id, ['name' => 'Hosted product', 'price' => '12.50', 'stock_quantity' => 3, 'image' => UploadedFile::fake()->image('replacement.jpg')])->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('uploaded_images', ['path' => $old]);
        $this->get('/media/'.$old)->assertNotFound();
        $this->get('/media/'.$product->fresh()->image)->assertOk();
        $this->assertDatabaseCount('uploaded_images', 1);
    }

    public function test_database_images_still_require_authentication(): void
    {
        config(['media.driver' => 'database']);
        $this->get('/media/products/example.webp')->assertRedirect('/login');
    }

    public function test_staff_registration_and_profile_use_hosted_avatar_storage(): void
    {
        config(['media.driver' => 'database']);
        $this->post('/staff/register', ['username' => 'new_staff', 'full_name' => 'New Staff', 'email' => 'new@example.test', 'password' => 'new-password', 'password_confirmation' => 'new-password', 'avatar' => UploadedFile::fake()->image('avatar.png')])->assertSessionHasNoErrors();
        $staff = User::where('username', 'new_staff')->firstOrFail();
        $this->assertDatabaseHas('uploaded_images', ['path' => $staff->avatar]);
        $this->get('/media/'.$staff->avatar)->assertOk();
        $old = $staff->avatar;
        $this->actingAs(User::factory()->create())->put('/users/'.$staff->id, ['username' => $staff->username, 'full_name' => $staff->full_name, 'email' => $staff->email, 'avatar' => UploadedFile::fake()->image('new.jpg')])->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('uploaded_images', ['path' => $old]);
        $this->assertDatabaseHas('uploaded_images', ['path' => $staff->fresh()->avatar]);
    }
}
