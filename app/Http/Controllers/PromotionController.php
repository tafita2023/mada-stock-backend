<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Materiel;
use App\Models\Produit;

class PromotionController extends Controller
{

    public function index()
    {
        $produits = DB::table('prom_produit')
            ->join('produits', 'produits.id', '=', 'prom_produit.id_produit')
            ->select(
                'prom_produit.id',
                'prom_produit.id_produit',
                'produits.nom',
                'produits.image',
                'prom_produit.description',
                'produits.prix as ancien_prix',
                'prom_produit.prix'
            )
            ->get()
            ->map(function ($promotion) {
                $promotion->type = 'produit';
                return $promotion;
            });
    
        $materiels = DB::table('prom_materiel')
            ->join('materiels', 'materiels.id', '=', 'prom_materiel.id_materiel')
            ->select(
                'prom_materiel.id',
                'prom_materiel.id_materiel',
                'materiels.nom',
                'materiels.image',
                'prom_materiel.description',
                'materiels.prix as ancien_prix',
                'prom_materiel.prix'
            )
            ->get()
            ->map(function ($promotion) {
                $promotion->type = 'materiel';
                return $promotion;
            });
    
        $promotions = $produits
            ->concat($materiels)
            ->sortBy('id')
            ->values();
    
        return response()->json([
            'data' => $promotions
        ]);
    }
    
 /**
     * Récupérer les produits disponibles pour les promotions
     */
    public function produits(): JsonResponse
    {
        $produits = DB::table('prom_produit')
            ->join('produits', 'produits.id', '=', 'prom_produit.id_produit')
            ->select(
                'produits.id',
                'produits.nom',
                'produits.description',
                'produits.prix as ancien_prix',
                'prom_produit.prix'
            )
            ->orderBy('produits.nom', 'asc')
            ->get();

        return response()->json($produits);
    }

    /**
     * Récupérer les matériels disponibles pour les promotions
     */
    public function materiels(): JsonResponse
    {
        $materiels = DB::table('prom_materiel')
            ->join('materiels', 'materiels.id', '=', 'prom_materiel.id_materiel')
            ->select(
                'materiels.id',
                'materiels.nom',
                'materiels.description',
                'materiels.prix as ancien_prix',
                'prom_materiel.prix'
            )
            ->orderBy('materiels.nom', 'asc')
            ->get();

        return response()->json($materiels);
    }

    /**
    * Ajouter
     */ 
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:produit,materiel',
            'nouveau_prix' => 'required|numeric|min:1',

            'id_produit' => 'nullable|exists:produits,id',
            'id_materiel' => 'nullable|exists:materiels,id',
        ]);

        try {

            if ($request->type === 'produit') {

                if (!$request->id_produit) {
                    return response()->json([
                        'message' => 'Veuillez choisir un produit'
                    ], 422);
                }

                $produit = Produit::findOrFail($request->id_produit);

                DB::table('prom_produit')->insert([
                    'id_produit' => $produit->id,
                    'description' => $request->description ?? $produit->description,
                    'prix' => $request->nouveau_prix,
                ]);

            } else {

                if (!$request->id_materiel) {
                    return response()->json([
                        'message' => 'Veuillez choisir un matériel'
                    ], 422);
                }

                $materiel = Materiel::findOrFail($request->id_materiel);

                DB::table('prom_materiel')->insert([
                    'id_materiel' => $materiel->id,
                    'description' => $request->description ?? $materiel->description,
                    'prix' => $request->nouveau_prix,
                ]);
            }

            return response()->json([
                'message' => 'Promotion ajoutée avec succès'
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Erreur lors de l’ajout de la promotion',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:produit,materiel',
            'nouveau_prix' => 'required|numeric|min:1',

            'id_produit' => 'nullable|exists:produits,id',
            'id_materiel' => 'nullable|exists:materiels,id',
        ]);

        try {

            if ($request->type === 'produit') {

                $promotion = DB::table('prom_produit')
                    ->where('id_produit', $request->id_produit)
                    ->first();

                if (!$promotion) {
                    return response()->json([
                        'message' => 'Promotion produit introuvable'
                    ], 404);
                }

                DB::table('prom_produit')
                    ->where('id_produit', $request->id_produit)
                    ->update([
                        'description' => $request->description ?? $promotion->description,
                        'prix' => $request->nouveau_prix,
                    ]);

            } else {

                $promotion = DB::table('prom_materiel')
                    ->where('id_materiel', $request->id_materiel)
                    ->first();

                if (!$promotion) {
                    return response()->json([
                        'message' => 'Promotion matériel introuvable'
                    ], 404);
                }

                DB::table('prom_materiel')
                    ->where('id_materiel', $request->id_materiel)
                    ->update([
                        'description' => $request->description ?? $promotion->description,
                        'prix' => $request->nouveau_prix,
                    ]);
            }

            return response()->json([
                'message' => 'Promotion modifiée avec succès'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Erreur lors de la modification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $type, int $id)
    {
        try {
    
            if ($type === 'produit') {
    
                $deleted = DB::table('prom_produit')
                    ->where('id', $id)
                    ->delete();
    
                if ($deleted === 0) {
                    return response()->json([
                        'message' => 'Promotion produit introuvable'
                    ], 404);
                }
    
                return response()->json([
                    'message' => 'Promotion produit supprimée avec succès'
                ], 200);
            }
    
            if ($type === 'materiel') {
    
                $deleted = DB::table('prom_materiel')
                    ->where('id', $id)
                    ->delete();
    
                if ($deleted === 0) {
                    return response()->json([
                        'message' => 'Promotion matériel introuvable'
                    ], 404);
                }
    
                return response()->json([
                    'message' => 'Promotion matériel supprimée avec succès'
                ], 200);
            }
    
            return response()->json([
                'message' => 'Type de promotion invalide'
            ], 422);
    
        } catch (\Exception $e) {
    
            return response()->json([
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
