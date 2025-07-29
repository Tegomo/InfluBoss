<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supervisor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'department',
        'expertise',
        'status'
    ];

    protected $casts = [
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

    public function campaigns()
    {
        return $this->hasManyThrough(Campaign::class, Assignment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
