<?php

use App\Helpers\UpdateOneZero2;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\MultiProductionLineController;
use App\Http\Controllers\PowerPlanController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProductionLineController;
use App\Http\Controllers\SharedFactoryController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/apply-fixes', function() {
    UpdateOneZero2::update();

    \Illuminate\Support\Facades\Artisan::call('rebuild-cache');
    \Illuminate\Support\Facades\Artisan::call('flush-production-cache');
});


Route::get('/favorites', [FavoritesController::class,'index'])->name('favorites');
Route::post('/favorites/preset', [FavoritesController::class,'storePreset'])->name('favorites.storePreset');
Route::post('/favorites', [FavoritesController::class,'store'])->name('favorites.store');
Route::delete('/favorites-all', [FavoritesController::class,'destroyAll'])->name('favorites.destroyAll');
Route::delete('/favorites/{id}', [FavoritesController::class,'destroy'])->name('favorites.destroy');

Route::post('favorites/sub/{recipe}', [ProductionController::class, 'addSubFavorite']);
Route::post('favorites/{recipe}', [ProductionController::class, 'addFavorite']);

Route::get('/power',[PowerPlanController::class,'index'])->name('power.index');
Route::get('/power/{output}',[PowerPlanController::class,'show'])->name('power.show');

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

Route::get('/shared/{hashId}', [SharedFactoryController::class, 'show'])->name('shared_factory');


Route::get('/checklist',[ChecklistController::class,'index'])->name('checklist');

