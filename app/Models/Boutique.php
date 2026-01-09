<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boutique extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'adresse',
        'telephone',
        'email',
        'is_active',
        'user_id'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function ventes()
    {
        return $this->hasMany(Vente::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
}
