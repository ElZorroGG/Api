<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PopulationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('population')->group(function () {
    // Población por municipio
    Route::get('/municipalities', [PopulationController::class, 'getMunicipalityPopulation']);
    
    // Población por isla
    Route::get('/islands', [PopulationController::class, 'getIslandPopulation']);
    
    // Evolución de población por municipio o isla
    Route::get('/evolution', [PopulationController::class, 'getPopulationEvolution']);
    
    // Búsqueda de municipios e islas
    Route::get('/search', [PopulationController::class, 'search']);
    
    // Filtros disponibles
    Route::get('/filters', [PopulationController::class, 'getFilters']);
});

