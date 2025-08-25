<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupervisorInfluencerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vider d'abord la table pivot pour éviter les doublons lors des re-seeds
        DB::table('supervisor_influencer')->truncate();
        
        // Récupérer les 5 superviseurs actifs
        $supervisors = User::where('role', 'supervisor')
            ->where('is_active', true)
            ->take(5)
            ->get();
            
        // Récupérer les 16 influenceurs actifs
        $influencers = User::where('role', 'influencer')
            ->where('is_active', true)
            ->take(16)
            ->get();
            
        // Répartir les influenceurs entre les superviseurs de manière équitable
        // Pour avoir exactement 3 influenceurs par superviseur (15 au total)
        // et 1 influenceur supplémentaire pour le premier superviseur
        $supervisorCount = $supervisors->count(); // 5
        $influencerCount = $influencers->count(); // 16
        
        if ($supervisorCount > 0) {
            // Distribution de base : 3 influenceurs par superviseur
            $baseInfluencersPerSupervisor = 3;
            
            // Distribuer les influenceurs aux superviseurs
            $currentInfluencerIndex = 0;
            
            foreach ($supervisors as $supervisorIndex => $supervisor) {
                // Nombre d'influenceurs à attribuer à ce superviseur
                $influencersToAssign = $baseInfluencersPerSupervisor;
                
                // Le premier superviseur reçoit un influenceur de plus pour arriver à 16 au total
                if ($supervisorIndex == 0) {
                    $influencersToAssign += 1;
                }
                
                // Assigner les influenceurs à ce superviseur
                for ($i = 0; $i < $influencersToAssign && $currentInfluencerIndex < $influencerCount; $i++) {
                    $influencer = $influencers[$currentInfluencerIndex];
                    
                    DB::table('supervisor_influencer')->insert([
                        'supervisor_id' => $supervisor->id,
                        'influencer_id' => $influencer->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    $currentInfluencerIndex++;
                }
            }
        }
    }
}
