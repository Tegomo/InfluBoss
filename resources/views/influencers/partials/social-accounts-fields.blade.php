{{--
    Champs URL + nombre d'abonnés pour chaque plateforme.
    $platforms : config('influboss.platforms') ; $accounts : comptes existants indexés par plateforme (optionnel).
--}}
@php $accounts = $accounts ?? collect(); @endphp
<div class="row">
    @foreach($platforms as $key => $cfg)
        @php $account = $accounts[$key] ?? null; @endphp
        <div class="col-md-6">
            <div class="form-group">
                <label for="social_{{ $key }}_url">
                    <i class="{{ $cfg['icon'] }} mr-1" style="color: {{ $cfg['color'] }}"></i> {{ $cfg['label'] }}
                </label>
                <div class="input-group">
                    <input type="url"
                           class="form-control @error("social.$key.url") is-invalid @enderror"
                           id="social_{{ $key }}_url"
                           name="social[{{ $key }}][url]"
                           placeholder="{{ $cfg['url_prefix'] }}pseudo"
                           value="{{ old("social.$key.url", $account?->url) }}">
                    <input type="number" min="0" step="1"
                           class="form-control @error("social.$key.followers") is-invalid @enderror"
                           style="max-width: 9rem"
                           id="social_{{ $key }}_followers"
                           name="social[{{ $key }}][followers]"
                           placeholder="Abonnés"
                           aria-label="Abonnés {{ $cfg['label'] }}"
                           value="{{ old("social.$key.followers", $account?->followers_count) }}">
                </div>
                @error("social.$key.url")
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
                @error("social.$key.followers")
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
                @if($account?->last_synced_at)
                    <small class="form-text text-muted">Abonnés mis à jour le {{ $account->last_synced_at->format('d/m/Y') }}</small>
                @endif
            </div>
        </div>
    @endforeach
</div>
<p class="text-muted small mb-0">
    <i class="fas fa-info-circle mr-1"></i>
    Indiquez l'URL du profil et, si vous le connaissez, le nombre d'abonnés : chaque nouveau nombre est conservé dans l'historique.
    Vider l'URL supprime le compte et son historique.
</p>
