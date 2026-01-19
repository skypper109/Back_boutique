<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaiementCredit extends Model
{
    use HasFactory;

    protected $table = 'paiements_credit';
    protected $guarded = [];

    public function vente()
    {
        return $this->belongsTo(Vente::class);
    }

    public function boutique()
    {
        return $this->belongsTo(Boutique::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
