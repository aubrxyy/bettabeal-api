<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\GalleryProductController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewLikeController;
use App\Http\Controllers\ReviewCommentController;
use App\Http\Controllers\ReviewResponseController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\OrderController;

// Authentication Routes
Route::post('/register/customer', [AuthController::class, 'registerCustomer']);
Route::post('/register/seller', [AuthController::class, 'registerSeller']);
Route::post('/register/admin', [AuthController::class, 'registerAdmin']);
Route::post('/login', [AuthController::class, 'login']);

// Routes for seller with sanctum and seller middleware
Route::middleware(['auth:sanctum', 'seller'])->group(function () {
    Route::get('/home', function () {
        return response()->json(['message' => 'Selamat datang di Home Seller!']);
    });
});


// Review Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    
    // Review Images
    Route::post('/reviews/{review}/images', [ReviewController::class, 'uploadImages']);
    Route::delete('/reviews/{review}/images/{image}', [ReviewController::class, 'deleteImage']);
    
    // Review Likes
    Route::post('/reviews/{review}/like', [ReviewLikeController::class, 'like']);
    Route::delete('/reviews/{review}/like', [ReviewLikeController::class, 'unlike']);
    
    // Review Comments
    Route::post('/reviews/{review}/comments', [ReviewCommentController::class, 'store']);
    Route::put('/reviews/{review}/comments/{comment}', [ReviewCommentController::class, 'update']);
    Route::delete('/reviews/{review}/comments/{comment}', [ReviewCommentController::class, 'destroy']);
    
    // Seller Response to Review
    Route::post('/reviews/{review}/response', [ReviewResponseController::class, 'store']);
    Route::put('/reviews/{review}/response/{response}', [ReviewResponseController::class, 'update']);
    Route::delete('/reviews/{review}/response/{response}', [ReviewResponseController::class, 'destroy']);
});

// Public Review Routes
Route::get('/reviews', [ReviewController::class, 'index']);
Route::get('/reviews/{id}', [ReviewController::class, 'show']);
Route::get('/reviews/{review}/comments', [ReviewCommentController::class, 'index']);


// Profile Routes
Route::middleware('auth:sanctum')->group(function () {
    
    Route::put('/customer/biodata', [ProfileController::class, 'updateCustomer']);
    Route::post('/customer/biodata', [ProfileController::class, 'updateCustomer']);
    Route::put('/seller/biodata', [ProfileController::class, 'updateSeller']);
    Route::post('/seller/biodata', [ProfileController::class, 'updateSeller']);
});

Route::get('/customers', [ProfileController::class, 'indexCustomer']);
Route::get('/customers/{id}', [ProfileController::class, 'showCustomer']);
Route::get('/sellers', [ProfileController::class, 'indexSeller']);
Route::get('/sellers/{id}', [ProfileController::class, 'showSeller']);


// Category Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{slug}', [CategoryController::class, 'update']);
    Route::delete('categories/{slug}', [CategoryController::class, 'destroy']);
});
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{slug}', [CategoryController::class, 'show']);
Route::get('categories/{slug}/products', [CategoryController::class, 'getProducts']);


// Product Routes

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);



// Gallery Product Routes 
Route::get('gallery-products', [GalleryProductController::class, 'index'])->name('gallery-products.index');
// Route untuk menampilkan galeri produk berdasarkan ID
Route::get('gallery-products/{id}', [GalleryProductController::class, 'show'])->name('gallery-products.show');

Route::middleware('auth:sanctum')->group(function () {
    
    
    // Route untuk membuat galeri produk baru (hanya seller yang diizinkan)
    Route::post('gallery-products', [GalleryProductController::class, 'store'])->name('gallery-products.store');

    
    // Route untuk mengupdate galeri produk berdasarkan ID
    Route::put('gallery-products/{id}', [GalleryProductController::class, 'update'])->name('gallery-products.update');

    // Route untuk menghapus galeri produk berdasarkan ID
    Route::delete('gallery-products/{id}', [GalleryProductController::class, 'destroy'])->name('gallery-products.destroy');
});

// Comment Routes 

// Route publik untuk melihat komentar
Route::get('comments', [CommentController::class, 'index']);
Route::get('comments/{id}', [CommentController::class, 'show']);
Route::get('article/{article_id}/comments', [CommentController::class, 'getArticleComments']);
Route::get('comments/{comment_id}/replies', [CommentController::class, 'getReplies']);

Route::middleware('auth:sanctum')->group(function () {
// Route yang membutuhkan autentikasi
Route::post('article/{article_id}/comments', [CommentController::class, 'store']);
Route::put('comments/{id}', [CommentController::class, 'update']);
Route::delete('comments/{id}', [CommentController::class, 'destroy']);
});

// Article routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('article', [ArticleController::class, 'store']);
    Route::put('article/{id}', [ArticleController::class, 'update']);
    Route::delete('article/{id}', [ArticleController::class, 'destroy']);
});

Route::get('article', [ArticleController::class, 'index']);
Route::get('article/{id}', [ArticleController::class, 'show']);

//chat routes

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/send-message', [ChatController::class, 'sendMessage']);
    Route::get('/messages/{receiver_id}', [ChatController::class, 'getMessages']);
});

//order routes  
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/{order}', [OrderController::class, 'show']);
        Route::put('/{order}/status', [OrderController::class, 'updateStatus']);
        Route::post('/{order}/cancel', [OrderController::class, 'cancel']);
        Route::get('/history', [OrderController::class, 'getOrderHistory']);
        Route::get('/pending', [OrderController::class, 'getPendingOrders']);
    });
});






// Route::get('product_list', [ProductController::class, 'product_list']);
// Route::get('/products', [ProductController::class, 'index']);
// Route::post('/products', [ProductController::class, 'store']);
// Route::get('/products/{id}', [ProductController::class, 'show']);
// Route::put('/products/{id}', [ProductController::class, 'update']);
// Route::delete('/products/{id}', [ProductController::class, 'destroy']);




#use Illuminate\Http\Request;
#use Illuminate\Support\Facades\Route;
#
#Route::get('/user', function (Request $request) {
 #   return $request->user();
 #})->middleware('auth:sanctum');

// use App\Http\Controllers\AuthController;
// use App\Http\Controllers\ProductController;

// // Route untuk login
// Route::post('/login', [AuthController::class, 'login']);

// // Route untuk register (opsional)
// Route::post('register', [AuthController::class, 'register']);
// Authentication Routes

// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('/logout', [AuthController::class, 'logout']);



// Review Routes
Route::middleware('auth:sanctum')->group(function () {
    // Basic Review CRUD
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews/{review}', [ReviewController::class, 'show']);
    Route::put('/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);

    // Review Images
    Route::post('/reviews/{review}/images', [ReviewController::class, 'uploadImages']);
    Route::delete('/reviews/{review}/images/{imageId}', [ReviewController::class, 'deleteImage']);
});

// Public Routes
Route::get('/products/{product}/reviews', [ReviewController::class, 'getProductReviews']);


