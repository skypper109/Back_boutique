<?php

namespace App\Http\Controllers;

use App\Models\Vente;
use App\Models\Boutique;
use App\Models\Inventaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    /**
     * Generate PDF based on document type
     */
    public function generatePdf(Request $request)
    {
        $request->validate([
            'type' => 'required|in:facture,bordereau,recu_credit,inventaire',
            'id' => 'required|integer'
        ]);

        $type = $request->input('type');
        $id = $request->input('id');

        try {
            // Load data based on type
            $data = $this->loadData($type, $id);
            
            // Select template
            $template = match($type) {
                'facture' => 'pdf.facture',
                'bordereau' => 'pdf.bordereau',
                'recu_credit' => 'pdf.recu_credit',
                'inventaire' => 'pdf.inventaire',
            };

            // Configure PDF options
            $orientation = $type === 'inventaire' ? 'landscape' : 'portrait';
            
            // Generate PDF
            $pdf = PDF::loadView($template, $data)
                ->setPaper('a4', $orientation)
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'sans-serif'
                ]);

            $filename = "{$type}-{$id}.pdf";
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la génération du PDF',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview PDF in browser
     */
    public function previewPdf(Request $request, $type, $id)
    {
        try {
            $data = $this->loadData($type, $id);
            
            $template = match($type) {
                'facture' => 'pdf.facture',
                'bordereau' => 'pdf.bordereau',
                'recu_credit' => 'pdf.recu_credit',
                'inventaire' => 'pdf.inventaire',
            };

            $orientation = $type === 'inventaire' ? 'landscape' : 'portrait';
            
            $pdf = PDF::loadView($template, $data)
                ->setPaper('a4', $orientation);

            return $pdf->stream();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la prévisualisation',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Load data based on document type
     */
    private function loadData($type, $id)
    {
        $user = Auth::user();
        $boutiqueId = $user->role === 'admin' ? null : $user->boutique_id;

        switch ($type) {
            case 'facture':
            case 'bordereau':
                $vente = Vente::with(['detailVentes.produit', 'client', 'user', 'boutique'])
                    ->when($boutiqueId, fn($q) => $q->where('boutique_id', $boutiqueId))
                    ->findOrFail($id);
                
                return [
                    'vente' => $vente,
                    'boutique' => $vente->boutique,
                    'type' => $type,
                    'date' => now()
                ];

            case 'recu_credit':
                $vente = Vente::with(['detailVentes.produit', 'client', 'user', 'boutique', 'paiementsCredit.user'])
                    ->when($boutiqueId, fn($q) => $q->where('boutique_id', $boutiqueId))
                    ->findOrFail($id);
                
                return [
                    'vente' => $vente,
                    'boutique' => $vente->boutique,
                    'paiements' => $vente->paiementsCredit,
                    'date' => now()
                ];

            case 'inventaire':
                // Get boutique
                $boutique = $boutiqueId 
                    ? Boutique::findOrFail($boutiqueId)
                    : Boutique::first();

                // Get inventaires for this boutique
                $inventaires = Inventaire::with(['produit.stock', 'user'])
                    ->where('boutique_id', $boutique->id)
                    ->orderBy('created_at', 'desc')
                    ->get();

                // Calculate stats
                $stats = [
                    'totalEntrees' => $inventaires->where('type', 'ajout')->sum('quantite'),
                    'totalSorties' => $inventaires->where('type', 'retrait')->sum('quantite'),
                    'valeurAchatEntrante' => 0,
                    'valeurVenteSortante' => 0,
                ];

                foreach ($inventaires as $inv) {
                    if ($inv->type === 'retrait') {
                        $stats['valeurVenteSortante'] += ($inv->quantite * ($inv->produit->stock->prix_vente ?? 0));
                    } else {
                        $stats['valeurAchatEntrante'] += ($inv->quantite * ($inv->produit->stock->prix_achat ?? 0));
                    }
                }

                $stats['netMouvement'] = $stats['totalEntrees'] - $stats['totalSorties'];

                return [
                    'boutique' => $boutique,
                    'inventaires' => $inventaires,
                    'stats' => $stats,
                    'date' => now()
                ];

            default:
                throw new \Exception('Type de document invalide');
        }
    }
}
