<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    /**
     * Authentifier un utilisateur et générer un token
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        // Utiliser le nom de l'appareil fourni ou 'api' par défaut
        $deviceName = $request->device_name ?? 'api';
        
        // Supprimer les anciens tokens pour ce device (optionnel)
        $user->tokens()->where('name', $deviceName)->delete();

        // Définir les capacités selon le rôle de l'utilisateur
        $abilities = [];
        switch ($user->role) {
            case 'super_admin':
                $abilities = ['*']; // Tous les droits
                break;
            case 'admin':
                $abilities = ['campaigns:*', 'influencers:*', 'supervisors:*', 'publications:*', 'stats:read'];
                break;
            case 'supervisor':
                $abilities = ['campaigns:read', 'influencers:read', 'publications:*', 'stats:read'];
                break;
            case 'influencer':
                $abilities = ['campaigns:read', 'publications:create', 'publications:read', 'stats:read'];
                break;
            default:
                $abilities = ['none'];
        }

        $token = $user->createToken($deviceName, $abilities);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token->plainTextToken,
        ]);
    }

    /**
     * Déconnecter l'utilisateur (révoquer le token)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        // Supprimer le token actuel
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté avec succès']);
    }
}
