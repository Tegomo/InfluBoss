<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Affiche le formulaire d'édition du profil
     */
    public function edit()
    {
        return view('profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Met à jour le profil de l'utilisateur
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'profile_photo' => 'nullable|image|max:2048',
        ]);
        
        // Gérer les liens sociaux pour les influenceurs
        if ($user->role === 'influencer') {
            $socialLinks = [];
            if ($request->filled('social_facebook')) {
                $socialLinks['facebook'] = $request->social_facebook;
            }
            if ($request->filled('social_instagram')) {
                $socialLinks['instagram'] = $request->social_instagram;
            }
            if ($request->filled('social_twitter')) {
                $socialLinks['twitter'] = $request->social_twitter;
            }
            if ($request->filled('social_tiktok')) {
                $socialLinks['tiktok'] = $request->social_tiktok;
            }
            if ($request->filled('social_youtube')) {
                $socialLinks['youtube'] = $request->social_youtube;
            }
            if ($request->filled('social_linkedin')) {
                $socialLinks['linkedin'] = $request->social_linkedin;
            }
            
            $user->social_links = !empty($socialLinks) ? json_encode($socialLinks) : null;
        }
        
        // Traiter la photo de profil si fournie
        if ($request->hasFile('profile_photo')) {
            // Supprimer l'ancienne photo s'il y en a une
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $user->profile_photo = $path;
        }
        
        // Mettre à jour les infos de l'utilisateur
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->bio = $validated['bio'] ?? null;
        
        // Mettre à jour le mot de passe si fourni
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            
            $user->password = Hash::make($request->password);
        }
        
        $user->save();
        
        return redirect()->route('profile.edit')->with('success', 'Profil mis à jour avec succès.');
    }

    /**
     * Supprime le compte de l'utilisateur
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);
        
        $user = Auth::user();
        
        Auth::logout();
        
        $user->delete();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}
