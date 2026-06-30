<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class ProfileController extends Controller
{
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|min:8|confirmed',
        ]);

        // Nom
        if ($request->filled('name')) {
            $user->name = $request->name;
        }

        // Email
        if ($request->filled('email')) {
            $user->email = $request->email;
        }

        // Avatar
        if ($request->hasFile('avatar')) {

        // supprimer ancien avatar
        if ($user->avatar && File::exists(public_path($user->avatar))) {
            File::delete(public_path($user->avatar));
        }

        $file = $request->file('avatar');

        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        $destination = public_path('uploads/avatars');

        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        $file->move($destination, $filename);

        $user->avatar = 'uploads/avatars/' . $filename;        
    }

        // Mot de passe
        if ($request->filled('password')) {

            if (!Hash::check(
                $request->current_password,
                $user->password
            )) {
                return response()->json([
                    'message' => 'Mot de passe actuel incorrect'
                ], 422);
            }

            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'user' => $user
        ]);
    }
}
