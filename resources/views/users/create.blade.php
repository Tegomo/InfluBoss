@extends('layouts.adminlte')

@section('title', 'Créer un utilisateur')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('users.index') }}">Utilisateurs</a></li>
<li class="breadcrumb-item active">Créer</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Informations de l'utilisateur</h3>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Nom</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Entrez le nom complet">
                        @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="pseudo">Pseudo</label>
                        <input type="text" class="form-control @error('pseudo') is-invalid @enderror" id="pseudo" name="pseudo" value="{{ old('pseudo') }}" placeholder="Entrez le pseudo sur les réseaux sociaux">
                        @error('pseudo')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="birthdate">Date de naissance</label>
                        <input type="date" class="form-control @error('birthdate') is-invalid @enderror" id="birthdate" name="birthdate" value="{{ old('birthdate') }}">
                        @error('birthdate')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Entrez l'adresse email">
                        @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="phone">Téléphone</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Entrez le numéro de téléphone">
                        @error('phone')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="city">Ville</label>
                        <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city') }}" placeholder="Entrez la ville">
                        @error('city')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Entrez le mot de passe">
                        @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Confirmer le mot de passe</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirmez le mot de passe">
                    </div>
                    <div class="form-group">
                        <label for="role">Rôle</label>
                        <select class="form-control @error('role') is-invalid @enderror" id="role" name="role">
                            <option value="">Sélectionner un rôle</option>
                            @if(auth()->user()->role === 'super_admin')
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            @endif
                            <option value="supervisor" {{ old('role') === 'supervisor' ? 'selected' : '' }}>Superviseur</option>
                            <option value="influencer" {{ old('role') === 'influencer' ? 'selected' : '' }}>Influenceur</option>
                        </select>
                        @error('role')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Enregistrer</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Retour</a>
                </div>
            </form>
        </div>
        <!-- /.card -->
    </div>
</div>
@endsection
