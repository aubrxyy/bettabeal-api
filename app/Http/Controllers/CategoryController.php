<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryController extends Controller
{

    /**
     * Display a listing of categories
     */
    public function index()
    {
        try {
            $categories = Category::active()
                                ->ordered()
                                ->get();

            return response()->json([
                'code' => '000',
                'status' => 'success',
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'status' => 'error',
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'category_name' => 'required|string|max:255|unique:categories,category_name',
                'description' => 'nullable|string',
                'icon' => 'nullable|string',
                'order' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => '422',
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            if (!$user || $user->role !== 'admin') {
                return response()->json([
                    'code' => '403',
                    'status' => 'error',
                    'message' => 'Unauthorized access',
                    'errors' => ['role' => ['Only admin can create categories']]
                ], 403);
            }

            $category = Category::create([
                'category_name' => $request->category_name,
                'slug' => Str::slug($request->category_name),
                'description' => $request->description,
                'icon' => $request->icon,
                'order' => $request->order ?? 0,
                'is_active' => $request->is_active ?? true
            ]);

            DB::commit();

            return response()->json([
                'code' => '000',
                'status' => 'success',
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => '500',
                'status' => 'error',
                'message' => 'Failed to create category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified category
     */
    public function show($slug)
    {
        try {
            $category = Category::where('slug', $slug)
                              ->active()
                              ->firstOrFail();

            return response()->json([
                'code' => '000',
                'status' => 'success',
                'data' => $category
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'code' => '404',
                'status' => 'error',
                'message' => 'Category not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'status' => 'error',
                'message' => 'Failed to fetch category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, $slug)
    {
        DB::beginTransaction();
        try {
            $category = Category::where('slug', $slug)->firstOrFail();

            $validator = Validator::make($request->all(), [
                'category_name' => 'required|string|max:255|unique:categories,category_name,' . $category->category_id . ',category_id',
                'description' => 'nullable|string',
                'icon' => 'nullable|string',
                'order' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => '422',
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            if (!$user || $user->role !== 'admin') {
                return response()->json([
                    'code' => '403',
                    'status' => 'error',
                    'message' => 'Unauthorized access',
                    'errors' => ['role' => ['Only admin can update categories']]
                ], 403);
            }

            // Update data termasuk slug jika category_name berubah
            $updateData = [
                'category_name' => $request->category_name,
                'description' => $request->description,
                'icon' => $request->icon,
                'order' => $request->order ?? $category->order,
                'is_active' => $request->is_active ?? $category->is_active
            ];

            // Update slug jika category_name berubah
            if ($request->category_name !== $category->category_name) {
                $updateData['slug'] = Str::slug($request->category_name);
            }

            $category->update($updateData);

            DB::commit();

            // Refresh model untuk mendapatkan data terbaru
            $category = $category->fresh();

            return response()->json([
                'code' => '000',
                'status' => 'success',
                'message' => 'Category updated successfully',
                'data' => $category
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'code' => '404',
                'status' => 'error',
                'message' => 'Category not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => '500',
                'status' => 'error',
                'message' => 'Failed to update category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified category
     */
    public function destroy($slug)
    {
        DB::beginTransaction();
        try {
            $category = Category::where('slug', $slug)->firstOrFail();

            // Validasi akses admin
            if (!auth()->user()?->isAdmin()) {
                return response()->json([
                    'code' => '403',
                    'status' => 'error',
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Nonaktifkan kategori
            $category->update(['is_active' => false]);
            
            DB::commit();
            return response()->json([
                'code' => '000',
                'status' => 'success',
                'message' => 'Category has been deactivated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => '500',
                'status' => 'error',
                'message' => 'Failed to deactivate category'
            ], 500);
        }
    }

    /**
     * Get products by category
     */
    public function getProducts($slug, Request $request)
    {
        try {
            $category = Category::where('slug', $slug)
                              ->active()
                              ->firstOrFail();

            $query = $category->products()
                            ->with(['images', 'seller'])
                            ->when($request->search, function($query, $search) {
                                $query->where('name', 'like', "%{$search}%");
                            })
                            ->when($request->min_price, function($query, $price) {
                                $query->where('price', '>=', $price);
                            })
                            ->when($request->max_price, function($query, $price) {
                                $query->where('price', '<=', $price);
                            })
                            ->when($request->sort, function($query, $sort) {
                                switch($sort) {
                                    case 'price_low':
                                        $query->orderBy('price', 'asc');
                                        break;
                                    case 'price_high':
                                        $query->orderBy('price', 'desc');
                                        break;
                                    case 'newest':
                                        $query->latest();
                                        break;
                                    default:
                                        $query->orderBy('name');
                                }
                            });

            $products = $query->paginate($request->per_page ?? 12);

            return response()->json([
                'code' => '000',
                'status' => 'success',
                'data' => [
                    'category' => $category,
                    'products' => $products
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'code' => '404',
                'status' => 'error',
                'message' => 'Category not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'status' => 'error',
                'message' => 'Failed to fetch products',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
