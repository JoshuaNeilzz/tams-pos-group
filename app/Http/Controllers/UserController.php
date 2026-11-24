<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DisplayImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return view('users', ['title' => 'Staff', 'users' => User::where('role', 'staff')->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")->orderBy('full_name')->get()]);
    }

    public function create()
    {
        return view('users-form', ['title' => 'Add staff', 'user' => null]);
    }

    public function edit(User $user)
    {
        abort_unless($user->role === 'staff', 404);

        return view('users-form', ['title' => 'Edit staff', 'user' => $user]);
    }

    public function show(User $user)
    {
        abort_unless($user->role === 'staff', 404);

        return view('users-show', ['title' => 'Staff details', 'user' => $user]);
    }

    private function save(Request $request, User $user, DisplayImage $images): void
    {
        $data = $request->validate([
            'username' => ['required', 'alpha_dash', 'max:50', Rule::unique('users')->ignore($user->id)],
            'full_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('users')->ignore($user->id)],
            'password' => [$user->exists ? 'nullable' : 'required', 'string', 'min:8', 'max:72', 'confirmed'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=6000,max_height=6000'],
        ]);
        unset($data['avatar']);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $old = $user->avatar;
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $images->store($request->file('avatar'), 'avatars', 256);
        }
        $user->fill($data)->save();
        if (isset($data['avatar']) && $old) {
            Storage::disk('public')->delete($old);
        }
    }

    public function store(Request $request, DisplayImage $images)
    {
        $this->save($request, new User, $images);

        return to_route('users.index')->with('success', 'Staff member added. They can now sign in with their username.');
    }

    public function update(Request $request, User $user, DisplayImage $images)
    {
        abort_unless($user->role === 'staff', 404);
        $this->save($request, $user, $images);

        return to_route('users.index')->with('success', 'Staff member updated.');
    }

    public function destroy(Request $request, User $user)
    {
        abort_unless($user->role === 'staff', 404);
        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'You cannot remove your own signed-in account.']);
        }
        $user->delete();

        return to_route('users.index')->with('success', 'Staff account removed. Past sales are preserved.');
    }

    public function approve(User $user)
    {
        abort_unless($user->role === 'staff' && $user->status === 'pending', 404);
        $user->update(['status' => 'active']);

        return to_route('users.index')->with('success', 'Staff registration approved.');
    }
}
