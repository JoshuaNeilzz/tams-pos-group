<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\DisplayImage;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = substr((string) $request->query('q', ''), 0, 100);

        return view('products', ['title' => 'Products', 'search' => $search,
            'products' => Product::when($search !== '', fn ($q) => $q->where('name', 'like', '%'.$search.'%'))->latest('id')->paginate(12)->withQueryString()]);
    }

    public function create()
    {
        return view('products-form', ['title' => 'Add product', 'product' => null]);
    }

    public function edit(Product $product)
    {
        return view('products-form', ['title' => 'Edit product', 'product' => $product]);
    }

    private function data(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:99999999.99'],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=6000,max_height=6000'],
        ]);
    }

    public function store(Request $request, DisplayImage $images)
    {
        $data = $this->data($request);
        unset($data['image']);
        if ($request->hasFile('image')) {
            $data['image'] = $images->store($request->file('image'), 'products', 640);
        }
        Product::create($data);

        return to_route('products.index')->with('success', 'Product added to your catalog.');
    }

    public function update(Request $request, Product $product, DisplayImage $images)
    {
        $data = $this->data($request);
        unset($data['image']);
        $old = $product->image;
        if ($request->hasFile('image')) {
            $data['image'] = $images->store($request->file('image'), 'products', 640);
        }
        $product->update($data);
        if (isset($data['image']) && $old) {
            $images->delete($old);
        }

        return to_route('products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return to_route('products.index')->with('success', 'Product archived. Past sales are preserved.');
    }
}
