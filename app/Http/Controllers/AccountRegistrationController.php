<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Services\DisplayImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountRegistrationController extends Controller
{
    public function userForm()
    {
        return view('auth.register', ['accountType' => 'user', 'title' => 'Create user account']);
    }

    public function staffForm()
    {
        return view('auth.register', ['accountType' => 'staff', 'title' => 'Create staff account']);
    }

    public function registerUser(Request $request, DisplayImage $images)
    {
        return $this->register($request, $images, 'user');
    }

    public function registerStaff(Request $request, DisplayImage $images)
    {
        return $this->register($request, $images, 'staff');
    }

    private function register(Request $request, DisplayImage $images, string $role)
    {
        $data = $request->validate([
            'username' => ['required', 'alpha_dash', 'max:50', 'unique:users,username'],
            'full_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=6000,max_height=6000'],
        ]);
        unset($data['avatar']);
        if ($role === 'staff' && $request->hasFile('avatar')) {
            $data['avatar'] = $images->store($request->file('avatar'), 'avatars', 256);
        }
        $user = DB::transaction(function () use ($data, $role) {
            $customer = $role === 'user' ? Customer::create(['full_name' => $data['full_name'], 'email' => $data['email']]) : null;

            return User::create($data + ['role' => $role, 'status' => $role === 'staff' ? 'pending' : 'active', 'customer_id' => $customer?->id]);
        });
        Auth::login($user);
        $request->session()->regenerate();

        return to_route('account')->with('success', $role === 'staff' ? 'Staff registration submitted. An existing staff member must approve your access.' : 'Your user account has been created.');
    }
}
