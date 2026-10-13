<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Deliverable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * Livrables d'une mission, gérés depuis la page de la mission.
 */
class DeliverableController extends Controller
{
    public function store(Request $request, Assignment $assignment)
    {
        Gate::authorize('create', [Deliverable::class, $assignment]);

        $assignment->deliverables()->create($request->validate($this->rules(), $this->messages()));

        return $this->backToMission($assignment, 'Livrable ajouté.');
    }

    public function edit(Deliverable $deliverable)
    {
        Gate::authorize('update', $deliverable);

        return view('deliverables.edit', [
            'deliverable' => $deliverable->load('assignment.campaign', 'assignment.influencer'),
            'types' => config('influboss.deliverable_types'),
            'platforms' => config('influboss.platforms'),
            'statuses' => config('influboss.deliverable_statuses'),
        ]);
    }

    public function update(Request $request, Deliverable $deliverable)
    {
        Gate::authorize('update', $deliverable);

        $validated = $request->validate($this->rules(withStatus: true), $this->messages());

        // Un statut changé à la main est conservé jusqu'au rattachement d'une publication.
        if ($validated['status'] !== $deliverable->status) {
            $validated['status_set_manually'] = true;
        }

        $deliverable->update($validated);

        return $this->backToMission($deliverable->assignment, 'Livrable mis à jour.');
    }

    public function destroy(Deliverable $deliverable)
    {
        Gate::authorize('delete', $deliverable);

        $assignment = $deliverable->assignment;
        $deliverable->delete();

        return $this->backToMission($assignment, 'Livrable supprimé.');
    }

    private function backToMission(Assignment $assignment, string $message)
    {
        return redirect()->to(route('assignments.show', $assignment).'#deliverables')->with('success', $message);
    }

    private function rules(bool $withStatus = false): array
    {
        $rules = [
            'type' => ['required', Rule::in(array_keys(config('influboss.deliverable_types')))],
            'platform' => ['nullable', Rule::in(array_keys(config('influboss.platforms')))],
            'quantity' => 'required|integer|min:1|max:100',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string|max:2000',
        ];

        if ($withStatus) {
            $rules['status'] = ['required', Rule::in(array_keys(config('influboss.deliverable_statuses')))];
        }

        return $rules;
    }

    private function messages(): array
    {
        return [
            'type.in' => 'Type de livrable inconnu.',
            'platform.in' => 'Plateforme inconnue.',
            'quantity.min' => 'La quantité doit être d\'au moins 1.',
            'due_date.date' => 'La date limite n\'est pas valide.',
            'status.in' => 'Statut inconnu.',
        ];
    }
}
