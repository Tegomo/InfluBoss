{{-- Tableau de paiements. $payments requis ; $showInfluencer (défaut true). --}}
@php $showInfluencer = $showInfluencer ?? true; @endphp
<div class="table-responsive">
    <table class="table table-hover table-sm mb-0">
        <thead>
            <tr>
                @if($showInfluencer)<th>Ambassadeur</th>@endif
                <th>Libellé</th>
                <th class="text-right">Montant (FCFA)</th>
                <th>Échéance</th>
                <th>Statut</th>
                <th>Paiement</th>
                <th class="text-right"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
            <tr>
                @if($showInfluencer)
                <td>
                    @if($payment->influencer && auth()->user()->role !== 'influencer')
                        <a href="{{ route('influencers.show', $payment->influencer) }}">{{ $payment->influencer->name }}</a>
                    @else
                        {{ $payment->influencer?->name }}
                    @endif
                </td>
                @endif
                <td>
                    {{ $payment->label }}
                    @if($payment->contract_id)
                        <br><small class="text-muted">Contrat <a href="{{ route('contracts.show', $payment->contract_id) }}">#{{ $payment->contract_id }}</a>{{ $payment->assignment_id ? ' · mission #' . $payment->assignment_id : '' }}</small>
                    @elseif($payment->assignment_id)
                        <br><small class="text-muted">Mission <a href="{{ route('assignments.show', $payment->assignment_id) }}">#{{ $payment->assignment_id }}</a></small>
                    @endif
                </td>
                <td class="text-right">{{ number_format((float) $payment->amount, 0, ',', ' ') }}</td>
                <td>{{ $payment->due_date?->format('d/m/Y') ?? '—' }}</td>
                <td><span class="badge badge-{{ $payment->display_status_badge }}">{{ $payment->display_status_label }}</span></td>
                <td class="small">
                    @if($payment->status === \App\Models\Payment::STATUS_PAID)
                        le {{ $payment->paid_at?->format('d/m/Y') }}{{ $payment->method_label ? ' · ' . $payment->method_label : '' }}
                        @if($payment->reference)<br><span class="text-muted">Réf. {{ $payment->reference }}</span>@endif
                    @else
                        —
                    @endif
                </td>
                <td class="text-right text-nowrap">
                    @if($payment->receipt_path)
                        <a href="{{ route('payments.receipt', $payment) }}" class="btn btn-xs btn-secondary" title="Justificatif : {{ $payment->receipt_original_name }}"><i class="fas fa-receipt"></i></a>
                    @endif
                    @can('update', $payment)
                        @if($payment->status === \App\Models\Payment::STATUS_PENDING)
                            <a href="{{ route('payments.edit', [$payment, 'mark_paid' => 1]) }}" class="btn btn-xs btn-success" title="Marquer payé"><i class="fas fa-check"></i> Payé</a>
                        @endif
                        <a href="{{ route('payments.edit', $payment) }}" class="btn btn-xs btn-primary" title="Modifier"><i class="fas fa-edit"></i></a>
                    @endcan
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ $showInfluencer ? 7 : 6 }}" class="text-center text-muted">Aucun paiement.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
