<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailVente extends Model
{
    use HasFactory;
    use HasFactory;
    protected $guarded = [];
    // On definit la relation entre les details de vente et les ventes
    public function vente(){
        return $this->belongsTo(Vente::class);
    }
    public function produit(){
        return $this->belongsTo(Produit::class);
    }
    // On definit la relation entre les details de vente et les produits
    public function scopeTopVente($query){
        return $query
        ->selectRaw('produit_id, prix_unitaire, SUM(quantite) as total_quantite, SUM(montant_total) as total_montant')
        ->groupBy('produit_id', 'prix_unitaire')
        ->orderByDesc('total_quantite')
        ->limit(5);
    }

    // On definit la relation entre les details de vente et les ventes
    public function scopeHistoriqueVente($query){
        return $query
        ->orderBy('date', 'desc');
    }

    public function scopeDetailVente($query,$id){
        return $query
        ->join('ventes','ventes.id','=','detail_ventes.vente_id')
        ->select('detail_ventes.*','ventes.date')
        ->where('detail_ventes.vente_id',$id);
    }

    public function scopeDetailVenteDate($query,$id,$date){
        return $query
        ->join('ventes','ventes.id','=','detail_ventes.vente_id')
        ->select('detail_ventes.*','ventes.date')
        ->where('detail_ventes.vente_id',$id)
        ->where('ventes.date',$date);
    }

    public function scopeDetailVenteProduit($query,$id){
        return $query
        ->join('ventes','ventes.id','=','detail_ventes.vente_id')
        ->join('produits','produits.id','=','detail_ventes.produit_id')
        ->select('detail_ventes.*','ventes.date','produits.nom','produits.prix_vente')
        ->where('detail_ventes.produit_id',$id);
    }

    public function scopeDetailVenteProduitDate($query,$id,$date){
        return $query
        ->join('ventes','ventes.id','=','detail_ventes.vente_id')
        ->join('produits','produits.id','=','detail_ventes.produit_id')
        ->select('detail_ventes.*','ventes.date','produits.nom','produits.prix_vente')
        ->where('detail_ventes.produit_id',$id)
        ->where('ventes.date',$date);
    }

    public function scopeDetailVenteProduitDateVente($query,$id,$date,$vente){
        return $query
        ->join('ventes','ventes.id','=','detail_ventes.vente_id')
        ->join('produits','produits.id','=','detail_ventes.produit_id')
        ->select('detail_ventes.*','ventes.date','produits.nom','produits.prix_vente')
        ->where('detail_ventes.produit_id',$id)
        ->where('ventes.date',$date)
        ->where('ventes.id',$vente);
    }
}
