<?php

namespace App\Http\Controllers;

use App\Models\GalleryProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GalleryProductController extends Controller
{
    // Create Gallery Product
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,product_id',
            'image_url' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => '101',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::check()) {
            return response()->json([
                'code' => '102',
                'message' => 'Unauthorized access, please login first',
            ], 403);
        }

        $user = Auth::user();

        if (!$user->isSeller()) {
            return response()->json([
                'code' => '103',
                'message' => 'Only seller can add gallery products',
            ], 403);
        }

        $seller = $user->seller;

        if (!$seller) {
            return response()->json([
                'code' => '104',
                'message' => 'Seller data not found for logged in user',
            ], 422);
        }

        $galleryProduct = GalleryProduct::create([
            'product_id' => $request->product_id,
            'seller_id' => $seller->seller_id,
            'image_url' => $request->image_url,
            'uploaded_at' => now(),
        ]);

        return response()->json([
            'code' => '000',
            'message' => 'Gallery product created successfully!',
            'gallery_product' => $galleryProduct,
        ], 201);
    }

    // Get All Gallery Products
    public function index()
    {
        $galleryProducts = GalleryProduct::all();
        return response()->json([
            'code' => '000',
            'gallery_products' => $galleryProducts
        ], 200);
    }

    // Get Single Gallery Product by ID
    public function show($id)
    {
        $galleryProduct = GalleryProduct::find($id);

        if (!$galleryProduct) {
            return response()->json([
                'code' => '404',
                'message' => 'Gallery product not found'
            ], 404);
        }

        return response()->json([
            'code' => '000',
            'gallery_product' => $galleryProduct
        ], 200);
    }

    // Update Gallery Product
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'sometimes|required|exists:products,product_id',
            'image_url' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => '101',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::check()) {
            return response()->json([
                'code' => '102',
                'message' => 'Unauthorized access, please login first',
            ], 403);
        }

        $galleryProduct = GalleryProduct::find($id);

        if (!$galleryProduct) {
            return response()->json([
                'code' => '404',
                'message' => 'Gallery product not found',
            ], 404);
        }

        if (Auth::user()->seller->seller_id !== $galleryProduct->seller_id) {
            return response()->json([
                'code' => '103',
                'message' => 'Action not allowed, you are not the owner of this gallery product'
            ], 403);
        }

        try {
            $galleryProduct->update([
                'product_id' => $request->product_id ?? $galleryProduct->product_id,
                'image_url' => $request->image_url ?? $galleryProduct->image_url,
            ]);

            return response()->json([
                'code' => '000',
                'message' => 'Gallery product updated successfully',
                'gallery_product' => $galleryProduct,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'message' => 'Failed to update gallery product',
            ], 500);
        }
    }

    // Delete Gallery Product
    public function destroy($id)
    {
        if (!Auth::check()) {
            return response()->json([
                'code' => '102',
                'message' => 'Unauthorized access, please login first',
            ], 403);
        }

        $galleryProduct = GalleryProduct::find($id);

        if (!$galleryProduct) {
            return response()->json([
                'code' => '404',
                'message' => 'Gallery product not found',
            ], 404);
        }

        if (Auth::user()->seller->seller_id !== $galleryProduct->seller_id) {
            return response()->json([
                'code' => '103',
                'message' => 'Action not allowed, you are not the owner of this gallery product'
            ], 403);
        }

        $galleryProduct->delete();

        return response()->json([
            'code' => '000',
            'message' => 'Gallery product deleted successfully',
        ], 200);
    }
}
