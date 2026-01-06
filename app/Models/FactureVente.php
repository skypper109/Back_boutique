<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactureVente extends Model
{
    use HasFactory;
    use HasFactory;
    protected $guarded = [];
    public function factures(){
        return $this->belongsTo(Facture::class);
    }
    public function vente(){
        return $this->belongsTo(Vente::class);
    }


}
