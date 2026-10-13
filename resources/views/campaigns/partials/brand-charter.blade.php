{{-- Charte de marque d'une campagne (lecture). $campaign requis. --}}
<div class="card card-outline card-warning">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-palette mr-1"></i> Charte de marque</h3>
    </div>
    <div class="card-body">
        @if($campaign && $campaign->hasBrandGuidelines())
            <dl class="row mb-0">
                @if(filled($campaign->products))
                    <dt class="col-sm-3">Produits / services à promouvoir</dt>
                    <dd class="col-sm-9">{!! nl2br(e($campaign->products)) !!}</dd>
                @endif
                @if(filled($campaign->brand_tone))
                    <dt class="col-sm-3">Ton</dt>
                    <dd class="col-sm-9">{!! nl2br(e($campaign->brand_tone)) !!}</dd>
                @endif
                @if(filled($campaign->visual_guidelines))
                    <dt class="col-sm-3">Consignes visuelles</dt>
                    <dd class="col-sm-9">{!! nl2br(e($campaign->visual_guidelines)) !!}</dd>
                @endif
                @if(! empty($campaign->required_hashtags))
                    <dt class="col-sm-3">Hashtags obligatoires</dt>
                    <dd class="col-sm-9">
                        @foreach($campaign->required_hashtags as $tag)
                            <span class="badge badge-primary mr-1">{{ $tag }}</span>
                        @endforeach
                    </dd>
                @endif
                @if(! empty($campaign->required_mentions))
                    <dt class="col-sm-3">Mentions obligatoires</dt>
                    <dd class="col-sm-9">
                        @foreach($campaign->required_mentions as $mention)
                            <span class="badge badge-info mr-1">{{ $mention }}</span>
                        @endforeach
                    </dd>
                @endif
            </dl>
        @else
            <p class="text-muted mb-0">Aucune charte de marque renseignée pour cette campagne.</p>
        @endif
    </div>
</div>
