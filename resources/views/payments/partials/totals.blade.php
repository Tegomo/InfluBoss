{{-- Totaux payé / en attente / en retard. $totals requis. --}}
<div class="row">
    <div class="col-md-4">
        <div class="info-box mb-2">
            <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Payé</span>
                <span class="info-box-number">{{ number_format($totals['paid'], 0, ',', ' ') }} FCFA</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box mb-2">
            <span class="info-box-icon bg-warning"><i class="fas fa-hourglass-half"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">En attente</span>
                <span class="info-box-number">{{ number_format($totals['pending'], 0, ',', ' ') }} FCFA</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box mb-2">
            <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">dont en retard</span>
                <span class="info-box-number">{{ number_format($totals['overdue'], 0, ',', ' ') }} FCFA</span>
            </div>
        </div>
    </div>
</div>
