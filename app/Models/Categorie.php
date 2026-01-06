<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Categorie extends Model
{
    use HasApiTokens;
    use HasFactory;
    // On definit les champs de la table categorie qui sont modifiables
    protected $fillable = ['nom', 'description'];
    // On definit la relation entre les categories et les produits
    public function produits(){
        return $this->hasMany(Produit::class);
    }


}
