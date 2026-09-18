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
        'user_id',
        'logo',
        'description_facture',
        'description_bordereau',
        'description_recu',
        'footer_facture',
        'footer_bordereau',
        'footer_recu',
        'couleur_principale',
        'couleur_secondaire',
        'devise',
        'format_facture',
        'nature_id',
        'date_expiration_licence'
    ];

    protected $casts = [
        'date_expiration_licence' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function licences()
    {
        return $this->hasMany(Licence::class)->orderByDesc('created_at');
    }

    /**
     * Vérifie si la licence de la boutique a expiré
     */
    public function isLicenceExpired(): bool
    {
        if (!$this->is_active) {
            return true;
        }

        // Si aucune date d'expiration n'a été fixée (nouvelle boutique ou période illimitée)
        if ($this->date_expiration_licence === null) {
            return false;
        }

        return now()->greaterThan($this->date_expiration_licence);
    }

    /**
     * Retourne le nombre de jours restants avant expiration (ou null si illimité)
     */
    public function joursRestants(): ?int
    {
        if ($this->date_expiration_licence === null) {
            return null;
        }

        $diff = (int) now()->diffInDays($this->date_expiration_licence, false);
        return $diff;
    }

    public function nature()
    {
        return $this->belongsTo(Nature::class);
    }

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

