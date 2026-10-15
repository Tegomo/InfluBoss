@extends('layouts.adminlte')

@section('title', 'Import/Export des Ambassadeurs')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('influencers.index') }}">Ambassadeurs</a></li>
<li class="breadcrumb-item active">Import/Export</li>
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {{ session('success') }}
</div>
@endif
@if($errors->has('error'))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <strong>{{ $errors->first('error') }}</strong>
    @if($errors->has('errors'))
        <ul class="mb-0 mt-2">
            @foreach($errors->get('errors') as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    @endif
</div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Exporter les Ambassadeurs</h3>
            </div>
            <div class="card-body">
                <p class="mb-3">
                    Téléchargez la liste complète des Ambassadeurs au format CSV (lisible dans Excel), avec leurs catégories, leurs comptes sociaux et leur nombre d'abonnés.
                    <strong>{{ $influencersCount }}</strong> Ambassadeurs disponibles pour export.
                </p>
                <a href="{{ route('influencers.export') }}" class="btn btn-success">
                    <i class="fas fa-download mr-1"></i> Exporter tous les Ambassadeurs
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Importer des Ambassadeurs</h3>
            </div>
            <div class="card-body">
                <p class="mb-3">
                    Importez de nouveaux Ambassadeurs à partir d'un fichier CSV respectant le format ci-dessous.
                    <a href="{{ route('influencers.template') }}" class="text-info">
                        <i class="fas fa-file-csv"></i> Télécharger le modèle
                    </a>
                </p>

                <form action="{{ route('influencers.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('file') is-invalid @enderror" id="file" name="file" accept=".csv,text/csv">
                            <label class="custom-file-label" for="file">Choisir un fichier</label>
                            @error('file')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload mr-1"></i> Importer des Ambassadeurs
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Instructions pour l'importation</h3>
            </div>
            <div class="card-body">
                <h5>Format du fichier CSV</h5>
                <p>Première ligne : les en-têtes ci-dessous (séparateur virgule, encodage UTF-8). Les colonnes optionnelles peuvent être omises.</p>
                <ul>
                    <li><strong>nom</strong> : nom complet de l'ambassadeur (obligatoire)</li>
                    <li><strong>email</strong> : adresse email unique (obligatoire)</li>
                    <li><strong>pseudo</strong>, <strong>telephone</strong>, <strong>adresse</strong>, <strong>ville</strong> (optionnels)</li>
                    <li>
                        <strong>categories</strong> : une ou plusieurs catégories séparées par « | » (optionnel), parmi :
                        @foreach($categories as $key => $label)
                            <code>{{ $key }}</code>@if(!$loop->last), @endif
                        @endforeach
                    </li>
                    @foreach($platforms as $key => $cfg)
                        <li><strong>{{ $key }}</strong> : URL du profil {{ $cfg['label'] }} ou @pseudo (optionnel) ; <strong>{{ $key }}_abonnes</strong> : nombre d'abonnés {{ $cfg['label'] }} (entier, optionnel)</li>
                    @endforeach
                    <li><strong>actif</strong> : statut (optionnel, « Oui » ou « Non », par défaut « Non »)</li>
                </ul>
                <p class="alert alert-info mb-0">
                    <i class="fas fa-info-circle mr-1"></i> Chaque ambassadeur importé reçoit le mot de passe par défaut <code>{{ config('influboss.default_influencer_password') }}</code>. Les nombres d'abonnés fournis sont enregistrés comme premier relevé de l'historique.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Affichage du nom du fichier sélectionné
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('.custom-file-input').addEventListener('change', function(e) {
            var fileName = e.target.files[0] ? e.target.files[0].name : 'Choisir un fichier';
            e.target.nextElementSibling.innerText = fileName;
        });
    });
</script>
@endpush
