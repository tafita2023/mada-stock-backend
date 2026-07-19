<?php

namespace App\Http\Controllers\Diy;

use App\Http\Controllers\Controller;
use App\Models\Diy\Arome;
use App\Models\Diy\Aromes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AromesController extends Controller
{
    public function index()
    {
        return Aromes::latest()->get();
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
            'quantite'      => 'required|integer',
            'description'   => 'nullable|string',
            'categorie'     => 'required|numeric',
            'prix'          => 'required|numeric',
            'stock'         => 'required|numeric',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp'

        ]);

        $path = null;
        
        if ($request -> hasFile('image')){
            $file = $request->file('image');
            $filename = time().'_'.$file->getClientOriginalName();

            $file->move(public_path('uploads/arome'), $filename);
    
            $path = 'uploads/arome/'.$filename;
        }

        $arome = Aromes::create([
            'nom'           => $request->input('nom'),
            'marque'        => $request->input('marque'),
            'quantite'      => $request->input('quantite'),
            'prix'          => $request->input('prix'),
            'categorie'     => $request->input('categorie'),
            'stock'         => $request->input('stock'),
            'description'   => $request->input('description'),
            'image'         => $path

        ]);

        return response()->json($arome, 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(Aromes $arome)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Aromes $arome)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Aromes $arome)
    {
        $request->validate([
            'nom'           => 'required|string|max:255',
            'marque'        => 'required|string|max:255',
            'quantite'      => 'required|integer',
            'categorie'     => 'required|numeric',
            'prix'          => 'required|numeric',
            'stock'         => 'required|numeric',
            'description'   => 'nullable|string',
        ]);
    
        $arome->nom = $request->input('nom');
        $arome->marque = $request->input('marque');
        $arome->quantite = $request->input('quantite');
        $arome->categorie = $request->input('categorie');
        $arome->marque = $request->input('marque');
        $arome->prix = $request->input('prix');
        $arome->stock = $request->input('stock');
        $arome->description = $request->input('description');
    
        if ($request->hasFile('image')) {
    
            if ($arome->image && File::exists(public_path($arome->image))) {
                File::delete(public_path($arome->image));
            }
    
            $file = $request->file('image');
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
    
            $destination = public_path('uploads/arome');
    
            if (!File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }
    
            $file->move($destination, $filename);
    
            $arome->image = 'uploads/arome/'.$filename;
        }
    
        $arome->save();
    
        return response()->json([
            'message' => 'Arome mise à jour avec succès',
            'data' => $arome
        ]);
    }

    public function destroy(Aromes $arome)
    {
        if ($arome->image && File::exists(public_path($arome->image))) {
            File::delete(public_path($arome->image));
        }

        $arome->delete();

    return response()->json([
        'message' => 'Arome supprimé avec succès'
    ]);
    }

}
