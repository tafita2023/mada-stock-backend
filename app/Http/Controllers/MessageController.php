<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;

class MessageController extends Controller
{
        /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Message::latest()->get();
    }

        /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom'           => 'required|string|max:255',
            'email'         => 'required|string|max:255',
            'message'       => 'required|string|max:255',
        ]);

        $message = Message::create([
            'nom'           => $request->input('nom'),
            'email'         => $request->input('email'),
            'message'       => $request->input('message'),
        ]);
    
        return response()->json($message, 201);
    
    }

}
