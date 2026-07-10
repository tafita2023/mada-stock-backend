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
        return Produit::latest()->get();
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
            'marque'        => 'required|string|max:255',
            'nom'           => 'required|string|max:255',
            'description'   => 'nullable|string',
            'saveur'        => 'required|integer',
            'contenance'    => 'required|integer',
            'nicotine'      => 'nullable|integer',
            'prix'          => 'required|numeric',
            'stock'         => 'required|integer',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp'
        ]);

        $path = null;
        
        if ($request -> hasFile('image')){
            $file = $request->file('image');
            $filename = time().'_'.$file->getClientOriginalName();

            $file->move(public_path('uploads/produits'), $filename);
    
            $path = 'uploads/produits/'.$filename;
        }

        $produit = Produit::create([
            'marque'        => $request->input('marque'),
            'nom'           => $request->input('nom'),
            'description'   => $request->input('description'),
            'saveur'        => $request->input('saveur'),
            'contenance'    => $request->input('contenance'),
            'nicotine'      => $request->input('nicotine', 0),
            'prix'          => $request->input('prix'),
            'stock'         => $request->input('stock'),
            'image'         => $path
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
            'marque'        => 'required|string|max:255',
            'nom'           => 'required|string|max:255',
            'description'   => 'nullable|string',
            'saveur'        => 'required|integer',
            'contenance'    => 'required|integer',
            'nicotine'      => 'nullable|integer',
            'prix'          => 'required|numeric',
            'stock'         => 'required|integer',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp'
        ]);
    
        $produit->marque = $request->marque;
        $produit->nom = $request->nom;
        $produit->description = $request->description;
        $produit->saveur = $request->saveur;
        $produit->contenance = $request->contenance;
        $produit->nicotine = $request->nicotine;
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
