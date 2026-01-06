<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function factureVentes()
    {
        return $this->hasMany(FactureVente::class);
    }

    public function factureDetail()
    {
        return $this->hasMany(FactureVente::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
