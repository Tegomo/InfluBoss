<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Assignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class NoteController extends Controller
{
    use AuthorizesRequests;
    /**
     * Affiche les notes associées à une mission
     */
    public function index(Assignment $assignment)
    {
        Gate::authorize('view', $assignment);

        $notes = $assignment->notes()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('notes.index', [
            'assignment' => $assignment,
            'notes' => $notes
        ]);
    }

    /**
     * Affiche le formulaire de création d'une note
     */
    public function create(Assignment $assignment)
    {
        Gate::authorize('view', $assignment);

        return view('notes.create', [
            'assignment' => $assignment
        ]);
    }

    /**
     * Enregistre une nouvelle note
     */
    public function store(Request $request, Assignment $assignment)
    {
        Gate::authorize('view', $assignment);

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $note = new Note([
            'content' => $validated['content'],
            'user_id' => Auth::id()
        ]);
        
        $assignment->notes()->save($note);

        return redirect()->route('assignments.show', $assignment)
            ->with('success', 'Note ajoutée avec succès.');
    }

    /**
     * API endpoint pour ajouter une note via AJAX
     */
    public function apiStore(Request $request, Assignment $assignment)
    {
        Gate::authorize('view', $assignment);

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $note = new Note([
            'content' => $validated['content'],
            'user_id' => Auth::id()
        ]);
        
        $assignment->notes()->save($note);
        $note->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Note ajoutée avec succès.',
            'note' => [
                'id' => $note->id,
                'content' => $note->content,
                'created_at' => $note->created_at->format('d/m/Y H:i'),
                'user' => [
                    'name' => $note->user->name
                ]
            ]
        ]);
    }

    /**
     * Affiche le formulaire d'édition d'une note
     */
    public function edit(Note $note)
    {
        // Vérifier que l'utilisateur est autorisé à modifier cette note
        $this->authorize('update', $note);

        return view('notes.edit', [
            'note' => $note,
            'assignment' => $note->assignment
        ]);
    }

    /**
     * Met à jour une note
     */
    public function update(Request $request, Note $note)
    {
        // Vérifier que l'utilisateur est autorisé à modifier cette note
        $this->authorize('update', $note);

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $note->update([
            'content' => $validated['content']
        ]);

        return redirect()->route('assignments.show', $note->assignment)
            ->with('success', 'Note mise à jour avec succès.');
    }

    /**
     * Supprime une note
     */
    public function destroy(Note $note)
    {
        // Vérifier que l'utilisateur est autorisé à supprimer cette note
        $this->authorize('delete', $note);

        $assignment = $note->assignment;
        $note->delete();

        return redirect()->route('assignments.show', $assignment)
            ->with('success', 'Note supprimée avec succès.');
    }

    /**
     * API endpoint pour supprimer une note via AJAX
     */
    public function apiDestroy(Note $note)
    {
        // Vérifier que l'utilisateur est autorisé à supprimer cette note
        $this->authorize('delete', $note);

        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Note supprimée avec succès.'
        ]);
    }
}
