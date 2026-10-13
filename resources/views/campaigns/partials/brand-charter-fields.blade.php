{{-- Saisie de la charte de marque. $campaign facultatif (édition). --}}
@php $campaign = $campaign ?? null; @endphp
<div class="card-body border-top">
    <h5 class="mb-3"><i class="fas fa-palette mr-1"></i> Charte de marque <small class="text-muted">(visible par les ambassadeurs sur leurs missions)</small></h5>

    <div class="form-group">
        <label for="products">Produits / services à promouvoir</label>
        <textarea class="form-control @error('products') is-invalid @enderror" id="products" name="products" rows="2" placeholder="Ex. Forfaits Max It, Orange Money">{{ old('products', $campaign?->products) }}</textarea>
        @error('products')<span class="invalid-feedback">{{ $message }}</span>@enderror
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="brand_tone">Ton</label>
                <textarea class="form-control @error('brand_tone') is-invalid @enderror" id="brand_tone" name="brand_tone" rows="3" placeholder="Ex. chaleureux, inclusif, tutoiement, sans jargon technique">{{ old('brand_tone', $campaign?->brand_tone) }}</textarea>
                @error('brand_tone')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="visual_guidelines">Consignes visuelles</label>
                <textarea class="form-control @error('visual_guidelines') is-invalid @enderror" id="visual_guidelines" name="visual_guidelines" rows="3" placeholder="Ex. logo visible dans les 3 premières secondes, couleurs Orange, pas de marque concurrente à l'image">{{ old('visual_guidelines', $campaign?->visual_guidelines) }}</textarea>
                @error('visual_guidelines')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="required_hashtags">Hashtags obligatoires</label>
                <input type="text" class="form-control @error('required_hashtags') is-invalid @enderror" id="required_hashtags" name="required_hashtags" placeholder="#OrangeCameroun #4GPourTous" value="{{ old('required_hashtags', implode(' ', $campaign?->required_hashtags ?? [])) }}">
                <small class="form-text text-muted">Séparés par des espaces ou des virgules ; le « # » est ajouté si besoin.</small>
                @error('required_hashtags')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="required_mentions">Mentions obligatoires</label>
                <input type="text" class="form-control @error('required_mentions') is-invalid @enderror" id="required_mentions" name="required_mentions" placeholder="@orangecameroun" value="{{ old('required_mentions', implode(' ', $campaign?->required_mentions ?? [])) }}">
                <small class="form-text text-muted">Comptes à identifier ; le « @ » est ajouté si besoin.</small>
                @error('required_mentions')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>
</div>
