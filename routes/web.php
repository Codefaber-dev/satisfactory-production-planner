<?php

use App\Helpers\ProductionCalculator;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProductionLineController;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//if (config('app.env') !== 'production')
//    auth()->loginUsingId(1);

Route::get('calc/{ingredient}/{qty}/{recipe}',function($ingredient,$qty,$recipe) {
    return ProductionCalculator::calc($ingredient, $qty, $recipe);
});

Route::post('favorites/sub/{recipe}', [ProductionController::class, 'addSubFavorite']);
Route::post('favorites/{recipe}', [ProductionController::class, 'addFavorite']);


Route::get('calc/{ingredient}/{qty}',function($ingredient,$qty) {
    return ProductionCalculator::calc($ingredient, $qty);
});

Route::redirect('/', 'dashboard');


Route::get('/dashboard', [ProductionController::class,'index'])->name('dashboard');
Route::get('/dashboard/{ingredient}/{qty}/{recipe}/{variant}', [ProductionController::class,'show'])->name('dashboard.show');
Route::get('/dashboard/{ingredient}/{qty}/{recipe}', [ProductionController::class,'show'])->name('dashboard.show');

Route::get('/newyield/{ingredient}/{qty}/{recipe}/{variant}', [ProductionController::class,'newYield'])->name('dashboard.newYield');
Route::get('/newyield/{ingredient}/{qty}/{recipe}', [ProductionController::class,'newYield'])->name('dashboard.newYield');

Route::get('/factories', [ProductionLineController::class,'index'])->name('factories');
Route::post('/factories', [ProductionLineController::class,'store']);
Route::patch('/factories/{id}', [ProductionLineController::class,'update']);
Route::delete('/factories/{id}', [ProductionLineController::class,'destroy']);
