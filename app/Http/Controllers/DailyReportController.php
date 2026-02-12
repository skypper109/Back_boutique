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
        $boutiqueId = ($user && $user->role === 'admin') ? $request->input('boutique_id') : ($user ? $user->boutique_id : null);

        $query = DailyReport::with('boutique')
            ->when($boutiqueId, fn($q) => $q->where('boutique_id', $boutiqueId))
            ->orderBy('date', 'desc');

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $reports = $query->paginate(20);

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
        $boutiqueId = ($user && $user->role === 'admin') 
            ? ($request->boutique_id ?? Boutique::first()->id)
            : ($user ? $user->boutique_id : ($request->boutique_id ?? Boutique::first()->id));

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

        $ventes = Vente::with(['detailVentes.produit', 'client', 'user'])
            ->where('boutique_id', $boutiqueId)
            ->whereDate('date_vente', $date)
            ->get();

        $depenses = Expense::with('user')
            ->where('boutique_id', $boutiqueId)
            ->whereDate('date', $date)
            ->get();

        $ventesNet = $ventes->sum(function ($vente) {
            return $vente->montant_total - ($vente->montant_remis ?? 0);
        });

        $ventesBrut = $ventes->sum('montant_total');
        $remises = $ventes->sum('montant_remis');
        $depensesTotal = $depenses->sum('montant');
        $beneficeNet = $ventesNet - $depensesTotal;

        $totaux = [
            'ventes_brut' => $ventesBrut,
            'remises' => $remises,
            'ventes_net' => $ventesNet,
            'depenses' => $depensesTotal,
            'benefice_net' => $beneficeNet
        ];

        $stats = [
            'nombre_ventes' => $ventes->count(),
            'nombre_depenses' => $depenses->count(),
            'vente_moyenne' => $ventes->count() > 0 ? $ventesNet / $ventes->count() : 0,
            'ventes_credit' => $ventes->where('type_paiement', 'credit')->count(),
            'ventes_cash' => $ventes->where('type_paiement', '!=', 'credit')->count()
        ];

        return [
            'boutique' => $boutique,
            'date' => $date,
            'ventes' => $ventes,
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
