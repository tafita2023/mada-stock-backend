<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Materiel;
use Illuminate\Support\Facades\Storage;
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
            $path = $file->store('Materiels', 'public');
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
    
            // supprimer ancienne image
            if ($materiel->image && Storage::disk('public')->exists($materiel->image)) {
                Storage::disk('public')->delete($materiel->image);
            }
    
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
    
            $path = $file->storeAs('Materiels', $filename, 'public');
    
            $materiel->image = $path;
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
