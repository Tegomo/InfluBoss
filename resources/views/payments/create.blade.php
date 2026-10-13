@extends('layouts.adminlte')

@section('title', 'Nouveau paiement')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Paiements</a></li>
<li class="breadcrumb-item active">Nouveau</li>
@endsection

@section('content')
<div class="card card-primary">
    <div class="card-header"><h3 class="card-title">Paiement</h3></div>
    <form method="POST" action="{{ route('payments.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            @include('payments._form')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="{{ url()->previous() }}" class="btn btn-default">Annuler</a>
        </div>
    </form>
</div>
@endsection
