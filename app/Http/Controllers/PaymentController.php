<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    /**
     * Liste filtrable des paiements visibles, avec totaux de la sélection.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Payment::class);

        $query = $this->filteredQuery($request);
        $totals = Payment::totalsFor($query);
        $payments = $query->with(['influencer', 'contract'])
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [Payment::STATUS_PENDING])
            ->orderBy('due_date')
            ->orderByDesc('paid_at')
            ->paginate(20)
            ->withQueryString();

        return view('payments.index', [
            'payments' => $payments,
            'totals' => $totals,
            'statuses' => config('influboss.payment_statuses'),
            'methods' => config('influboss.payment_methods'),
        ]);
    }

    public function create(Request $request)
    {
        Gate::authorize('create', Payment::class);

        $payment = new Payment(['status' => Payment::STATUS_PENDING]);

        if ($request->filled('contract')) {
            $contract = Contract::find($request->integer('contract'));
            if ($contract) {
                $payment->contract_id = $contract->id;
                $payment->influencer_id = $contract->influencer_id;
            }
        }
        if ($request->filled('influencer')) {
            $payment->influencer_id = $request->integer('influencer');
        }
        if ($request->filled('assignment')) {
            $assignment = Assignment::find($request->integer('assignment'));
            if ($assignment) {
                $payment->assignment_id = $assignment->id;
                $payment->influencer_id = $assignment->influencer_id;
            }
        }

        return view('payments.create', $this->formData() + compact('payment'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Payment::class);

        $validated = $this->validatePayment($request);
        $payment = new Payment($validated);
        $payment->created_by = Auth::id();

        if ($request->hasFile('receipt')) {
            $this->attachReceipt($payment, $request->file('receipt'));
        }

        $this->saveOrDiscardReceipt($payment);

        return $this->redirectAfterSave($payment, 'Paiement enregistré.');
    }

    public function edit(Request $request, Payment $payment)
    {
        Gate::authorize('update', $payment);

        // « Marquer payé » : formulaire pré-rempli avec le statut payé et la date du jour.
        if ($request->boolean('mark_paid') && $payment->status !== Payment::STATUS_PAID) {
            $payment->status = Payment::STATUS_PAID;
            $payment->paid_at = now();
        }

        return view('payments.edit', $this->formData() + compact('payment'));
    }

    public function update(Request $request, Payment $payment)
    {
        Gate::authorize('update', $payment);

        $validated = $this->validatePayment($request);
        $payment->fill($validated);
        $payment->updated_by = Auth::id();

        $replaced = null;
        if ($request->hasFile('receipt')) {
            $replaced = $payment->receipt_path;
            $this->attachReceipt($payment, $request->file('receipt'));
        }

        $this->saveOrDiscardReceipt($payment);

        if ($replaced) {
            Storage::disk(Payment::DISK)->delete($replaced);
        }

        return $this->redirectAfterSave($payment, 'Paiement mis à jour.');
    }

    public function destroy(Payment $payment)
    {
        Gate::authorize('delete', $payment);

        $payment->delete();

        return redirect()->route('payments.index')->with('success', 'Paiement supprimé.');
    }

    public function receipt(Payment $payment)
    {
        Gate::authorize('downloadReceipt', $payment);

        if (! $payment->receipt_path || ! Storage::disk(Payment::DISK)->exists($payment->receipt_path)) {
            abort(404);
        }

        return Storage::disk(Payment::DISK)->download(
            $payment->receipt_path,
            $payment->receipt_original_name ?: basename($payment->receipt_path)
        );
    }

    /**
     * Export CSV (séparateur « ; », lisible dans Excel) des paiements filtrés.
     */
    public function export(Request $request)
    {
        Gate::authorize('export', Payment::class);

        $payments = $this->filteredQuery($request)->with(['influencer', 'contract'])->orderBy('due_date')->get();

        $callback = function () use ($payments) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, ['ID', 'Ambassadeur', 'Libellé', 'Montant (FCFA)', 'Échéance', 'Statut', 'Payé le', 'Moyen', 'Référence', 'Contrat', 'Mission', 'Notes'], ';');
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->id,
                    $payment->influencer?->name,
                    $payment->label,
                    number_format((float) $payment->amount, 0, '', ''),
                    $payment->due_date?->format('d/m/Y'),
                    $payment->display_status_label,
                    $payment->paid_at?->format('d/m/Y'),
                    $payment->method_label,
                    $payment->reference,
                    $payment->contract ? '#'.$payment->contract->id.' '.$payment->contract->type_label : '',
                    $payment->assignment_id ? '#'.$payment->assignment_id : '',
                    $payment->notes,
                ], ';');
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="paiements_'.date('Y-m-d').'.csv"',
        ]);
    }

    /**
     * Paiements visibles filtrés par statut (dont « overdue »), moyen, ambassadeur, contrat et période d'échéance.
     */
    private function filteredQuery(Request $request): Builder
    {
        $query = Payment::visibleTo(Auth::user());

        $status = $request->input('status');
        if ($status === 'overdue') {
            $query->overdue();
        } elseif ($status && array_key_exists($status, config('influboss.payment_statuses'))) {
            $query->where('status', $status);
        }
        if ($request->filled('method') && array_key_exists($request->method, config('influboss.payment_methods'))) {
            $query->where('method', $request->method);
        }
        if ($request->filled('influencer_id')) {
            $query->where('influencer_id', $request->integer('influencer_id'));
        }
        if ($request->filled('contract_id')) {
            $query->where('contract_id', $request->integer('contract_id'));
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q->where('label', 'like', "%{$search}%")
                ->orWhere('reference', 'like', "%{$search}%")
                ->orWhereHas('influencer', fn ($u) => $u->where('name', 'like', "%{$search}%")));
        }
        if ($request->filled('from')) {
            $query->whereDate('due_date', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('due_date', '<=', $request->date('to'));
        }

        return $query;
    }

    private function validatePayment(Request $request): array
    {
        $influencerId = (int) $request->input('influencer_id');

        $validated = $request->validate([
            'influencer_id' => ['required', Rule::exists('users', 'id')->where('role', 'influencer')],
            'contract_id' => ['nullable', Rule::exists('contracts', 'id')->where('influencer_id', $influencerId)->whereNull('deleted_at')],
            'assignment_id' => ['nullable', Rule::exists('assignments', 'id')->where('influencer_id', $influencerId)->whereNull('deleted_at')],
            'label' => 'required|string|max:255',
            'amount' => 'required|numeric|gt:0|max:9999999999999',
            'due_date' => 'nullable|date',
            'status' => ['required', Rule::in(array_keys(config('influboss.payment_statuses')))],
            'paid_at' => 'nullable|required_if:status,paid|date',
            'method' => ['nullable', Rule::in(array_keys(config('influboss.payment_methods')))],
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
            'receipt' => 'nullable|file|mimes:'.config('influboss.upload_mimes').'|max:'.config('influboss.upload_max_kb'),
        ], [
            'influencer_id.exists' => 'Cet utilisateur n\'est pas un ambassadeur.',
            'contract_id.exists' => 'Ce contrat n\'appartient pas à cet ambassadeur.',
            'assignment_id.exists' => 'Cette mission n\'appartient pas à cet ambassadeur.',
            'amount.gt' => 'Le montant doit être supérieur à 0.',
            'paid_at.required_if' => 'Indiquez la date de paiement.',
            'method.in' => 'Moyen de paiement inconnu.',
            'receipt.mimes' => 'Le justificatif doit être un PDF, JPG ou PNG.',
            'receipt.max' => 'Le justificatif ne doit pas dépasser 10 Mo.',
        ]);

        // La date de paiement n'a de sens que pour un paiement effectué.
        if ($validated['status'] !== Payment::STATUS_PAID) {
            $validated['paid_at'] = null;
        }
        unset($validated['receipt']);

        return $validated;
    }

    private function attachReceipt(Payment $payment, UploadedFile $file): void
    {
        $payment->receipt_path = $file->store("payments/{$payment->influencer_id}", Payment::DISK);
        $payment->receipt_original_name = $file->getClientOriginalName();
    }

    /**
     * Enregistre le paiement ; en cas d'échec, supprime le justificatif tout juste déposé.
     */
    private function saveOrDiscardReceipt(Payment $payment): void
    {
        try {
            $payment->save();
        } catch (\Throwable $e) {
            if ($payment->isDirty('receipt_path') && $payment->receipt_path) {
                Storage::disk(Payment::DISK)->delete($payment->receipt_path);
            }
            throw $e;
        }
    }

    /**
     * Retour vers le contrat concerné, sinon vers la fiche de l'ambassadeur.
     */
    private function redirectAfterSave(Payment $payment, string $message)
    {
        $url = $payment->contract_id
            ? route('contracts.show', $payment->contract_id).'#payments'
            : route('influencers.show', $payment->influencer_id).'#payments';

        return redirect()->to($url)->with('success', $message);
    }

    private function formData(): array
    {
        return [
            'influencers' => User::where('role', 'influencer')->orderBy('name')->get(['id', 'name', 'pseudo']),
            'contracts' => Contract::with('influencer:id,name')->orderByDesc('end_date')->get(),
            'assignments' => Assignment::with('campaign:id,name')->orderByDesc('id')->get(),
            'statuses' => config('influboss.payment_statuses'),
            'methods' => config('influboss.payment_methods'),
        ];
    }
}
