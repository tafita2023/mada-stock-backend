<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Diy\BasesController;
use App\Http\Controllers\Diy\AromesController;
use App\Http\Controllers\Diy\PacksController;
use App\Http\Controllers\Diy\DiversController;
use App\Http\Controllers\MaterielController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;

Route::post('/message', [MessageController::class, 'store']);

Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

Route::get('/produits', [ProduitController::class, 'index']);
Route::get('/produits/{produit}', [ProduitController::class, 'show']);

Route::get('/materiels', [MaterielController::class, 'index']);
Route::get('/materiels/{materiel}', [MaterielController::class, 'show']);

Route::get('/diy/bases', [BasesController::class, 'index']);
Route::get('/diy/bases/{base}', [BasesController::class, 'show']);

Route::get('/diy/aromes', [AromesController::class, 'index']);
Route::get('/diy/aromes/{arome}', [AromesController::class, 'show']);

Route::get('/diy/packs', [PacksController::class, 'index']);
Route::get('/diy/packs/{pack}', [PacksController::class, 'show']);

Route::get('/diy/divers', [DiversController::class, 'index']);
Route::get('/diy/divers/{diver}', [DiversController::class, 'show']);

Route::middleware(['auth:sanctum'])->group(function () {
    
    // Utilisateur connecté
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Gestion des produits (protégé)
    Route::middleware(['admin'])->group(function () {
            // Logout
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
    
        // ROUTE MESSAGE
        Route::put('/messages/{id}/read', [MessageController::class, 'markAsRead']);
        Route::get('/messages', [MessageController::class, 'index']);
        Route::delete('/messages/{message}', [MessageController::class, 'destroy']);

        // Route produit
        Route::post('/produits', [ProduitController::class, 'store']);
        Route::put('/produits/{produit}', [ProduitController::class, 'update']);
        Route::delete('/produits/{produit}', [ProduitController::class, 'destroy']);

        // Route materiel
        Route::post('/materiels', [MaterielController::class, 'store']);
        Route::put('/materiels/{materiel}', [MaterielController::class, 'update']);
        Route::delete('/materiels/{materiel}', [MaterielController::class, 'destroy']);

        // Route DIY: BASE
        Route::post('/diy/bases', [BasesController::class, 'store']);
        Route::put('/diy/bases/{base}', [BasesController::class, 'update']);
        Route::delete('/diy/bases/{base}', [BasesController::class, 'destroy']);

        // Route DIY: AROME
        Route::post('/diy/aromes', [AromesController::class, 'store']);
        Route::put('/diy/aromes/{arome}', [AromesController::class, 'update']);
        Route::delete('/diy/aromes/{arome}', [AromesController::class, 'destroy']);

        // Route DIY: PACK
        Route::post('/diy/packs', [PacksController::class, 'store']);
        Route::put('/diy/packs/{pack}', [PacksController::class, 'update']);
        Route::delete('/diy/packs/{pack}', [PacksController::class, 'destroy']);

        // Route DIY: DIVER
        Route::post('/diy/divers', [DiversController::class, 'store']);
        Route::put('/diy/divers/{diver}', [DiversController::class, 'update']);
        Route::delete('/diy/divers/{diver}', [DiversController::class, 'destroy']);

        // Route profil
        Route::get('/profile', [ProfileController::class, 'me']);
        Route::post('/profile', [ProfileController::class, 'update']);
    });    
});
