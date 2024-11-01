<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::all();
        return response()->json([
            'code' => '000',
            'articles' => $articles
        ], 200);
    }

    public function store(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
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
                'message' => 'Only seller can add articles',
            ], 403);
        }

        $seller = $user->seller;

        if (!$seller) {
            return response()->json([
                'code' => '104',
                'message' => 'Seller data not found for logged in user',
            ], 422);
        }

        $article = Article::create([
            'title' => $request->title,
            'content' => $request->content,
            'seller_id' => $user->seller->seller_id,
            'created_at' => now()
        ]);

        return response()->json([
            'code' => '000',
            'message' => 'Article created successfully!',
            'article' => $article,
        ], 201);
    }

    public function show($id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'code' => '404',
                'message' => 'Article not found'
            ], 404);
        }

        return response()->json([
            'code' => '000',
            'article' => $article
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
  
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

        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'code' => '404',
                'message' => 'Article not found',
            ], 404);
        }

        if (Auth::user()->seller->seller_id !== $article->seller_id) {
            return response()->json([
                'code' => '103',
                'message' => 'Action not allowed, you are not the owner of this article'
            ], 403);
        }

        try {
            $article->update([
                'title' => $request->title ?? $article->title,
                'content' => $request->content ?? $article->content,

            ]);

            return response()->json([
                'code' => '000',
                'message' => 'Article updated successfully',
                'article' => $article,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'message' => 'Failed to update article',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (!Auth::check()) {
            return response()->json([
                'code' => '102',
                'message' => 'Unauthorized access, please login first',
            ], 403);
        }

        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'code' => '404',
                'message' => 'Article not found',
            ], 404);
        }

        if (Auth::user()->seller->seller_id !== $article->seller_id) {
            return response()->json([
                'code' => '103',
                'message' => 'Action not allowed, you are not the owner of this article'
            ], 403);
        }

        $article->delete();

        return response()->json([
            'code' => '000',
            'message' => 'Article deleted successfully',
        ], 200);
    }
}
