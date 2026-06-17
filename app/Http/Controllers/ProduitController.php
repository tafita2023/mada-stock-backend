<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Produit::latest()->paginate(10);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'saveur' => 'required|string|max:255',
            'prix' => 'required|numeric',
            'stock' => 'required|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp'
        ]);

        $path = null;
        
        if ($request -> hasFile('image')){
            $file = $request->file('image');
            $path = $file->store('produits', 'public');
        }

        $produit = Produit::create([
            'nom' => $request->nom,
            'description' => $request->description,
            'saveur' => $request->saveur,
            'prix' => $request->prix,
            'stock' => $request->stock,
            'image' => $path
        ]);
    
        return response()->json($produit, 201);
    
    }

    /**
     * Display the specified resource.
     */
    public function show(Produit $produit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Produit $produit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produit $produit)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'saveur' => 'required|string|max:255',
            'prix' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
    
        $produit->nom = $request->nom;
        $produit->description = $request->description;
        $produit->saveur = $request->saveur;
        $produit->prix = $request->prix;
        $produit->stock = $request->stock;
    
        if ($request->hasFile('image')) {
    
            // supprimer ancienne image
            if ($produit->image && Storage::disk('public')->exists($produit->image)) {
                Storage::disk('public')->delete($produit->image);
            }
    
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
    
            $path = $file->storeAs('produits', $filename, 'public');
    
            $produit->image = $path;
        }
    
        $produit->save();
    
        return response()->json([
            'message' => 'Produit mis à jour avec succès',
            'data' => $produit
        ]);
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produit $produit)
    {
        $produit->delete();

    return response()->json([
        'message' => 'Produit supprimé avec succès'
    ]);
    }
}
