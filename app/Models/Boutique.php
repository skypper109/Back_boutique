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
     * Vérifie si la boutique a une licence illimitée active
     */
    public function hasUnlimitedLicence(): bool
    {
        return $this->licences()
            ->where('statut', 'active')
            ->where('duree_jours', '>=', 90000)
            ->exists();
    }

    /**
     * Vérifie si la licence de la boutique a expiré ou n'est plus valide
     */
    public function isLicenceExpired(): bool
    {
        if (!$this->is_active) {
            return true;
        }

        // Si la boutique dispose d'une licence illimitée active
        if ($this->hasUnlimitedLicence()) {
            return false;
        }

        // Vérifier s'il existe au moins une licence active non révoquée
        $hasActiveLicence = $this->licences()
            ->where('statut', 'active')
            ->exists();

        if (!$hasActiveLicence) {
            return true;
        }

        // Si aucune date d'expiration n'est fixée
        if ($this->date_expiration_licence === null) {
            return true;
        }

        // Si la date d'expiration est dépassée par rapport à maintenant
        return now()->greaterThan($this->date_expiration_licence);
    }

    /**
     * Retourne le nombre de jours restants avant expiration (ou null si illimité)
     */
    public function joursRestants(): ?int
    {
        if ($this->hasUnlimitedLicence()) {
            return null;
        }

        if ($this->date_expiration_licence === null || $this->isLicenceExpired()) {
            return 0;
        }

        $diffHours = now()->diffInHours($this->date_expiration_licence, false);
        if ($diffHours <= 0) {
            return 0;
        }

        return (int) ceil($diffHours / 24);
    }

    /**
     * Recalcule et synchronise l'état de la licence de la boutique
     * en fonction de ses licences réelles actives.
     */
    public function recalculerLicence(): void
    {
        // 1. Licence illimitée active ?
        if ($this->hasUnlimitedLicence()) {
            $this->date_expiration_licence = null;
            $this->save();
            return;
        }

        // 2. Licences actives avec date d'expiration
        $activeLicences = $this->licences()
            ->where('statut', 'active')
            ->whereNotNull('date_expiration')
            ->get();

        if ($activeLicences->isEmpty()) {
            // Aucune licence active : date d'expiration passée pour bloquer l'accès
            $this->date_expiration_licence = now()->subMinute();
            $this->save();
            return;
        }

        // 3. Calculer la date maximale parmi les licences actives
        $maxExp = $activeLicences->max('date_expiration');
        if ($maxExp) {
            $this->date_expiration_licence = $maxExp;
        } else {
            $this->date_expiration_licence = now()->subMinute();
        }

        $this->save();
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

