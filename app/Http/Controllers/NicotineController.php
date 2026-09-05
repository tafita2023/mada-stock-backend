<?php

namespace App\Http\Controllers;

use App\Models\Nicotine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class NicotineController extends Controller
{
        /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Nicotine::latest()->get();
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
            'titre'         => 'required|string|max:255',
            'description'   => 'required|string',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp'
        ]);

        $path = null;
        
        if ($request -> hasFile('image')){
            $file = $request->file('image');
            $filename = time().'_'.$file->getClientOriginalName();

            $file->move(public_path('uploads/nicotines'), $filename);
    
            $path = 'uploads/nicotines/'.$filename;
        }

        $nicotine = Nicotine::create([
            'titre'         => $request->input('titre'),
            'description'   => $request->input('description'),
            'image'         => $path
        ]);
    
        return response()->json($nicotine, 201);
    
    }

    /**
     * Display the specified resource.
     */
    public function show(Nicotine $nicotine)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Nicotine $nicotine)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Nicotine $nicotine)
    {
        $request->validate([
            'titre'         => 'required|string|max:255',
            'description'   => 'required|string',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp'
        ]);
    
        $nicotine->titre = $request->titre;
        $nicotine->description = $request->description;
    
        if ($request->hasFile('image')) {
    
        // Supprimer l'ancienne image
        if ($nicotine->image && File::exists(public_path($nicotine->image))) {
            File::delete(public_path($nicotine->image));
        }

        // Enregistrer la nouvelle image
        $file = $request->file('image');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Crée automatiquement le dossier s'il n'existe pas
        $destination = public_path('uploads/nicotines');

        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        $file->move($destination, $filename);

        $nicotine->image = 'uploads/nicotines/' . $filename;        
    }
    
        $nicotine->save();
    
        return response()->json([
            'message' => 'Mis à jour avec succès',
            'data' => $nicotine
        ]);
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Nicotine $nicotine)
    {
        if ($nicotine->image && File::exists(public_path($nicotine->image))) {
            File::delete(public_path($nicotine->image));
        }

        $nicotine->delete();

    return response()->json([
        'message' => 'Supprimé avec succès'
    ]);
    }

}
