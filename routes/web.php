<?php

use App\Helpers\ProductionCalculator;
use App\Helpers\UpdateSix;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\MultiProductionLineController;
use App\Http\Controllers\PowerPlanController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProductionLineController;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Request;
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

// redirect the old domain to the new
Route::domain('satisfactory.codefaber.dev')->group(function() {
    Route::permanentRedirect('{any}', str_replace("satisfactory.codefaber.dev","satisfactoryproductionplanner.com",Request::fullUrl()));
});

//if (config('app.env') !== 'production')
//    auth()->loginUsingId(1);

//Route::get('calc/{ingredient}/{qty}/{recipe}',function($ingredient,$qty,$recipe) {
//    return ProductionCalculator::calc($ingredient, $qty, $recipe);
//});

Route::get('/fix-recipes', function() {
    //r('Aluminum Scrap')->update(['base_per_min' => 360]);
    //r('Aluminum Scrap', force: true);
    //
    //r('Electric Motor')->addIngredient(i('Electromagnetic Control Rod'), 3.75);
    //r('Electric Motor', force: true);
    //
    //r('Seismic Nobelisk')->addIngredient(i('Crystal Oscillator'), 1.5);
    //r('Seismic Nobelisk', force: true);

    // r('Plutonium Fuel Rod')->update(['base_per_min' => 0.25]);
    // r('Plutonium Fuel Rod', force: true);
});

Route::get('/fix-recipes-u6', function() {
    UpdateSix::update();
});



//
//Route::get('testing', function() {
//    return energy('Unpackage Fuel');
//});

Route::get('/favorites', [FavoritesController::class,'index'])->name('favorites');
Route::post('/favorites/preset', [FavoritesController::class,'storePreset'])->name('favorites.storePreset');
Route::post('/favorites', [FavoritesController::class,'store'])->name('favorites.store');
Route::delete('/favorites-all', [FavoritesController::class,'destroyAll'])->name('favorites.destroyAll');
Route::delete('/favorites/{id}', [FavoritesController::class,'destroy'])->name('favorites.destroy');

Route::post('favorites/sub/{recipe}', [ProductionController::class, 'addSubFavorite']);
Route::post('favorites/{recipe}', [ProductionController::class, 'addFavorite']);

Route::get('/power',[PowerPlanController::class,'index'])->name('power.index');
Route::get('/power/{output}',[PowerPlanController::class,'show'])->name('power.show');

//Route::get('calc/{ingredient}/{qty}',function($ingredient,$qty) {
//    return ProductionCalculator::calc($ingredient, $qty);
//});

Route::redirect('/', 'dashboard');


Route::get('/dashboard', [ProductionController::class,'index'])->name('dashboard');
Route::get('/dashboard/multi', [ProductionController::class,'multi'])->name('dashboard.multi');
Route::get('/dashboard/{ingredient}/{qty}/{recipe}/{variant}', [ProductionController::class,'show'])->name('dashboard.show');
Route::get('/dashboard/{ingredient}/{qty}/{recipe}', [ProductionController::class,'show'])->name('dashboard.show');

Route::get('/newyield/multi', [ProductionController::class,'newYieldMulti'])->name('dashboard.newYield.multi');
Route::get('/newyield/{ingredient}/{qty}/{recipe}/{variant}', [ProductionController::class,'newYield'])->name('dashboard.newYield');
Route::get('/newyield/{ingredient}/{qty}/{recipe}', [ProductionController::class,'newYield'])->name('dashboard.newYield');

Route::post('/factories/multi',[MultiProductionLineController::class,'store']);
Route::patch('/factories/multi/{id}', [MultiProductionLineController::class,'update']);
Route::delete('/factories/multi/{id}', [MultiProductionLineController::class,'destroy']);

Route::get('/factories', [ProductionLineController::class,'index'])->name('factories');
Route::post('/factories', [ProductionLineController::class,'store']);
Route::patch('/factories/{id}', [ProductionLineController::class,'update']);
Route::delete('/factories/{id}', [ProductionLineController::class,'destroy']);


Route::get('/checklist',[ChecklistController::class,'index'])->name('checklist');

