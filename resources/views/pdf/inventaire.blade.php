@extends('pdf.layouts.base')

@section('title', 'Inventaire')
@section('orientation', 'landscape')

@section('styles')
    <style>
        .inventaire-table {
            width: 100%;
            font-size: 8pt;
            border-collapse: collapse;
            margin-bottom: 10mm;
        }

        .inventaire-table th {
            background-color: #f1f5f9;
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            font-size: 7pt;
            font-weight: 900;
            color: #64748b;
            text-transform: uppercase;
        }

        .inventaire-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            font-size: 8pt;
        }

        .inventaire-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .stat-box {
            background-color: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 10px;
            padding: 10px;
            text-align: center;
        }

        .stat-box.dark {
            background-color: #0f172a;
            color: white;
            border-color: #0f172a;
        }
    </style>
@endsection

@section('content')
    <div
        style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10mm; padding-bottom: 8mm; border-bottom: 2px solid #0f172a;">
        <div>
            <h1 style="font-size: 18pt; font-weight: 900; text-transform: uppercase; margin-bottom: 3px;">Rapport d'Audit
                Journalier</h1>
            <p style="font-size: 7pt; font-weight: 900; color: #f59e0b; text-transform: uppercase; letter-spacing: 0.2em;">
                Journal d'Inventaire & Mouvements de Stock
            </p>
        </div>
        <div style="text-align: right;">
            <p style="font-size: 14pt; font-weight: 900; margin-bottom: 5px;">{{ $boutique->nom ?? 'Ma Boutique' }}</p>
            <div style="font-size: 7pt; font-weight: 700; color: #64748b;">
                <p style="margin: 2px 0;">Période: {{ now()->format('d/m/Y') }}</p>
                <p style="margin: 2px 0;">{{ count($inventaires) }} Opérations</p>
            </div>
        </div>
    </div>

    <!-- Summary Grid -->
    <div style="display: flex; gap: 10px; margin-bottom: 10mm;">
        <div class="stat-box" style="flex: 1;">
            <span
                style="display: block; font-size: 7pt; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 3px;">Total
                Entrées</span>
            <span style="font-size: 12pt; font-weight: 900;">{{ $stats['totalEntrees'] }} Unités</span>
        </div>
        <div class="stat-box" style="flex: 1;">
            <span
                style="display: block; font-size: 7pt; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 3px;">Total
                Sorties</span>
            <span style="font-size: 12pt; font-weight: 900;">{{ $stats['totalSorties'] }} Unités</span>
        </div>
        <div class="stat-box" style="flex: 1;">
            <span
                style="display: block; font-size: 7pt; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 3px;">Valeur
                Entrante</span>
            <span style="font-size: 12pt; font-weight: 900;">{{ number_format($stats['valeurAchatEntrante'], 0, ',', ' ') }}
                F</span>
        </div>
        <div class="stat-box dark" style="flex: 1;">
            <span
                style="display: block; font-size: 7pt; font-weight: 900; color: rgba(255,255,255,0.5); text-transform: uppercase; margin-bottom: 3px;">Variation
                Nette</span>
            <span
                style="font-size: 12pt; font-weight: 900;">{{ number_format($stats['valeurVenteSortante'] - $stats['valeurAchatEntrante'], 0, ',', ' ') }}
                F</span>
        </div>
    </div>

    <!-- Table Compacte -->
    <table class="inventaire-table">
        <thead>
            <tr>
                <th style="text-align: left;">Réf / Date</th>
                <th style="text-align: left;">Article</th>
                <th style="text-align: left;">Nature / Motif</th>
                <th style="text-align: center;">Qté</th>
                <th style="text-align: right;">Val. Unit</th>
                <th style="text-align: right; font-weight: 900;">Impact</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($inventaires as $item)
                <tr style="font-style: italic;">
                    <td>{{ $item->id }} - {{ \Carbon\Carbon::parse($item->created_at)->format('d/m H:i') }}</td>
                    <td style="font-weight: 700;">{{ $item->produit->nom ?? 'N/A' }}</td>
                    <td style="text-transform: uppercase;">{{ $item->description }}</td>
                    <td
                        style="text-align: center; font-weight: 900; color: {{ $item->type === 'retrait' ? '#dc2626' : '#16a34a' }};">
                        {{ $item->type === 'retrait' ? '-' : '+' }}{{ $item->quantite }}
                    </td>
                    <td style="text-align: right;">
                        {{ number_format($item->type === 'retrait' ? $item->produit->stock->prix_vente ?? 0 : $item->produit->stock->prix_achat ?? 0, 0, ',', ' ') }}
                    </td>
                    <td style="text-align: right; font-weight: 700;">
                        {{ number_format($item->quantite * ($item->type === 'retrait' ? $item->produit->stock->prix_vente ?? 0 : $item->produit->stock->prix_achat ?? 0), 0, ',', ' ') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Signatures -->
    <div style="display: flex; justify-content: space-between; gap: 50px; padding-top: 10mm;">
        <div style="text-align: center; flex: 1;">
            <p
                style="font-size: 8pt; font-weight: 900; text-transform: uppercase; text-decoration: underline; margin-bottom: 60px;">
                Le Responsable Stock</p>
        </div>
        <div style="text-align: center; flex: 1;">
            <p
                style="font-size: 8pt; font-weight: 900; text-transform: uppercase; text-decoration: underline; margin-bottom: 60px;">
                Visa Direction / Audit</p>
        </div>
    </div>

    <p style="text-align: center; font-size: 7pt; color: #94a3b8; font-style: italic; margin-top: 15mm;">
        Document d'audit officiel - {{ $boutique->nom ?? 'Ma Boutique' }} - Rapport généré le
        {{ now()->format('d/m/Y H:i') }}
    </p>
@endsection
