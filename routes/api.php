<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\MaterielController;
use App\Http\Controllers\ProfileController;

Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

Route::get('/produits', [ProduitController::class, 'index']);
Route::get('/produits/{produit}', [ProduitController::class, 'show']);

Route::get('/materiels', [MaterielController::class, 'index']);
Route::get('/materiels/{materiel}', [MaterielController::class, 'show']);

Route::middleware(['auth:sanctum'])->group(function () {
    
    // Utilisateur connecté
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Gestion des produits (protégé)
    Route::middleware(['admin'])->group(function () {
            // Logout
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
    
        // Route produit
        Route::post('/produits', [ProduitController::class, 'store']);
        Route::put('/produits/{produit}', [ProduitController::class, 'update']);
        Route::delete('/produits/{produit}', [ProduitController::class, 'destroy']);

        // Route materiel
        Route::post('/materiels', [MaterielController::class, 'store']);
        Route::put('/materiels/{materiel}', [MaterielController::class, 'update']);
        Route::delete('/materiels/{materiel}', [MaterielController::class, 'destroy']);

        // Route profil
        Route::get('/profile', [ProfileController::class, 'me']);
        Route::post('/profile', [ProfileController::class, 'update']);
    });    
});
