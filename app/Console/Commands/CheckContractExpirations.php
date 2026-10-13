<?php

namespace App\Console\Commands;

use App\Services\ContractExpiryService;
use Illuminate\Console\Command;

class CheckContractExpirations extends Command
{
    protected $signature = 'contracts:check-expirations';

    protected $description = 'Passe en « expiré » les contrats échus et envoie les alertes avant expiration (30/15/7 jours)';

    public function handle(ContractExpiryService $service): int
    {
        $result = $service->run();

        $this->info("{$result['expired']} contrat(s) passé(s) en « Expiré ».");
        $this->info("{$result['alerts']} alerte(s) d'expiration envoyée(s).");

        return self::SUCCESS;
    }
}
