@extends('layouts.adminlte')

@section('title', 'Modifier le livrable')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('assignments.index') }}">Missions</a></li>
<li class="breadcrumb-item"><a href="{{ route('assignments.show', $deliverable->assignment) }}">Mission #{{ $deliverable->assignment_id }}</a></li>
<li class="breadcrumb-item active">Livrable</li>
@endsection

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">
            Livrable — {{ $deliverable->assignment->campaign?->name }} / {{ $deliverable->assignment->influencer?->name }}
        </h3>
    </div>
    <form method="POST" action="{{ route('deliverables.update', $deliverable) }}">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('deliverables.partials.fields', ['prefix' => 'edit'])
            <div class="form-group">
                <label for="edit_status" class="small">Statut</label>
                <select name="status" id="edit_status" class="form-control form-control-sm @error('status') is-invalid @enderror" style="max-width: 16rem">
                    @foreach($statuses as $key => $status)
                        <option value="{{ $key }}" {{ old('status', $deliverable->status) === $key ? 'selected' : '' }}>{{ $status['label'] }}</option>
                    @endforeach
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="form-text text-muted">
                    Le statut se met à jour tout seul quand des publications sont rattachées à ce livrable.
                    Un statut choisi ici est conservé tant qu'aucune publication n'y est rattachée.
                </small>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="{{ route('assignments.show', $deliverable->assignment) }}#deliverables" class="btn btn-default">Annuler</a>
        </div>
    </form>
</div>
@endsection
