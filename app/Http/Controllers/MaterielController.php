<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Materiel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class MaterielController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Materiel::latest()->paginate(10);
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
            'type' => 'required|string|max:255',
            'marque' => 'required|string|max:255',
            'prix' => 'required|numeric',
            'stock' => 'required|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp'
        ]);

        $path = null;
        
        if ($request -> hasFile('image')){
            $file = $request->file('image');
            $filename = time().'_'.$file->getClientOriginalName();

            $file->move(public_path('uploads/materiels'), $filename);
    
            $path = 'uploads/materiels/'.$filename;
        }

        $materiel = Materiel::create([
            'nom' => $request->nom,
            'type' => $request->type,
            'marque' => $request->marque,
            'prix' => $request->prix,
            'stock' => $request->stock,
            'image' => $path
        ]);
    
        return response()->json($materiel, 201);
    
    }

    /**
     * Display the specified resource.
     */
    public function show(Materiel $materiel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Materiel $materiel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Materiel $materiel)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'marque' => 'required|string|max:255',
            'prix' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
    
        $materiel->nom = $request->nom;
        $materiel->type = $request->type;
        $materiel->marque = $request->marque;
        $materiel->prix = $request->prix;
        $materiel->stock = $request->stock;
    
        if ($request->hasFile('image')) {
    
        // Supprimer l'ancienne image
        if ($materiel->image && File::exists(public_path($materiel->image))) {
            File::delete(public_path($materiel->image));
        }

        // Enregistrer la nouvelle image
        $file = $request->file('image');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Crée automatiquement le dossier s'il n'existe pas
        $destination = public_path('uploads/materiels');

        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        $file->move($destination, $filename);

        $materiel->image = 'uploads/materiels/' . $filename;        
    }
    
        $materiel->save();
    
        return response()->json([
            'message' => 'Materiel mis à jour avec succès',
            'data' => $materiel
        ]);
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Materiel $materiel)
    {
        $materiel->delete();

    return response()->json([
        'message' => 'Materiel supprimé avec succès'
    ]);
    }
}
