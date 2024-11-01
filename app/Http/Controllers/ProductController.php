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
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        if (!$user || $user->role !== 'seller') {
            return response()->json([
                'code' => '102',
                'message' => 'Tidak punya akses login',
            ], 403);
        }

        // Ambil seller_id dari tabel sellers berdasarkan user_id
        $seller = \DB::table('sellers')->where('user_id', $user->user_id)->first();

        if (!$seller) {
            return response()->json([
                'code' => '103',
                'message' => 'Seller ID tidak ditemukan pada pengguna yang login.',
            ], 400);
        }

        // Buat produk baru
        $product = Product::create([
            'seller_id' => $seller->seller_id, // Ambil seller_id dari tabel sellers
            'category_id' => $request->category_id,
            'product_name' => $request->product_name,
            'description' => $request->description,
            'price' => $request->price,
            'stock_quantity' => $request->stock_quantity,
        ]);

        return response()->json([
            'code' => '000',
            'message' => 'Produk berhasil dibuat!',
            'product' => $product,
        ], 201);
    }

    // Get All Products
    public function index()
    {
        // Hapus pengecekan auth, langsung tampilkan semua produk untuk guest
        if (!Auth::user()) {
            $products = Product::all();
            return response()->json([
                'code' => '000',
                'products' => $products
            ], 200);
        }

        // Logic untuk user yang sudah login
        $user = Auth::user();
        if ($user->role === 'customer' || !$user->role) {
            $products = Product::all();
        } elseif ($user->role === 'seller') {
            $seller = \DB::table('sellers')->where('user_id', $user->user_id)->first();
            if ($seller) {
                $products = Product::where('seller_id', $seller->seller_id)->get();
            } else {
                return response()->json([
                    'code' => '103',
                    'message' => 'Seller tidak ditemukan untuk user yang login.',
                ], 404);
            }
        }

        return response()->json([
            'code' => '000',
            'products' => $products
        ], 200);
    }

    // Get Single Product by ID
    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'code' => '102',
                'message' => 'Produk tidak ditemukan!'
            ], 404);
        }

        // Guest user bisa melihat detail produk
        if (!Auth::user()) {
            return response()->json([
                'code' => '000',
                'product' => $product
            ], 200);
        }

        // Logic untuk user yang sudah login
        $user = Auth::user();
        if ($user->role === 'customer' || !$user->role) {
            return response()->json([
                'code' => '000',
                'product' => $product
            ], 200);
        } elseif ($user->role === 'seller') {
            $seller = \DB::table('sellers')->where('user_id', $user->user_id)->first();
            if ($seller && $seller->seller_id !== $product->seller_id) {
                return response()->json([
                    'code' => '101',
                    'message' => 'Aksi tidak diizinkan'
                ], 403);
            }
            return response()->json([
                'code' => '000',
                'product' => $product
            ], 200);
        }
    }

    // Update Product
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'seller') {
            return response()->json([
                'code' => '102',
                'message' => 'Tidak punya akses login',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'product_name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric',
            'stock_quantity' => 'sometimes|required|integer',
            'category_id' => 'sometimes|required|exists:categories,category_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => '101',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'code' => '102',
                'message' => 'Produk tidak ditemukan!'
            ], 404);
        }

        // Pastikan penjual yang login adalah pemilik produk
        $seller = \DB::table('sellers')->where('user_id', $user->user_id)->first();
        if (!$seller || $seller->seller_id !== $product->seller_id) {
            return response()->json([
                'code' => '101',
                'message' => 'Aksi tidak diizinkan'
            ], 403);
        }

        // Update data produk
        $product->update($request->only(['product_name', 'description', 'price', 'stock_quantity', 'category_id']));

        return response()->json([
            'code' => '000',
            'message' => 'Produk berhasil diperbarui!',
            'product' => $product
        ], 200);
    }

    // Delete Product
    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'seller') {
            return response()->json([
                'code' => '102',
                'message' => 'Tidak punya akses login',
            ], 403);
        }

        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'code' => '102',
                'message' => 'Produk tidak ditemukan!'
            ], 404);
        }

        // Pastikan penjual yang login adalah pemilik produk
        $seller = \DB::table('sellers')->where('user_id', $user->user_id)->first();
        if (!$seller || $seller->seller_id !== $product->seller_id) {
            return response()->json([
                'code' => '101',
                'message' => 'Aksi tidak diizinkan'
            ], 403);
        }

        // Hapus produk
        $product->delete();

        return response()->json([
            'code' => '000',
            'message' => 'Produk berhasil dihapus!'
        ], 200);
    }
}
