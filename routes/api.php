<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\MenuItemAvailabilityController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NotificationController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgot']);
Route::post('/reset-password', [AuthController::class, 'reset']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});
// 🔐 Authenticated users can manage their own info
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/me', [UserController::class, 'me']);           // Get own info
    Route::put('/me', [UserController::class, 'updateMe']);     // Update own info
    Route::delete('/me', [UserController::class, 'deleteMe']);  // Delete own account
});


Route::middleware(['auth:sanctum', 'role:Super Admin,Admin'])->prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);        // List all users
        Route::get('/', [UserController::class, 'index']);           // List all users
    Route::get('/{user}', [UserController::class, 'show']);      // ✅ Get user by ID
    Route::post('/', [UserController::class, 'store']);          // Create user
    Route::put('/{user}', [UserController::class, 'update']);    // Update user
    Route::delete('/{user}', [UserController::class, 'destroy']); // Delete user
});
Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'show']);
    Route::put('/', [ProfileController::class, 'update']);
    Route::post('/avatar', [ProfileController::class, 'updateAvatar']);
});

Route::middleware(['auth:sanctum', 'role:Super Admin,Admin'])->prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::post('/', [CategoryController::class, 'store']);
    Route::get('/{category}', [CategoryController::class, 'show']);
    Route::put('/{category}', [CategoryController::class, 'update']);
    Route::delete('/{category}', [CategoryController::class, 'destroy']);
});


Route::middleware(['auth:sanctum', 'role:Super Admin,Admin'])->prefix('menu-items')->group(function () {
    Route::get('/', [MenuItemController::class, 'index']);
    Route::post('/', [MenuItemController::class, 'store']);
    Route::get('/{menuItem}', [MenuItemController::class, 'show']);
    Route::put('/{menuItem}', [MenuItemController::class, 'update']);
    Route::delete('/{menuItem}', [MenuItemController::class, 'destroy']);
});
Route::get('/menu-items/search', [MenuItemController::class, 'search']);
Route::middleware('auth:sanctum')->get('/menu-items/{id}/availability', [MenuItemAvailabilityController::class, 'check']);


Route::middleware(['auth:sanctum', 'role:Super Admin,Admin'])->group(function () {
    Route::apiResource('ingredients', IngredientController::class);

    Route::prefix('stocks')->group(function () {
        Route::get('/', [StockController::class, 'index']);
        Route::post('/', [StockController::class, 'store']);
        Route::get('/{stock}', [StockController::class, 'show']);
    });
});


Route::middleware(['auth:sanctum', 'role:Super Admin,Admin'])->prefix('recipes')->group(function () {
    Route::get('/', [RecipeController::class, 'index']);
    Route::post('/', [RecipeController::class, 'store']);
    Route::delete('/{recipe}', [RecipeController::class, 'destroy']);
});
Route::middleware('auth:sanctum')->prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::get('/{order}', [OrderController::class, 'show']);
    Route::post('/', [OrderController::class, 'store']);
    Route::post('/{order}/pay', [OrderController::class, 'pay']);
    Route::post('/{order}/cancel', [OrderController::class, 'cancel']);
});

Route::middleware(['auth:sanctum', 'role:Super Admin,Admin'])->prefix('order-items')->group(function () {
    Route::get('/', [OrderItemController::class, 'index']);
    Route::get('/{orderItem}', [OrderItemController::class, 'show']);
    Route::delete('/{orderItem}', [OrderItemController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'role:Super Admin,Admin'])->prefix('reports')->group(function () {
    Route::get('/summary', [ReportController::class, 'summary']);
    Route::get('/top-items', [ReportController::class, 'topItems']);
    Route::get('/stats', [ReportController::class, 'stats']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('discounts')->group(function () {
        Route::get('/', [DiscountController::class, 'index']);
        Route::post('/', [DiscountController::class, 'store']);
        Route::get('/{discount}', [DiscountController::class, 'show']);
        Route::put('/{discount}', [DiscountController::class, 'update']);
        Route::delete('/{discount}', [DiscountController::class, 'destroy']);
    });
});

Route::middleware('auth:sanctum')->prefix('payments')->group(function () {
    Route::get('/', [PaymentController::class, 'index']);
    Route::post('/', [PaymentController::class, 'store']);
    Route::get('/{payment}', [PaymentController::class, 'show']);
    Route::post('/{payment}/logs', [PaymentController::class, 'log']);
});
Route::post('/orders/ai-reorder', [OrderController::class, 'aiReorder'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::post('/', [NotificationController::class, 'store']);
    Route::put('/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/{notification}', [NotificationController::class, 'destroy']);
});
