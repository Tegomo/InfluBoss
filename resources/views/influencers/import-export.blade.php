@extends('layouts.adminlte')

@section('title', 'Import/Export des influenceurs')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('users.index', ['role' => 'influencer']) }}">Influenceurs</a></li>
<li class="breadcrumb-item active">Import/Export</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Exporter les influenceurs</h3>
            </div>
            <div class="card-body">
                <p class="mb-3">
                    Téléchargez la liste complète des influenceurs au format Excel. 
                    <strong>{{ $influencersCount }}</strong> influenceurs disponibles pour export.
                </p>
                <a href="{{ route('influencers.export') }}" class="btn btn-success">
                    <i class="fas fa-download mr-1"></i> Exporter tous les influenceurs
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Importer des influenceurs</h3>
            </div>
            <div class="card-body">
                <p class="mb-3">
                    Importez de nouveaux influenceurs à partir d'un fichier Excel. Le fichier doit respecter le format requis.
                    <a href="{{ route('influencers.template') }}" class="text-info">
                        <i class="fas fa-file-excel"></i> Télécharger le modèle
                    </a>
                </p>
                
                <form action="{{ route('influencers.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('file') is-invalid @enderror" id="file" name="file">
                            <label class="custom-file-label" for="file">Choisir un fichier</label>
                            @error('file')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload mr-1"></i> Importer des influenceurs
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
                <h5>Format du fichier Excel</h5>
                <p>Le fichier d'importation doit respecter les colonnes suivantes :</p>
                <ul>
                    <li><strong>nom</strong>: Nom complet de l'influenceur (obligatoire)</li>
                    <li><strong>email</strong>: Adresse email unique (obligatoire)</li>
                    <li><strong>telephone</strong>: Numéro de téléphone (optionnel)</li>
                    <li><strong>instagram</strong>: Compte Instagram (optionnel)</li>
                    <li><strong>facebook</strong>: Compte Facebook (optionnel)</li>
                    <li><strong>youtube</strong>: Chaîne YouTube (optionnel)</li>
                    <li><strong>tiktok</strong>: Compte TikTok (optionnel)</li>
                    <li><strong>twitter</strong>: Compte Twitter (optionnel)</li>
                    <li><strong>linkedin</strong>: Compte LinkedIn (optionnel)</li>
                    <li><strong>actif</strong>: Statut (optionnel, "Oui" ou "Non", par défaut "Non")</li>
                </ul>
                <p class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i> Un mot de passe temporaire sera généré automatiquement pour chaque influenceur importé.
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
            var fileName = e.target.files[0].name;
            var nextSibling = e.target.nextElementSibling;
            nextSibling.innerText = fileName;
        });
    });
</script>
@endpush
