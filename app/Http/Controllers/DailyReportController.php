<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\Boutique;
use App\Models\Vente;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Mail\DailyReportMail;
use Illuminate\Support\Facades\Mail;
use App\Services\WhatsAppService;

class DailyReportController extends Controller
{
    protected $whatsApp;

    public function __construct(WhatsAppService $whatsApp)
    {
        $this->whatsApp = $whatsApp;
    }
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $boutique_id = $this->getBoutiqueId();

        $query = DailyReport::with('boutique')
            ->when($boutique_id, fn($q) => $q->where('boutique_id', $boutique_id))
            ->orderBy('date', 'desc')
            ->limit(3);

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $reports = $query->paginate(3);

        return response()->json($reports);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'boutique_id' => 'nullable|exists:boutiques,id',
            'regenerate' => 'nullable|boolean'
        ]);

        $user = Auth::user();
        
        $boutiqueId = $this->getBoutiqueId();
        $date = Carbon::parse($request->date)->format('Y-m-d');

        try {
            // Check if exists for regeneration message
            $exists = DailyReport::where('boutique_id', $boutiqueId)->whereDate('date', $date)->exists();
            
            $report = $this->generateReport($boutiqueId, $date);

            return response()->json([
                'success' => true,
                'message' => $exists ? 'Rapport mis à jour avec succès' : 'Rapport généré avec succès',
                'report' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la génération du rapport',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $user = Auth::user();
        $report = DailyReport::with('boutique')->findOrFail($id);

        if ($user && $user->role !== 'admin' && $report->boutique_id !== $user->boutique_id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        return response()->json($report);
    }

    public function download($id)
    {
        $user = Auth::user();
        $report = DailyReport::with('boutique')->findOrFail($id);

        if ($user && $user->role !== 'admin' && $report->boutique_id !== $user->boutique_id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        if ($report->pdf_path && Storage::exists($report->pdf_path)) {
            return Storage::download($report->pdf_path);
        }

        $data = $this->loadReportData($report->boutique_id, $report->date->format('Y-m-d'));
        $pdf = PDF::loadView('pdf.rapport_journalier', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif'
            ]);

        $filename = "rapport-{$report->boutique->nom}-{$report->date->format('Y-m-d')}.pdf";
        return $pdf->download($filename);
    }

    public function sendEmail($id)
    {
        $user = Auth::user();
        $report = DailyReport::with('boutique')->findOrFail($id);

        if ($user && $user->role !== 'admin' && $report->boutique_id !== $user->boutique_id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        try {
            $this->sendReportEmail($report);

            return response()->json([
                'success' => true,
                'message' => 'Rapport envoyé par email avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de l\'envoi de l\'email',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function sendWhatsApp($id)
    {
        $user = Auth::user();
        $report = DailyReport::with('boutique')->findOrFail($id);

        if ($user && $user->role !== 'admin' && $report->boutique_id !== $user->boutique_id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        try {
            $this->sendReportWhatsApp($report);

            return response()->json([
                'success' => true,
                'message' => 'Rapport envoyé via WhatsApp avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de l\'envoi WhatsApp',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function generateReport($boutiqueId, $date)
    {
        $boutique = Boutique::findOrFail($boutiqueId);
        $data = $this->loadReportData($boutiqueId, $date);

        $report = DailyReport::updateOrCreate(
            [
                'boutique_id' => $boutiqueId,
                'date' => $date
            ],
            [
                'total_ventes' => $data['totaux']['ventes_net'],
                'total_depenses' => $data['totaux']['depenses'],
                'benefice_net' => $data['totaux']['benefice_net'],
                'nombre_ventes' => $data['stats']['nombre_ventes'],
                'nombre_depenses' => $data['stats']['nombre_depenses']
            ]
        );

        $pdf = PDF::loadView('pdf.rapport_journalier', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif'
            ]);

        $filename = "rapports/rapport-{$boutique->nom}-{$date}.pdf";
        Storage::put($filename, $pdf->output());

        $report->pdf_path = $filename;
        $report->save();

        return $report;
    }

    private function loadReportData($boutiqueId, $date)
    {
        $boutique = Boutique::findOrFail($boutiqueId);

        $venteAnnulee = Vente::with(['detailVentes.produit', 'client', 'user'])
            ->where('boutique_id', $boutiqueId)
            ->where('statut', 'annulee')
            ->whereDate('date_vente', $date)
            ->get();

        $venteCredit = Vente::with(['detailVentes.produit', 'client', 'user'])
            ->where('boutique_id', $boutiqueId)
            ->where('statut', 'credit')
            ->whereDate('date_vente', $date)
            ->get();

        $ventes = Vente::with(['detailVentes.produit', 'client', 'user'])
            ->where('boutique_id', $boutiqueId)
            ->where('statut','annulee')
            ->where('statut', 'payee')
            ->whereDate('date_vente', $date)
            ->get();

        $depenses = Expense::with('user')
            ->where('boutique_id', $boutiqueId)
            ->whereDate('date', $date)
            ->get();

        // Summing remises from all active sales
        $totalRemise = $ventes->sum('remise') + $venteCredit->sum('remise');

        // Total Net for all active sales (amount actually to be received/perceived)
        $totalNetCash = $ventes->sum('montant_total');
        $totalNetCredit = $venteCredit->sum('montant_total');
        $ventes_net = $totalNetCash + $totalNetCredit;

        // Vente Brut = Net + Remise
        $ventesBrut = $ventes_net + $totalRemise;

        // Vente Annulee (for information)
        $ventesBrutAnnulee = $venteAnnulee->sum(function($v) {
            return $v->montant_total + $v->remise;
        });

        $depensesTotal = $depenses->sum('montant');
        $beneficeNet = $ventes_net - $depensesTotal;

        $totaux = [
            'ventes_brut' => $ventesBrut,
            'remises' => $totalRemise,
            'ventes_net' => $ventes_net,
            'ventes_annulee_net' => $venteAnnulee->sum('montant_total'),
            'ventes_credit_net' => $totalNetCredit,
            'depenses' => $depensesTotal,
            'benefice_net' => $beneficeNet
        ];

        $stats = [
            'nombre_ventes' => $ventes->count() + $venteCredit->count(),
            'nombre_depenses' => $depenses->count(),
            'vente_moyenne' => ($ventes->count() + $venteCredit->count()) > 0 ? $ventes_net / ($ventes->count() + $venteCredit->count()) : 0,
            'ventes_credit' => $venteCredit->count(),
            'ventes_cash' => $ventes->count(),
            'ventes_annulee' => $venteAnnulee->count()
        ];

        return [
            'boutique' => $boutique,
            'date' => $date,
            'ventes' => $ventes,
            'ventes_annulee' => $venteAnnulee,
            'ventes_credit' => $venteCredit,
            'depenses' => $depenses,
            'totaux' => $totaux,
            'stats' => $stats
        ];
    }

    private function sendReportEmail(DailyReport $report)
    {
        $admins = \App\Models\User::where('role', 'admin')
            ->where('boutique_id', $report->boutique_id)
            ->whereNotNull('email')
            ->get();

        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new DailyReportMail($report));
        }

        $report->sent_at = now();
        $report->save();
    }

    private function sendReportWhatsApp(DailyReport $report)
    {
        $admins = \App\Models\User::where('role', 'admin')
            ->where('boutique_id', $report->boutique_id)
            ->get();

        $boutiqueName = $report->boutique->nom;
        $date = $report->date->format('d/m/Y');
        $ventes = number_format($report->total_ventes, 0, '.', ' ');
        $benefice = number_format($report->benefice_net, 0, '.', ' ');

        $message = "📊 *Rapport Journalier - {$boutiqueName}*\n";
        $message .= "📅 Date: {$date}\n\n";
        $message .= "💰 Ventes Net: {$ventes} CFA\n";
        $message .= "📉 Dépenses: " . number_format($report->total_depenses, 0, '.', ' ') . " CFA\n";
        $message .= "✨ Bénéfice Net: *{$benefice} CFA*\n\n";
        $message .= "📝 Lien du rapport: " . url("/api/reports/{$report->id}/download");

        foreach ($admins as $admin) {
            // Use phone if available, or boutique phone as fallback for admin
            $phone = $admin->telephone ?? $report->boutique->telephone;
            if ($phone) {
                $this->whatsApp->sendMessage($phone, $message);
            }
        }
    }
}
