<?php

use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CustomerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(RegisterController::class)->group(callback: function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});

Route::middleware('my_auth')->group(callback: function () {
    Route::get('/getUser', [RegisterController::class, 'getInfoUser']);
    Route::get('/logout', [RegisterController::class, 'logout']);
    //Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/store_products', [ProductController::class, 'storeProduct']);
    Route::post('/update_products/{id}', [ProductController::class, 'updateProduct']);
    Route::post('/get_products/{id}', [ProductController::class, 'getProducts']);
    Route::post('/delete_product/{id}', [ProductController::class, 'deleteProduct']);
    //Categories
    Route::get('/categories', [CategoriesController::class, 'index']);
    Route::post('/store_categories', [CategoriesController::class, 'storeCategories']);
    Route::post('/get_category/{id}', [CategoriesController::class, 'getCategory']);
    Route::post('/update_categories/{id}', [CategoriesController::class, 'updateCategory']);
    Route::post('/delete_category/{id}', [CategoriesController::class, 'deleteCategory']);
    //Customer
    Route::get('/customer', [CustomerController::class, 'index']);
    Route::post('/store_customer', [CustomerController::class, 'storeCustomer']);
    Route::post('/update_customer/{id}', [CustomerController::class, 'updateCustomer']);
    Route::post('/delete_customer/{id}', [CustomerController::class, 'deleteCustomer']);
});

//Route::get('/products', [ProductController::class, 'index']);
