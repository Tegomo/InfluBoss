<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CampaignApiController;
use App\Http\Controllers\API\InfluencerApiController;
use App\Http\Controllers\API\PublicationApiController;
use App\Http\Controllers\API\StatApiController;
use App\Http\Controllers\API\AuthApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route pour l'authentification API
Route::post('/login', [AuthApiController::class, 'login']);

// Routes protégées par authentification API
Route::middleware('auth:sanctum')->group(function () {
    // Informations utilisateur
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Déconnexion
    Route::post('/logout', [AuthApiController::class, 'logout']);
    
    // API Campagnes
    Route::apiResource('campaigns', CampaignApiController::class);
    Route::get('/campaigns/{campaign}/stats', [CampaignApiController::class, 'stats']);
    Route::get('/campaigns/{campaign}/influencers', [CampaignApiController::class, 'influencers']);
    Route::get('/campaigns/{campaign}/publications', [CampaignApiController::class, 'publications']);
    Route::get('/campaigns/{campaign}/contents', [CampaignApiController::class, 'contents']);
    
    // API Influenceurs
    Route::apiResource('influencers', InfluencerApiController::class);
    Route::get('/influencers/{influencer}/campaigns', [InfluencerApiController::class, 'campaigns']);
    Route::get('/influencers/{influencer}/publications', [InfluencerApiController::class, 'publications']);
    Route::get('/influencers/{influencer}/stats', [InfluencerApiController::class, 'stats']);
    
    // API Publications
    Route::apiResource('publications', PublicationApiController::class);
    Route::post('/publications/{publication}/verify', [PublicationApiController::class, 'verify']);
    Route::post('/publications/{publication}/refresh-stats', [PublicationApiController::class, 'refreshStats']);
    
    // API Statistiques
    Route::get('/stats/global', [StatApiController::class, 'global']);
    Route::get('/stats/platforms', [StatApiController::class, 'platforms']);
    Route::get('/stats/trends', [StatApiController::class, 'trends']);
    Route::get('/stats/engagement', [StatApiController::class, 'engagement']);
});
