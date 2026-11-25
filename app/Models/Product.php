<?php

namespace App\Models;

use App\Support\SnackCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    const UPDATED_AT = null;

    protected $fillable = ['name', 'price', 'stock_quantity', 'image'];

    public function catalogImageUrl(): string
    {
        return $this->image ? url('media/'.$this->image) : asset('images/snacks/'.SnackCatalog::sketch($this->name).'.svg');
    }

    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }
}
