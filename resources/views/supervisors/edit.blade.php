@extends('layouts.adminlte')

@section('title')
Modifier le superviseur: {{ $supervisor->name }}
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('supervisors.index') }}">Superviseurs</a></li>
<li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Modifier le superviseur</h3>
    </div>
    <!-- /.card-header -->
    <form action="{{ route('supervisors.update', $supervisor) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Nom</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $supervisor->name) }}" required>
                        @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $supervisor->email) }}" required>
                        @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="phone">Téléphone</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $supervisor->phone) }}">
                        @error('phone')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="city">Ville</label>
                        <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', $supervisor->city) }}">
                        @error('city')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="birthdate">Date de naissance</label>
                        <input type="date" class="form-control @error('birthdate') is-invalid @enderror" id="birthdate" name="birthdate" value="{{ old('birthdate', $supervisor->birthdate ? $supervisor->birthdate->format('Y-m-d') : '') }}">
                        @error('birthdate')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="profile_photo">Photo de profil</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('profile_photo') is-invalid @enderror" id="profile_photo" name="profile_photo">
                            <label class="custom-file-label" for="profile_photo">Choisir une image</label>
                        </div>
                        @error('profile_photo')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        @if($supervisor->profile_photo)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $supervisor->profile_photo) }}" alt="Photo de profil actuelle" class="img-thumbnail" style="max-height: 100px;">
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password">Nouveau mot de passe (laisser vide pour conserver l'actuel)</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                        @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password_confirmation">Confirmer le nouveau mot de passe</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ $supervisor->is_active ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Compte actif</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.card-body -->
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            <a href="{{ route('supervisors.show', $supervisor) }}" class="btn btn-default">Annuler</a>
        </div>
    </form>
</div>
<!-- /.card -->
@endsection

@section('js')
<script>
    // Script pour afficher le nom du fichier sélectionné dans l'input file
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass("selected").html(fileName);
    });
</script>
@endsection
