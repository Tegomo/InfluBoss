<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ContractController extends Controller
{
    /**
     * Liste des contrats visibles par l'utilisateur, avec filtres.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Contract::class);

        $user = Auth::user();
        $types = config('influboss.contract_types');
        $statuses = config('influboss.contract_statuses');
        $alertWindow = max(config('influboss.contract_alert_days'));

        $query = Contract::visibleTo($user)->with('influencer');

        if ($request->filled('status') && array_key_exists($request->status, $statuses)) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type') && array_key_exists($request->type, $types)) {
            $query->where('type', $request->type);
        }
        if ($request->filled('influencer_id')) {
            $query->where('influencer_id', $request->integer('influencer_id'));
        }
        if ($request->boolean('expiring')) {
            $query->expiringWithin($alertWindow);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('influencer', fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('pseudo', 'like', "%{$search}%"));
        }

        $contracts = $query->orderBy('end_date')->paginate(15)->withQueryString();
        $expiringCount = Contract::visibleTo($user)->expiringWithin($alertWindow)->count();

        return view('contracts.index', compact('contracts', 'types', 'statuses', 'expiringCount', 'alertWindow'));
    }

    public function create(Request $request)
    {
        Gate::authorize('create', Contract::class);

        return view('contracts.create', $this->formData() + [
            'contract' => new Contract([
                'influencer_id' => $request->integer('influencer') ?: null,
                'status' => Contract::STATUS_NEGOTIATION,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Contract::class);

        $validated = $request->validate($this->rules(), $this->messages());
        $contract = new Contract($validated);
        $contract->created_by = Auth::id();

        if ($request->hasFile('file')) {
            $this->attachFile($contract, $request->file('file'));
        }

        $this->saveOrDiscardFile($contract);

        return redirect()->route('contracts.show', $contract)->with('success', 'Contrat enregistré.');
    }

    public function show(Contract $contract)
    {
        Gate::authorize('view', $contract);

        $contract->load(['influencer', 'creator', 'alerts']);

        // Échéancier : réservé aux admins et à l'influenceur du contrat (pas aux superviseurs).
        $canSeePayments = Gate::allows('viewAny', \App\Models\Payment::class);
        $payments = $canSeePayments ? $contract->payments()->get() : collect();
        $paymentSummary = $canSeePayments ? $contract->paymentSummary() : null;

        return view('contracts.show', compact('contract', 'canSeePayments', 'payments', 'paymentSummary'));
    }

    public function edit(Contract $contract)
    {
        Gate::authorize('update', $contract);

        return view('contracts.edit', $this->formData() + compact('contract'));
    }

    public function update(Request $request, Contract $contract)
    {
        Gate::authorize('update', $contract);

        $validated = $request->validate($this->rules(), $this->messages());
        $previousEndDate = $contract->end_date?->toDateString();

        $contract->fill($validated);
        $contract->updated_by = Auth::id();

        $replacedFile = null;
        if ($request->hasFile('file')) {
            $replacedFile = $contract->file_path;
            $this->attachFile($contract, $request->file('file'));
        }

        $this->saveOrDiscardFile($contract);

        // L'ancien fichier n'est supprimé qu'une fois le nouveau enregistré en base.
        if ($replacedFile) {
            Storage::disk(Contract::DISK)->delete($replacedFile);
        }

        // Prolongation ou modification de l'échéance : les alertes repartent de zéro.
        if ($previousEndDate !== $contract->end_date->toDateString()) {
            $contract->alerts()->delete();
        }

        return redirect()->route('contracts.show', $contract)->with('success', 'Contrat mis à jour.');
    }

    public function destroy(Contract $contract)
    {
        Gate::authorize('delete', $contract);

        $contract->delete();

        return redirect()->route('contracts.index')->with('success', 'Contrat supprimé.');
    }

    /**
     * Téléchargement du contrat scanné depuis le disque privé.
     */
    public function download(Contract $contract)
    {
        Gate::authorize('download', $contract);

        if (! $contract->file_path || ! Storage::disk(Contract::DISK)->exists($contract->file_path)) {
            abort(404);
        }

        return Storage::disk(Contract::DISK)->download(
            $contract->file_path,
            $contract->file_original_name ?: basename($contract->file_path)
        );
    }

    /**
     * Enregistre le contrat ; en cas d'échec, supprime le fichier qui vient d'être déposé pour ne pas
     * laisser de document orphelin sur le disque privé.
     */
    private function saveOrDiscardFile(Contract $contract): void
    {
        try {
            $contract->save();
        } catch (\Throwable $e) {
            if ($contract->isDirty('file_path') && $contract->file_path) {
                Storage::disk(Contract::DISK)->delete($contract->file_path);
            }
            throw $e;
        }
    }

    private function attachFile(Contract $contract, UploadedFile $file): void
    {
        $contract->file_path = $file->store("contracts/{$contract->influencer_id}", Contract::DISK);
        $contract->file_original_name = $file->getClientOriginalName();
    }

    private function formData(): array
    {
        return [
            'influencers' => User::where('role', 'influencer')->orderBy('name')->get(['id', 'name', 'pseudo']),
            'types' => config('influboss.contract_types'),
            'statuses' => config('influboss.contract_statuses'),
        ];
    }

    private function rules(): array
    {
        return [
            'influencer_id' => ['required', Rule::exists('users', 'id')->where('role', 'influencer')],
            'type' => ['required', Rule::in(array_keys(config('influboss.contract_types')))],
            'status' => ['required', Rule::in(array_keys(config('influboss.contract_statuses')))],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'amount' => 'nullable|numeric|min:0|max:9999999999999',
            'payment_terms' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:5000',
            'file' => 'nullable|file|mimes:'.config('influboss.upload_mimes').'|max:'.config('influboss.upload_max_kb'),
        ];
    }

    private function messages(): array
    {
        return [
            'influencer_id.required' => 'Choisissez un ambassadeur.',
            'influencer_id.exists' => 'Cet utilisateur n\'est pas un ambassadeur.',
            'type.in' => 'Type de contrat inconnu.',
            'status.in' => 'Statut inconnu.',
            'end_date.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
            'file.mimes' => 'Le document doit être un PDF, JPG ou PNG.',
            'file.max' => 'Le document ne doit pas dépasser 10 Mo.',
        ];
    }
}
