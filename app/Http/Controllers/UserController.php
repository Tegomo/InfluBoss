<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Méthode index standard pour la route resource.
     * Affiche tous les utilisateurs ou filtre par rôle.
     */
    public function index(Request $request)
    {
        // Filtrer par rôle si spécifié
        $query = User::query();
        
        // Exclure les super_admin de la liste
        $query->where('role', '!=', 'super_admin');
        
        if ($request->has('role')) {
            $selectedRole = $request->query('role');
            $query->where('role', $selectedRole);
        } else {
            $selectedRole = null;
        }
        
        $users = $query->latest()->paginate(20);
        
        return view('users.index', compact('users', 'selectedRole'));
    }
    
    /**
     * Affiche la liste de tous les utilisateurs.
     * Accessible uniquement aux administrateurs.
     */
    public function indexAll()
    {
        // TODO: Ajouter une policy/gate pour restreindre l'accès
        $users = User::where('role', '!=', 'super_admin')->latest()->paginate(20);
        return view('users.index', [
            'users' => $users,
            'selectedRole' => null
        ]);
    }

    /**
     * Affiche la liste des influenceurs.
     * Accessible aux administrateurs et superviseurs.
     */
    public function indexInfluencers()
    {
        // TODO: Ajouter une policy/gate pour restreindre l'accès
        $influencers = User::where('role', 'influencer')->latest()->paginate(20);
        return view('users.index', [
            'users' => $influencers,
            'selectedRole' => 'influencer'
        ]);
    }

    /**
     * Affiche la liste des superviseurs.
     * Accessible uniquement aux administrateurs.
     */
    public function indexSupervisors()
    {
        // TODO: Ajouter une policy/gate pour restreindre l'accès
        $supervisors = User::where('role', 'supervisor')->latest()->paginate(20);
        return view('users.index', [
            'users' => $supervisors,
            'selectedRole' => 'supervisor'
        ]);
    }
    
    /**
     * Affiche le formulaire de création d'un nouvel utilisateur.
     * 
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('users.create');
    }
    
    /**
     * Enregistre un nouvel utilisateur dans la base de données.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,supervisor,influencer',
            'pseudo' => 'nullable|string|max:255',
            'birthdate' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
        ]);
        
        // Hash du mot de passe avant création
        $validated['password'] = bcrypt($validated['password']);
        
        User::create($validated);
        
        return redirect()->route('users.index')
                        ->with('success', 'Utilisateur créé avec succès.');
    }
    
    /**
     * Affiche les détails d'un utilisateur spécifique.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /**
     * Affiche le formulaire d'édition pour un utilisateur spécifique.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Met à jour les données d'un utilisateur spécifique dans la base de données.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string|in:admin,supervisor,influencer',
            'pseudo' => 'nullable|string|max:255',
            'birthdate' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
        ];
        
        // Valider le mot de passe seulement s'il est fourni
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }
        
        $validated = $request->validate($rules);
        
        // Hash du mot de passe s'il est fourni
        if ($request->filled('password')) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']); // Ne pas mettre à jour le mot de passe s'il n'est pas fourni
        }
        
        $user->update($validated);
        
        return redirect()->route('users.index')
                        ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Supprime un utilisateur spécifique de la base de données.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        // Vérifier que l'utilisateur ne se supprime pas lui-même
        if (Auth::id() === $user->id) {
            return redirect()->route('users.index')
                             ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        
        // Vérifier que l'utilisateur n'est pas le dernier admin/super_admin
        if (($user->role === 'admin' || $user->role === 'super_admin') && 
            User::whereIn('role', ['admin', 'super_admin'])->count() <= 1) {
            return redirect()->route('users.index')
                             ->with('error', 'Impossible de supprimer le dernier administrateur.');
        }
        
        $user->delete();
        
        return redirect()->route('users.index')
                        ->with('success', 'Utilisateur supprimé avec succès.');
    }
}
