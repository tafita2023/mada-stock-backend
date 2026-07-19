<?php

namespace App\Http\Controllers\Diy;

use App\Http\Controllers\Controller;
use App\Models\Diy\Divers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class DiversController extends Controller
{
    public function index()
    {
        return Divers::latest()->get();
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
            'description'   => 'nullable|string',
            'prix'          => 'required|numeric',
            'stock'         => 'required|numeric',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp'

        ]);

        $path = null;
        
        if ($request -> hasFile('image')){
            $file = $request->file('image');
            $filename = time().'_'.$file->getClientOriginalName();

            $file->move(public_path('uploads/divers'), $filename);
    
            $path = 'uploads/divers/'.$filename;
        }


        $diver = Divers::create([
            'nom'           => $request->input('nom'),
            'marque'        => $request->input('marque'),
            'prix'          => $request->input('prix'),
            'stock'         => $request->input('stock'),
            'description'   => $request->input('description'),
            'image'         => $path

        ]);

        return response()->json($diver, 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(Divers $diver)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Divers $diver)
    {
        //
    }

        /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Divers $diver)
    {
        $request->validate([
            'nom'           => 'required|string|max:255',
            'marque'        => 'required|string|max:255',
            'description'   => 'nullable|string',
            'prix'          => 'required|numeric',
            'stock'         => 'required|numeric',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp'
        ]);
    
        $diver->nom = $request->input('nom');
        $diver->marque = $request->input('marque');
        $diver->prix = $request->input('prix');
        $diver->stock = $request->input('stock');
        $diver->description = $request->input('description');
    
        if ($request->hasFile('image')) {
    
            if ($diver->image && File::exists(public_path($diver->image))) {
                File::delete(public_path($diver->image));
            }
    
            $file = $request->file('image');
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
    
            $destination = public_path('uploads/divers');
    
            if (!File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }
    
            $file->move($destination, $filename);
    
            $diver->image = 'uploads/divers/'.$filename;
        }
    
        $diver->save();
    
        return response()->json([
            'message' => 'diver mise à jour avec succès',
            'data' => $diver
        ]);
    }


    public function destroy(Divers $diver)
    {
        if ($diver->image && File::exists(public_path($diver->image))) {
            File::delete(public_path($diver->image));
        }

        $diver->delete();

    return response()->json([
        'message' => 'diver supprimé avec succès'
    ]);
    }

}
