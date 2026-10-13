@extends('layouts.adminlte')

@section('title', 'Modifier le paiement')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Paiements</a></li>
<li class="breadcrumb-item active">Paiement #{{ $payment->id }}</li>
@endsection

@section('content')
<div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Paiement #{{ $payment->id }} — {{ $payment->influencer?->name }}</h3></div>
    <form method="POST" action="{{ route('payments.update', $payment) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('payments._form')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="{{ url()->previous() }}" class="btn btn-default">Annuler</a>
        </div>
    </form>
    @can('delete', $payment)
    <div class="card-footer border-top-0 pt-0 text-right">
        <form method="POST" action="{{ route('payments.destroy', $payment) }}" class="d-inline" onsubmit="return confirm('Supprimer ce paiement ?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash mr-1"></i> Supprimer</button>
        </form>
    </div>
    @endcan
</div>
@endsection
