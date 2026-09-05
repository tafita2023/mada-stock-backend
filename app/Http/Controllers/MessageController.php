<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use Illuminate\Support\Facades\Http;

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
            'telephone'     => 'required|string|max:10',
            'message'       => 'required|string|max:255',
            'captcha'       => 'required|string',
        ]);

        // Vérification captcha Google
        $captchaResponse = Http::asForm()->post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'secret'   => env('RECAPTCHA_SECRET_KEY'),
                'response' => $request->captcha,
            ]
        );


        $captchaResult = $captchaResponse->json();


        if (
            !isset($captchaResult['success']) ||
            $captchaResult['success'] !== true
        ) {

            return response()->json([
                'message' => 'Captcha invalide'
            ], 422);

        }

        $message = Message::create([
            'nom'           => $request->input('nom'),
            'email'         => $request->input('email'),
            'telephone'     => $request->input('telephone'),
            'message'       => $request->input('message'),
            'status'        => 1,
        ]);
    
        return response()->json($message, 201);
    
    }

    public function markAsRead(int $id)
    {
        $message = Message::findOrFail($id);

        $message->update([
            'status' => 0
        ]);

        return response()->json([
            'message' => 'Message marqué comme lu'
        ]);
    }

    public function destroy(Message $message)
    {

    $message->delete();

    return response()->json([
        'message' => 'Message supprimé avec succès'
    ]);
    }
 
}
