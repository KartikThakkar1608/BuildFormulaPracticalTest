<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\CategoryController;
use App\Http\Controllers\Api\v1\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(['prefix' => 'v1', 'middleware' => ['XssSanitization']], function () {
    // This group will be protected by JWT and have a token TTL of 1 hour
    // This group will manage users
    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/signup', 'signUp');
    });
    Route::group(['middleware' => ['UserAuthentication']], function () {
        // Category Master Routes
        Route::controller(CategoryController::class)->group(function () {
            Route::post('/create-category', 'createCategory');
            Route::post('/edit-category', 'editCategory');
            Route::post('/category-list', 'categoryList');
            Route::post('/view-category', 'viewCategory');
            Route::post('/category-status-change', 'categoryStatusChange');
            Route::post('/category-delete', 'categoryDelete');
        });
        Route::controller(ProductController::class)->group(function () {
            Route::post('/upload-image', 'uploadImage');
            Route::post('/create-product', 'createProduct');
            Route::post('/edit-product', 'editProduct');
            Route::post('/product-list', 'productList');
            Route::post('/view-product', 'viewProduct');
            Route::post('/delete-product', 'deleteProduct');
        });
    });
});
