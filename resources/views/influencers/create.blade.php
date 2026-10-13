@extends('layouts.adminlte')

@section('title')
Ajouter un nouvel ambassadeur
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('influencers.index') }}">Ambassadeurs</a></li>
<li class="breadcrumb-item active">Nouveau</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Informations de l'ambassadeur</h3>
            </div>
            <form method="POST" action="{{ route('influencers.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="name">Nom complet</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Nom complet" value="{{ old('name') }}" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="email">Adresse e-mail</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="pseudo">Pseudo / nom de la chaîne</label>
                                <input type="text" class="form-control @error('pseudo') is-invalid @enderror" id="pseudo" name="pseudo" placeholder="Pseudo sur les réseaux sociaux" value="{{ old('pseudo') }}">
                                @error('pseudo')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="phone">Téléphone</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" placeholder="Téléphone" value="{{ old('phone') }}">
                                @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="address">Adresse</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" placeholder="Quartier, rue…" value="{{ old('address') }}">
                                @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="city">Ville</label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" placeholder="Ville" value="{{ old('city') }}">
                                @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="categories">Catégories / niches</label>
                                <select name="categories[]" id="categories" multiple class="form-control select2 @error('categories') is-invalid @enderror" data-placeholder="Choisir une ou plusieurs catégories" style="width: 100%">
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}" {{ in_array($key, old('categories', [])) ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('categories')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @error('categories.*')
                                <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="password">Mot de passe</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Mot de passe" autocomplete="new-password" required>
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="password_confirmation">Confirmer le mot de passe</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirmer le mot de passe" autocomplete="new-password" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="profile_photo">Photo de profil</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input @error('profile_photo') is-invalid @enderror" id="profile_photo" name="profile_photo" accept="image/*">
                                        <label class="custom-file-label" for="profile_photo">Choisir une photo</label>
                                    </div>
                                </div>
                                @error('profile_photo')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <div class="mt-3">
                                    <div class="img-preview text-center">
                                        <i class="fas fa-user fa-5x text-secondary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="bio">Bio / Présentation</label>
                        <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" rows="3" placeholder="Courte biographie ou présentation">{{ old('bio') }}</textarea>
                        @error('bio')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>
                    <h5><i class="fas fa-share-alt mr-2"></i>Réseaux sociaux et abonnés</h5>
                    @error('social')
                    <div class="alert alert-danger py-1">{{ $message }}</div>
                    @enderror
                    @include('influencers.partials.social-accounts-fields', ['platforms' => $platforms])
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="{{ route('influencers.index') }}" class="btn btn-default">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        // Aperçu de la photo avant envoi
        $('#profile_photo').on('change', function () {
            if (!this.files[0]) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                $('.img-preview').html('<img src="' + e.target.result + '" class="img-fluid img-circle" style="max-height: 200px;">');
            };
            reader.readAsDataURL(this.files[0]);
            $('.custom-file-label').text(this.files[0].name);
        });
    });
</script>
@endpush
