<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductListController extends Controller
{
    public function index()
    {
        $products = Product::with('category', 'brand', 'product_images');
        $filterProducts = $products->filtered()->paginate(9)->withQueryString();

        $categories = Category::get();
        $brands = Brand::get();
        
        return Inertia::render(
            'User/ProductList',
            [
                'categories'=>$categories,
                'brands'=>$brands,
                'products' => ProductResource::collection($filterProducts)
            ]
        );
    }

    public function adminProducts()
    {
        $products = Product::whereHas('user', function ($query) {
            $query->where('is_admin', 1);
        })->with('user')->latest()->get();

        return Inertia::render('User/Stores', [
            'products' => $products
        ]);
    }

}
