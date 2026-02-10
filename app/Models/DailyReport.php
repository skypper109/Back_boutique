<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'boutique_id',
        'date',
        'total_ventes',
        'total_depenses',
        'benefice_net',
        'nombre_ventes',
        'nombre_depenses',
        'pdf_path',
        'sent_at'
    ];

    protected $casts = [
        'date' => 'date',
        'total_ventes' => 'float',
        'total_depenses' => 'float',
        'benefice_net' => 'float',
        'sent_at' => 'datetime'
    ];

    public function boutique()
    {
        return $this->belongsTo(Boutique::class);
    }

    public function scopeForBoutique($query, $boutiqueId)
    {
        return $query->where('boutique_id', $boutiqueId);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeRecent($query, $limit = 30)
    {
        return $query->orderBy('date', 'desc')->limit($limit);
    }

    public function getIsSentAttribute()
    {
        return !is_null($this->sent_at);
    }
}
