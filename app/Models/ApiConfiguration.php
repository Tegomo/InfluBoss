<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiConfiguration extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'api_type',
        'api_key',
        'username',
        'password',
        'endpoint_url',
        'additional_config',
        'is_active'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'additional_config' => 'json',
        'is_active' => 'boolean',
    ];
    
    /**
     * Scope for active API configurations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope for Bright Data social media APIs
     */
    public function scopeSocialMedia($query)
    {
        return $query->where('api_type', 'brightdata_social');
    }
    
    /**
     * Scope for Bright Data SERP APIs
     */
    public function scopeSerp($query)
    {
        return $query->where('api_type', 'brightdata_serp');
    }
    
    /**
     * Scope for Bright Data Web Unlocker APIs
     */
    public function scopeWebUnlocker($query)
    {
        return $query->where('api_type', 'brightdata_web_unlocker');
    }
    
    /**
     * Get the formatted configuration for API clients
     */
    public function getConfigAttribute()
    {
        $config = [
            'api_key' => $this->api_key,
            'endpoint' => $this->endpoint_url,
        ];
        
        if ($this->username) {
            $config['username'] = $this->username;
        }
        
        if ($this->password) {
            $config['password'] = $this->password;
        }
        
        if ($this->additional_config) {
            $config = array_merge($config, $this->additional_config);
        }
        
        return $config;
    }
}
