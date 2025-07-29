<?php

namespace App\Providers;

use App\Models\Campaign;
use App\Models\Content;
use App\Models\Publication;
use App\Models\User;
use App\Policies\CampaignPolicy;
use App\Policies\ContentPolicy;
use App\Policies\PublicationPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Campaign::class => CampaignPolicy::class,
        Publication::class => PublicationPolicy::class,
        Content::class => ContentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Enregistrement des politiques définies ci-dessus
        $this->registerPolicies();

        // Définition des portes pour les actions administratives globales
        Gate::define('access-admin', function ($user) {
            return in_array($user->role, ['super_admin', 'admin']);
        });

        Gate::define('access-supervisor', function ($user) {
            return in_array($user->role, ['super_admin', 'admin', 'supervisor']);
        });

        Gate::define('manage-system', function ($user) {
            return $user->role === 'super_admin';
        });

        Gate::define('view-analytics', function ($user) {
            return in_array($user->role, ['super_admin', 'admin', 'supervisor']);
        });
    }
}
