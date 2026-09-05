<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_name',
        'company_name',
        'role_title',
        'project_name',
        'quote',
        'rating',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'rating'    => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Scope to only active testimonials.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Generate 2-letter uppercase initials for client avatar.
     */
    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim($this->client_name));
        $initials = '';

        foreach ($words as $w) {
            $cleaned = preg_replace('/[^a-zA-Z]/', '', $w);
            if (!empty($cleaned)) {
                $initials .= strtoupper($cleaned[0]);
            }
            if (strlen($initials) >= 2) {
                break;
            }
        }

        return $initials ?: 'CL';
    }
}
