<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Les statuts possibles pour un contenu
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_APPROVED = 'approved';
    const STATUS_ARCHIVED = 'archived';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'campaign_id',
        'created_by',
        'type',
        'file_path',
        'content_text',
        'guidelines',
        'status',
        'publication_date',
        'tags'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'guidelines' => 'json',
        'publication_date' => 'date',
    ];
    
    /**
     * Get the campaign that owns this content
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
    
    /**
     * Get the user who created this content
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the publications that use this content
     */
    public function publications()
    {
        return $this->hasMany(Publication::class);
    }
    
    /**
     * Scope for draft content
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }
    
    /**
     * Scope for approved content
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
    
    /**
     * Scope for archived content
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }
    
    /**
     * Get the file URL attribute
     */
    public function getFileUrlAttribute()
    {
        if ($this->file_path) {
            return asset('storage/' . $this->file_path);
        }
        return null;
    }
}
