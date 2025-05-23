<?php //ProductController.php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index()
    {
        // Get only products that belong to the authenticated user
        $products = Product::with('category', 'brand', 'product_images')
                          ->where('user_id', Auth::id())
                          ->get();
        
        $brands = Brand::get();
        $categories = Category::get();

        return Inertia::render(
            'Admin/Product/Index',
            [
                'products' => $products,
                'brands' => $brands,
                'categories' => $categories
            ]
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'price' => 'required|numeric',
            'title' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            // other fields...
        ]);

        $product = new Product;
        $product->title = $request->title;
        $product->price = floatval($request->price); // cast to float
        $product->quantity = $request->quantity;
        $product->description = $request->description;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $product->user_id = Auth::id(); // Associate product with authenticated user
        $product->save();

        //check if product has images upload 
        if ($request->hasFile('product_images')) {
            $productImages = $request->file('product_images');
            foreach ($productImages as $image) {
                // Generate a unique name for the image using timestamp and random string
                $uniqueName = time() . '-' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                // Store the image in the public folder with the unique name
                $image->move('product_images', $uniqueName);
                // Create a new product image record with the product_id and unique name
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => 'product_images/' . $uniqueName,
                ]);
            }
        }
        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    //update 
    public function update(Request $request, $id)
    {
        // Find product that belongs to the authenticated user
        $product = Product::where('id', $id)
                         ->where('user_id', Auth::id())
                         ->firstOrFail();

        $request->validate([
            'price' => 'required|numeric',
            'title' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
        ]);

        $product->title = $request->title;
        $product->price = $request->price;
        $product->quantity = $request->quantity;
        $product->description = $request->description;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        
        // Check if product images were uploaded
        if ($request->hasFile('product_images')) {
            $productImages = $request->file('product_images');
            // Loop through each uploaded image
            foreach ($productImages as $image) {
                // Generate a unique name for the image using timestamp and random string
                $uniqueName = time() . '-' . Str::random(10) . '.' . $image->getClientOriginalExtension();

                // Store the image in the public folder with the unique name
                $image->move('product_images', $uniqueName);

                // Create a new product image record with the product_id and unique name
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => 'product_images/' . $uniqueName,
                ]);
            }
        }
        $product->update();
        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function deleteImage($id)
    {
        // Get the image and ensure it belongs to a product owned by the authenticated user
        $image = ProductImage::whereHas('product', function($query) {
            $query->where('user_id', Auth::id());
        })->where('id', $id)->firstOrFail();
        
        $image->delete();
        return redirect()->route('admin.products.index')->with('success', 'Image deleted successfully.');
    }

    public function destory($id)
    {
        // Find and delete only products that belong to the authenticated user
        $product = Product::where('id', $id)
                         ->where('user_id', Auth::id())
                         ->firstOrFail();
        
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

        public function show(Product $product)
    {
        return Inertia::render('Products/Show', [
            'product' => $product->load(['product_images', 'category', 'brand'])
        ]);
    }
}