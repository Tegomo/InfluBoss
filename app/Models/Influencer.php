<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Influencer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'specialty',
        'bio',
        'profile_picture',
        'social_links',
        'status',
        'rating'
    ];

    protected $casts = [
        'social_links' => 'json',
        'rating' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function publications()
    {
        return $this->hasManyThrough(Publication::class, Assignment::class);
    }

    public function getSocialLinksAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function setSocialLinksAttribute($value)
    {
        $this->attributes['social_links'] = json_encode($value);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }
}
