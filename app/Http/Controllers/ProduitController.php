<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

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
            $filename = time().'_'.$file->getClientOriginalName();

            $file->move(public_path('uploads/produits'), $filename);
    
            $path = 'uploads/produits/'.$filename;
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
    
// Supprimer l'ancienne image
if ($produit->image && File::exists(public_path($produit->image))) {
    File::delete(public_path($produit->image));
}

// Enregistrer la nouvelle image
$file = $request->file('image');
$filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

// Crée automatiquement le dossier s'il n'existe pas
$destination = public_path('uploads/produits');

        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        $file->move($destination, $filename);

        $produit->image = 'uploads/produits/' . $filename;        
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
        if ($produit->image && File::exists(public_path($produit->image))) {
            File::delete(public_path($produit->image));
        }

        $produit->delete();

    return response()->json([
        'message' => 'Produit supprimé avec succès'
    ]);
    }
}
