<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, SoftDeletes;

    const UPDATED_AT = null;

    protected $fillable = ['username', 'full_name', 'avatar', 'password', 'email', 'role', 'status', 'customer_id'];

    public function isActiveStaff(): bool
    {
        return $this->role === 'staff' && $this->status === 'active';
    }

    protected $hidden = ['password', 'remember_token'];

    protected $attributes = ['role' => 'staff', 'status' => 'active'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }
}
