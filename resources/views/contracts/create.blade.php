@extends('layouts.adminlte')

@section('title', 'Nouveau contrat')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('contracts.index') }}">Contrats</a></li>
<li class="breadcrumb-item active">Nouveau</li>
@endsection

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Informations du contrat</h3>
    </div>
    <form method="POST" action="{{ route('contracts.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            @include('contracts._form')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="{{ url()->previous() }}" class="btn btn-default">Annuler</a>
        </div>
    </form>
</div>
@endsection
