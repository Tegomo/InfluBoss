<?php

namespace App\Services;

use App\Models\ApiConfiguration;
use App\Models\Publication;
use App\Models\Stat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrightDataService
{
    protected $config;
    protected $apiType;
    
    /**
     * Initialize the service with the appropriate API configuration
     */
    public function __construct($apiType = 'brightdata_social')
    {
        $this->apiType = $apiType;
        $this->loadConfig($apiType);
    }
    
    /**
     * Load the configuration from the database
     */
    protected function loadConfig($apiType)
    {
        $apiConfig = ApiConfiguration::where('api_type', $apiType)
            ->where('is_active', true)
            ->first();
            
        if (!$apiConfig) {
            Log::warning("No active Bright Data API configuration found for type: $apiType");
            return;
        }
        
        $this->config = $apiConfig->getConfigAttribute();
    }
    
    /**
     * Fetch statistics for a publication
     */
    public function fetchStats(Publication $publication)
    {
        if (!$this->config) {
            return false;
        }
        
        try {
            $response = $this->makeApiCall($publication);
            
            if ($response && $response->successful()) {
                $data = $response->json();
                
                // Enregistrer les statistiques récupérées
                return $this->saveStats($publication, $data);
            } else {
                Log::error('Failed to fetch stats from Bright Data API', [
                    'publication_id' => $publication->id,
                    'status' => $response ? $response->status() : 'No response',
                    'body' => $response ? $response->body() : 'No response body'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception when fetching stats from Bright Data: ' . $e->getMessage(), [
                'publication_id' => $publication->id,
                'exception' => $e->getMessage()
            ]);
        }
        
        return false;
    }
    
    /**
     * Make the API call to Bright Data
     */
    protected function makeApiCall(Publication $publication)
    {
        $endpoint = $this->config['endpoint'] ?? 'https://api.brightdata.com/social';
        $headers = [
            'Authorization' => 'Bearer ' . $this->config['api_key'],
            'Content-Type' => 'application/json',
        ];
        
        $params = $this->buildRequestParams($publication);
        
        return Http::withHeaders($headers)->post($endpoint, $params);
    }
    
    /**
     * Build the request parameters based on the platform and post type
     */
    protected function buildRequestParams(Publication $publication)
    {
        $params = [
            'url' => $publication->post_url,
            'format' => 'json',
        ];
        
        // Adaptez les paramètres en fonction de la plateforme
        switch ($publication->platform) {
            case 'instagram':
                $params['platform'] = 'instagram';
                if ($publication->post_type === 'story') {
                    $params['content_type'] = 'story';
                } elseif ($publication->post_type === 'reel') {
                    $params['content_type'] = 'reel';
                }
                break;
                
            case 'facebook':
                $params['platform'] = 'facebook';
                if ($publication->post_type === 'story') {
                    $params['content_type'] = 'story';
                }
                break;
                
            case 'tiktok':
                $params['platform'] = 'tiktok';
                break;
                
            case 'twitter':
            case 'x':
                $params['platform'] = 'twitter';
                break;
                
            case 'youtube':
                $params['platform'] = 'youtube';
                if ($publication->post_type === 'short') {
                    $params['content_type'] = 'short';
                }
                break;
                
            case 'linkedin':
                $params['platform'] = 'linkedin';
                break;
        }
        
        // Add optional post ID if available
        if ($publication->post_id) {
            $params['post_id'] = $publication->post_id;
        }
        
        return $params;
    }
    
    /**
     * Save the statistics data into the database
     */
    protected function saveStats(Publication $publication, array $data)
    {
        try {
            $stats = new Stat([
                'publication_id' => $publication->id,
                'collected_at' => now(),
                'likes' => $data['likes'] ?? 0,
                'comments' => $data['comments'] ?? 0,
                'shares' => $data['shares'] ?? 0,
                'saves' => $data['saves'] ?? 0,
                'views' => $data['views'] ?? null,
                'clicks' => $data['clicks'] ?? null,
                'engagement_rate' => $data['engagement_rate'] ?? null,
                'reach' => $data['reach'] ?? null,
                'impressions' => $data['impressions'] ?? null,
                'demographics' => $data['demographics'] ?? null,
                'raw_data' => $data
            ]);
            
            $stats->save();
            return $stats;
        } catch (\Exception $e) {
            Log::error('Failed to save stats: ' . $e->getMessage(), [
                'publication_id' => $publication->id,
                'data' => $data,
                'exception' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Batch update statistics for multiple publications
     */
    public function batchUpdateStats($publications = null)
    {
        if (!$publications) {
            // Par défaut, récupérer les publications vérifiées des 7 derniers jours
            $publications = Publication::where('status', 'verified')
                ->where('published_at', '>=', now()->subDays(7))
                ->get();
        }
        
        $results = [
            'success' => 0,
            'failed' => 0,
            'total' => count($publications)
        ];
        
        foreach ($publications as $publication) {
            $success = $this->fetchStats($publication);
            
            if ($success) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }
        
        return $results;
    }
    
    /**
     * Test the API connection with current configuration
     * 
     * @return bool True if connection is successful, false otherwise
     */
    public function testApiConnection()
    {
        if (!$this->config) {
            return false;
        }
        
        try {
            // Préparer les paramètres de base pour le test
            $endpoint = $this->config['endpoint'] ?? 'https://api.brightdata.com/social';
            $headers = [
                'Authorization' => 'Bearer ' . $this->config['api_key'],
                'Content-Type' => 'application/json',
            ];
            
            // Les paramètres dépendent du type d'API
            $params = [];
            
            switch ($this->apiType) {
                case 'brightdata_social':
                    $params = [
                        'url' => 'https://www.instagram.com/instagram/',
                        'format' => 'json',
                        'test_mode' => true
                    ];
                    break;
                    
                case 'brightdata_serp':
                    $params = [
                        'query' => 'test',
                        'format' => 'json',
                        'test_mode' => true
                    ];
                    break;
                    
                case 'brightdata_web_unlocker':
                    $params = [
                        'url' => 'https://www.example.com',
                        'test_mode' => true
                    ];
                    break;
            }
            
            // Effectuer la requête avec le mode test pour ne pas consommer de crédits
            $response = Http::withHeaders($headers)->get($endpoint . '/test', $params);
            
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Exception when testing Bright Data API: ' . $e->getMessage());
            return false;
        }
    }
}
