<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\MaterielController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);

Route::apiResource('produits', ProduitController::class);
Route::apiResource('materiels', MaterielController::class);


Route::middleware(['auth:sanctum'])->group(function () {
    
    // Utilisateur connecté
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::get('/produits', [ProduitController::class, 'index']);
    Route::get('/produits/{id}', [ProduitController::class, 'show']);
    Route::get('/materiels', [MaterielController::class, 'index']);
    Route::get('/materiels/{id}', [MaterielController::class, 'show']);

    // Gestion des produits (protégé)
    Route::middleware(['admin'])->group(function () {
            // Logout
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
    
        // Route produit
        Route::post('/produits', [ProduitController::class, 'store']);
        Route::put('/produits/{id}', [ProduitController::class, 'update']);
        Route::delete('/produits/{id}', [ProduitController::class, 'destroy']);

        // Route materiel
        Route::post('/materiels', [MaterielController::class, 'store']);
        Route::put('/materiels/{id}', [MaterielController::class, 'update']);
        Route::delete('/materiels/{id}', [MaterielController::class, 'destroy']);

    });    
});
