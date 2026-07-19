<?php

namespace App\Http\Controllers\Diy;

use App\Http\Controllers\Controller;
use App\Models\Diy\Bases;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BasesController extends Controller
{
    public function index()
    {
        return Bases::latest()->get();
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
            'nom'           => 'required|string|max:255',
            'ratio'         => 'required|string|max:255',
            'quantite'      => 'required|integer',
            'description'   => 'nullable|string',
            'prix'          => 'required|numeric',
            'stock'         => 'required|numeric',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp'

        ]);

        $path = null;
        
        if ($request -> hasFile('image')){
            $file = $request->file('image');
            $filename = time().'_'.$file->getClientOriginalName();

            $file->move(public_path('uploads/bases'), $filename);
    
            $path = 'uploads/bases/'.$filename;
        }


        $base = Bases::create([
            'nom'           => $request->input('nom'),
            'ratio'         => $request->input('ratio'),
            'quantite'      => $request->input('quantite'),
            'prix'          => $request->input('prix'),
            'stock'         => $request->input('stock'),
            'description'   => $request->input('description'),
            'image'         => $path

        ]);

        return response()->json($base, 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(Bases $base)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bases $base)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bases $base)
    {
        $request->validate([
            'nom'           => 'required|string|max:255',
            'ratio'         => 'required|string|max:255',
            'quantite'      => 'required|integer',
            'prix'          => 'required|numeric',
            'stock'         => 'required|numeric',
            'description'   => 'nullable|string',
        ]);
    
        $base->nom = $request->input('nom');
        $base->ratio = $request->input('ratio');
        $base->quantite = $request->input('quantite');
        $base->prix = $request->input('prix');
        $base->stock = $request->input('stock');
        $base->description = $request->input('description');
    
        if ($request->hasFile('image')) {
    
            if ($base->image && File::exists(public_path($base->image))) {
                File::delete(public_path($base->image));
            }
    
            $file = $request->file('image');
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
    
            $destination = public_path('uploads/bases');
    
            if (!File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }
    
            $file->move($destination, $filename);
    
            $base->image = 'uploads/bases/'.$filename;
        }
    
        $base->save();
    
        return response()->json([
            'message' => 'Base mise à jour avec succès',
            'data' => $base
        ]);
    }

    public function destroy(Bases $base)
    {
        if ($base->image && File::exists(public_path($base->image))) {
            File::delete(public_path($base->image));
        }

        $base->delete();

    return response()->json([
        'message' => 'Base supprimé avec succès'
    ]);
    }

}
