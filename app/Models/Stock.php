<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;
    use HasFactory;
    // protected $fillable = ['produit_id','quantite','type','description','date'];
    protected $guarded = [];
    // On definit la relation entre les stocks et les produits
    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function boutique()
    {
        return $this->belongsTo(Boutique::class);
    }
}
