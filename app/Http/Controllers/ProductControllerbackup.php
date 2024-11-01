<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
	// Create Product
	public function store(Request $request)
	{
		// Validasi input produk
		$validator = Validator::make($request->all(), [       
			'product_name' => 'required|string|max:255',
			'description' => 'required|string',
			'price' => 'required|numeric',
			'stock_quantity' => 'required|integer',
			'category_id' => 'required|exists:categories,category_id',
		]);


		if ($validator->fails()) {
			return response()->json([
				'code' => '101',
				'message' => 'Validation errors',
				'errors' => $validator->errors()
			], 422);
		}

                if (!Auth::user()){
                        return response()->json([
                                'code' => '102',
                                'message' => 'Tidak punya access login',
                        ], 422);
                }

		$seller = Auth::user();

		// Buat produk baru
		$product = Product::create([
			'seller_id' => $seller->seller_id, // Ambil seller_id dari user yang sedang login
			'category_id' => $request->category_id,
			'product_name' => $request->product_name,
			'description' => $request->description,
			'price' => $request->price,
			'stock_quantity' => $request->stock_quantity,
		]);

		return response()->json([
        'code' => '000',
			'message' => 'Product created successfully!',
			'product' => $product,
		], 201);
	}

	// Get All Products
	public function index()
	{
		$products = Product::all();

		return response()->json([
        'code' => '000',
			'products' => $products
		], 200);
	}

	// Get Single Product by ID
	public function show($id)
	{
		$product = Product::find($id);
//        $product = Product::where("product_id","=",$id)->firstOrFail();

		if (!$product) {
			return response()->json([
        'code' => '102',
				'message' => 'Product not found!'
			], 404);
		}

		return response()->json([
        'code' => '000',
			'product' => $product
		], 200);
	}

	// Update Product
	public function update(Request $request, $id)
	{
		// Validasi input produk
		$request->validate([
			'product_name' => 'sometimes|required|string|max:255',
			'description' => 'sometimes|required|string',
			'price' => 'sometimes|required|numeric',
			'stock_quantity' => 'sometimes|required|integer',
			'category_id' => 'sometimes|required|exists:categories,category_id',
		]);

		$product = Product::find($id);

		if (!$product) {
			return response()->json([
        'code' => '102',
				'message' => 'Product not found!'
			], 404);
		}

		// Pastikan penjual yang login adalah pemilik produk
		if (Auth::user()->user_id !== $product->seller_id) {
			return response()->json([
        'code' => '101',
				'message' => 'Unauthorized action!'
			], 403);
		}

		// Update data produk
		$product->update($request->all());

		return response()->json([
        'code' => '000',
			'message' => 'Product updated successfully!',
			'product' => $product
		], 200);
	}

	// Delete Product
	public function destroy($id)
	{
		$product = Product::find($id);

		if (!$product) {
			return response()->json([
        'code' => '102',
				'message' => 'Product not found!'
			], 404);
		}

		// Pastikan penjual yang login adalah pemilik produk
		if (Auth::user()->user_id !== $product->seller_id) {
			return response()->json([
        'code' => '101',
				'message' => 'Unauthorized action!'
			], 403);
		}

		// Hapus produk
		$product->delete();

		return response()->json([
        'code' => '000',
			'message' => 'Product deleted successfully!'
		], 200);
	}
}

