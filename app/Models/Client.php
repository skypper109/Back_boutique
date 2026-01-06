<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    use HasFactory;
    protected $guarded = [];
    public function factures(){
        return $this->hasMany(Facture::class);
    }
}
