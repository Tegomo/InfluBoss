@extends('layouts.adminlte')

@section('title', 'Modifier le contrat')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('contracts.index') }}">Contrats</a></li>
<li class="breadcrumb-item"><a href="{{ route('contracts.show', $contract) }}">Contrat #{{ $contract->id }}</a></li>
<li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Contrat #{{ $contract->id }} — {{ $contract->influencer?->name }}</h3>
    </div>
    <form method="POST" action="{{ route('contracts.update', $contract) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('contracts._form')
            <p class="text-muted small mb-0"><i class="fas fa-info-circle mr-1"></i> Modifier la date de fin réinitialise les alertes d'expiration de ce contrat.</p>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            <a href="{{ route('contracts.show', $contract) }}" class="btn btn-default">Annuler</a>
        </div>
    </form>
</div>
@endsection
