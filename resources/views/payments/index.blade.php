@extends('layouts.adminlte')

@section('title', auth()->user()->role === 'influencer' ? 'Mes paiements' : 'Paiements')

@section('breadcrumb')
<li class="breadcrumb-item active">Paiements</li>
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {{ session('success') }}
</div>
@endif

@include('payments.partials.totals')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Liste des paiements</h3>
        <div class="card-tools">
            @can('export', \App\Models\Payment::class)
            <a href="{{ route('payments.export', request()->query()) }}" class="btn btn-success btn-sm"><i class="fas fa-file-csv mr-1"></i> Export CSV</a>
            @endcan
            @can('create', \App\Models\Payment::class)
            <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm ml-1"><i class="fas fa-plus mr-1"></i> Nouveau paiement</a>
            @endcan
        </div>
    </div>
    <div class="card-body p-0">
        <div class="p-3 bg-light">
            <form action="{{ route('payments.index') }}" method="GET" class="row">
                <div class="col-md-3 mb-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="{{ auth()->user()->role === 'influencer' ? 'Libellé, référence…' : 'Ambassadeur, libellé, référence…' }}" value="{{ request('search') }}">
                </div>
                <div class="col-md-2 mb-2">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">Statut (Tous)</option>
                        @foreach($statuses as $key => $status)
                            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $status['label'] }}</option>
                        @endforeach
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>En retard</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select name="method" class="form-control form-control-sm">
                        <option value="">Moyen (Tous)</option>
                        @foreach($methods as $key => $label)
                            <option value="{{ $key }}" {{ request('method') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text">Échéance</span></div>
                        <input type="date" name="from" class="form-control" value="{{ request('from') }}" aria-label="Du">
                        <input type="date" name="to" class="form-control" value="{{ request('to') }}" aria-label="Au">
                    </div>
                </div>
                <div class="col-md-2 mb-2">
                    <button type="submit" class="btn btn-sm btn-info mr-1">Filtrer</button>
                    <a href="{{ route('payments.index') }}" class="btn btn-sm btn-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>

        @include('payments.partials.table', ['showInfluencer' => auth()->user()->role !== 'influencer'])
    </div>
    <div class="card-footer clearfix">
        <div class="float-right">{{ $payments->links('pagination.bootstrap-4') }}</div>
    </div>
</div>
@endsection
