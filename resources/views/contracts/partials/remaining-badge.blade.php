{{-- Badge « expire dans N jours » pour un contrat actif. $contract requis. --}}
@php $days = $contract->days_remaining; @endphp
@if($days !== null)
    @if($days < 0)
        <span class="badge badge-dark">Échéance dépassée</span>
    @elseif($days === 0)
        <span class="badge badge-danger">Expire aujourd'hui</span>
    @elseif($days <= 7)
        <span class="badge badge-danger">Expire dans {{ $days }} j</span>
    @elseif($contract->isExpiringSoon())
        <span class="badge badge-warning">Expire dans {{ $days }} j</span>
    @endif
@endif
