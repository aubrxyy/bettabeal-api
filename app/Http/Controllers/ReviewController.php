<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['user', 'product', 'images', 'sellerResponse'])
            ->withCount(['likes', 'comments']);

        // Filter berdasarkan rating
        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }

        // Filter verified purchase
        if ($request->has('verified')) {
            $query->where('purchase_verified', true);
        }

        // Filter with images
        if ($request->has('with_images')) {
            $query->has('images');
        }

        // Sorting
        switch ($request->sort) {
            case 'newest':
                $query->latest('review_date');
                break;
            case 'helpful':
                $query->orderBy('helpful_count', 'desc');
                break;
            case 'rating_high':
                $query->orderBy('rating', 'desc');
                break;
            case 'rating_low':
                $query->orderBy('rating', 'asc');
                break;
            default:
                $query->latest('review_date');
        }

        $reviews = $query->paginate(10);

        return response()->json([
            'data' => $reviews,
            'meta' => [
                'total' => Review::count(),
                'average_rating' => Review::avg('rating')
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|between:1,5',
            'review_text' => 'required|string|min:10|max:1000',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // Cek apakah user sudah membeli produk
        $order = Order::where('user_id', auth()->id())
            ->whereHas('items', function($query) use ($request) {
                $query->where('product_id', $request->product_id);
            })
            ->where('status', 'completed')
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'You can only review products that you have purchased'
            ], 403);
        }

        // Cek review ganda
        $existingReview = Review::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->exists();

        if ($existingReview) {
            return response()->json([
                'message' => 'You have already reviewed this product'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $review = Review::create([
                'user_id' => auth()->id(),
                'product_id' => $request->product_id,
                'order_id' => $order->id,
                'rating' => $request->rating,
                'review_text' => $request->review_text,
                'purchase_verified' => true,
                'review_date' => now(),
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('review-images', 'public');
                    $review->images()->create([
                        'image_path' => $path,
                        'is_main' => $review->images()->count() === 0
                    ]);
                }
            }

            DB::commit();
            return response()->json($review->load('images'), 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create review'], 500);
        }
    }

    public function show(Review $review)
    {
        return response()->json(
            $review->load(['user', 'product', 'images', 'comments.user', 'sellerResponse'])
        );
    }

    public function update(Request $request, Review $review)
    {
        if ($review->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'review_text' => 'required|string|min:10|max:1000',
        ]);

        $review->update([
            'rating' => $request->rating,
            'review_text' => $request->review_text,
        ]);

        return response()->json($review->load('images'));
    }

    public function destroy(Review $review)
    {
        if ($review->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete associated images from storage
        foreach ($review->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $review->delete();
        return response()->json(['message' => 'Review deleted successfully']);
    }

    public function uploadImages(Request $request, Review $review)
    {
        if ($review->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $uploadedImages = [];
        foreach ($request->file('images') as $image) {
            $path = $image->store('review-images', 'public');
            $uploadedImages[] = $review->images()->create([
                'image_path' => $path,
                'is_main' => $review->images()->count() === 0
            ]);
        }

        return response()->json($uploadedImages, 201);
    }

    public function deleteImage(Review $review, $imageId)
    {
        if ($review->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $image = $review->images()->findOrFail($imageId);
        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return response()->json(['message' => 'Image deleted successfully']);
    }

    public function getProductReviews(Product $product)
    {
        $reviews = $product->reviews()
            ->with(['user', 'images', 'sellerResponse'])
            ->withCount(['likes', 'comments'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'reviews' => $reviews,
            'summary' => [
                'average_rating' => $product->reviews()->avg('rating'),
                'total_reviews' => $product->reviews()->count(),
                'rating_breakdown' => [
                    5 => $product->reviews()->where('rating', 5)->count(),
                    4 => $product->reviews()->where('rating', 4)->count(),
                    3 => $product->reviews()->where('rating', 3)->count(),
                    2 => $product->reviews()->where('rating', 2)->count(),
                    1 => $product->reviews()->where('rating', 1)->count(),
                ]
            ]
        ]);
    }
}
