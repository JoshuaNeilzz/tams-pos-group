<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['product_id', 'customer_id', 'sold_by', 'quantity', 'total_price', 'product_name', 'customer_name', 'staff_name'];

    protected function casts(): array
    {
        return ['total_price' => 'decimal:2'];
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'sold_by')->withTrashed();
    }
}
