<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\InfluencerController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StatisticsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Route d'accueil et d'authentification
// Redirection vers la page de login
Route::get('/', function () {
    return redirect()->route('login');
});

// Routes d'authentification
Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Routes du dashboard
Route::middleware(['auth'])->group(function () {
    // Routes pour les utilisateurs
    Route::resource('users', \App\Http\Controllers\UserController::class);
    Route::get('/users-all', [\App\Http\Controllers\UserController::class, 'indexAll'])->name('users.all');
    Route::get('/influencers-list', [\App\Http\Controllers\UserController::class, 'indexInfluencers'])->name('users.influencers');
    Route::get('/supervisors-list', [\App\Http\Controllers\UserController::class, 'indexSupervisors'])->name('users.supervisors');

    // Route principale du dashboard
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
});

// Redirection vers le tableau de bord après connexion
Route::middleware(['auth'])->group(function () {
    // Tableau de bord général (adapté selon le rôle)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Gestion du profil utilisateur
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Routes pour les campagnes
    Route::resource('campaigns', CampaignController::class);
    Route::get('/campaigns/{campaign}/dashboard', [CampaignController::class, 'dashboard'])->name('campaigns.dashboard');
    Route::get('/campaigns/{campaign}/influencers', [CampaignController::class, 'influencers'])->name('campaigns.influencers');
    Route::get('/campaigns/{campaign}/contents', [CampaignController::class, 'contents'])->name('campaigns.contents');
    Route::get('/campaigns/{campaign}/get-contents', [CampaignController::class, 'getContents'])->name('campaigns.get-contents');
    
    // Routes pour les contenus
    Route::resource('contents', ContentController::class);
    Route::get('/contents/{content}/download', [ContentController::class, 'download'])->name('contents.download');
    
    // Routes pour les influenceurs
    Route::resource('influencers', InfluencerController::class);
    Route::get('/influencers/{influencer}/assign-campaigns', [InfluencerController::class, 'assignCampaigns'])
        ->name('influencers.assign-campaigns');
    Route::post('/influencers/{influencer}/store-assignment', [InfluencerController::class, 'storeAssignment'])
        ->name('influencers.store-assignment');
    Route::get('/influencers/{influencer}/dashboard', [InfluencerController::class, 'dashboard'])
        ->name('influencers.dashboard');
        
    // Routes pour l'import/export des influenceurs
    Route::get('/influencers-import-export', [\App\Http\Controllers\InfluencerImportExportController::class, 'index'])
        ->name('influencers.import-export');
    Route::get('/influencers-export', [\App\Http\Controllers\InfluencerImportExportController::class, 'export'])
        ->name('influencers.export');
    Route::get('/influencers-template', [\App\Http\Controllers\InfluencerImportExportController::class, 'downloadTemplate'])
        ->name('influencers.template');
    Route::post('/influencers-import', [\App\Http\Controllers\InfluencerImportExportController::class, 'import'])
        ->name('influencers.import');
    
    // Routes pour les publications
    Route::resource('publications', PublicationController::class);
    Route::post('/publications/{publication}/verify', [PublicationController::class, 'verify'])->name('publications.verify');
    Route::post('/publications/{publication}/reject', [PublicationController::class, 'reject'])->name('publications.reject');
    Route::post('/publications/{publication}/refresh-stats', [PublicationController::class, 'refreshStats'])
        ->name('publications.refresh-stats');
    Route::post('/publications/{publication}/capture-screenshot', [PublicationController::class, 'captureScreenshot'])
        ->name('publications.capture-screenshot');
    Route::post('/publications/{publication}/add-note', [PublicationController::class, 'addNote'])
        ->name('publications.add-note');
    Route::get('/publications/{publication}/stats', [PublicationController::class, 'statsHistory'])
        ->name('publications.stats');

    // Routes pour les missions
    Route::resource('assignments', AssignmentController::class);
    Route::get('/assignments/{assignment}/dashboard', [AssignmentController::class, 'dashboard'])
        ->name('assignments.dashboard');
        
    // Routes pour les notes
    Route::get('/assignments/{assignment}/notes', [\App\Http\Controllers\NoteController::class, 'index'])->name('notes.index');
    Route::get('/assignments/{assignment}/notes/create', [\App\Http\Controllers\NoteController::class, 'create'])->name('notes.create');
    Route::post('/assignments/{assignment}/notes', [\App\Http\Controllers\NoteController::class, 'store'])->name('notes.store');
    Route::get('/notes/{note}/edit', [\App\Http\Controllers\NoteController::class, 'edit'])->name('notes.edit');
    Route::put('/notes/{note}', [\App\Http\Controllers\NoteController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{note}', [\App\Http\Controllers\NoteController::class, 'destroy'])->name('notes.destroy');
    
    // API routes pour les notes
    Route::post('/api/assignments/{assignment}/notes', [\App\Http\Controllers\NoteController::class, 'apiStore'])->name('api.notes.store');
    Route::delete('/api/notes/{note}', [\App\Http\Controllers\NoteController::class, 'apiDestroy'])->name('api.notes.destroy');
    
    // Routes pour les superviseurs (accès temporairement sans restriction)
    // NOTE: Protection middleware désactivée temporairement en raison du problème avec le middleware 'role'
    Route::resource('supervisors', SupervisorController::class);
    Route::post('/supervisors/{supervisor}/assign-influencer', [SupervisorController::class, 'assignInfluencer'])->name('supervisors.assign-influencer');
    Route::delete('/supervisors/{supervisor}/unassign-influencer/{influencer}', [SupervisorController::class, 'unassignInfluencer'])->name('supervisors.unassign-influencer');
    
    // Routes du tableau de bord du superviseur (protection temporairement désactivée)
    Route::get('/supervisor/dashboard', [SupervisorController::class, 'dashboard'])->name('supervisor.dashboard');
    Route::get('/supervisor/pending-publications', [SupervisorController::class, 'pendingPublications'])
        ->name('supervisor.pending-publications');
    
    // Routes pour l'administration (accès restreint)
    Route::middleware(['\App\Http\Middleware\RoleMiddleware:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
        // Tableau de bord administrateur
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // Gestion des administrateurs (super admin uniquement)
        Route::middleware(['\App\Http\Middleware\RoleMiddleware:super_admin'])->group(function () {
            Route::get('/administrators', [AdminController::class, 'administrators'])->name('administrators');
            Route::get('/administrators/create', [AdminController::class, 'createAdministrator'])->name('administrators.create');
            Route::post('/administrators', [AdminController::class, 'storeAdministrator'])->name('administrators.store');
        });
        
        // Gestion des configurations API (super admin uniquement)
        Route::middleware(['\App\Http\Middleware\RoleMiddleware:super_admin'])->group(function () {
            Route::get('/api-configurations', [AdminController::class, 'apiConfigurations'])->name('api-configurations');
            Route::get('/api-configurations/create', [AdminController::class, 'createApiConfiguration'])->name('api-configurations.create');
            Route::post('/api-configurations', [AdminController::class, 'storeApiConfiguration'])->name('api-configurations.store');
            Route::get('/api-configurations/{apiConfiguration}/edit', [AdminController::class, 'editApiConfiguration'])
                ->name('api-configurations.edit');
            Route::put('/api-configurations/{apiConfiguration}', [AdminController::class, 'updateApiConfiguration'])
                ->name('api-configurations.update');
            Route::delete('/api-configurations/{apiConfiguration}', [AdminController::class, 'destroyApiConfiguration'])
                ->name('api-configurations.destroy');
            Route::post('/api-configurations/{apiConfiguration}/test', [AdminController::class, 'testApiConfiguration'])
                ->name('api-configurations.test');
        });
        
        // Statistiques globales
        Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics');
        Route::get('/statistics/export', [StatisticsController::class, 'export'])->name('statistics.export');
        Route::get('/statistics/export-pdf', [StatisticsController::class, 'exportPdf'])->name('statistics.export-pdf');
        Route::get('/statistics/export-raw-data', [StatisticsController::class, 'exportRawData'])->name('statistics.export-raw-data');
    });
});

require __DIR__.'/auth.php';
