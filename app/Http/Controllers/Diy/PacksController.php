<?php

namespace App\Http\Controllers\Diy;

use App\Http\Controllers\Controller;
use App\Models\Diy\Packs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PacksController extends Controller
{
    public function index()
    {
        return Packs::latest()->get();
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
            'marque'        => 'required|string|max:255',
            'nicotine'      => 'required|numeric',
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

            $file->move(public_path('uploads/packs'), $filename);
    
            $path = 'uploads/packs/'.$filename;
        }


        $pack = Packs::create([
            'nom'           => $request->input('nom'),
            'marque'        => $request->input('marque'),
            'quantite'      => $request->input('quantite'),
            'nicotine'      => $request->input('nicotine'),
            'prix'          => $request->input('prix'),
            'stock'         => $request->input('stock'),
            'description'   => $request->input('description'),
            'image'         => $path

        ]);

        return response()->json($pack, 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(Packs $pack)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Packs $pack)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Packs $pack)
    {
        $request->validate([
            'nom'           => 'required|string|max:255',
            'marque'        => 'required|string|max:255',
            'nicotine'      => 'required|numeric',
            'quantite'      => 'required|integer',
            'description'   => 'nullable|string',
            'prix'          => 'required|numeric',
            'stock'         => 'required|numeric',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp'
        ]);
    
        $pack->nom = $request->input('nom');
        $pack->marque = $request->input('marque');
        $pack->quantite = $request->input('quantite');
        $pack->nicotine = $request->input('nicotine');
        $pack->prix = $request->input('prix');
        $pack->stock = $request->input('stock');
        $pack->description = $request->input('description');
    
        if ($request->hasFile('image')) {
    
            if ($pack->image && File::exists(public_path($pack->image))) {
                File::delete(public_path($pack->image));
            }
    
            $file = $request->file('image');
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
    
            $destination = public_path('uploads/packs');
    
            if (!File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }
    
            $file->move($destination, $filename);
    
            $pack->image = 'uploads/packs/'.$filename;
        }
    
        $pack->save();
    
        return response()->json([
            'message' => 'Pack mise à jour avec succès',
            'data' => $pack
        ]);
    }


    public function destroy(Packs $pack)
    {
        if ($pack->image && File::exists(public_path($pack->image))) {
            File::delete(public_path($pack->image));
        }

        $pack->delete();

    return response()->json([
        'message' => 'Pack supprimé avec succès'
    ]);
    }

}
