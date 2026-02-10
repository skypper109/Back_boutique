@extends('pdf.layouts.base')

@section('title', 'Rapport d\'Audit Inventaire')
@section('orientation', 'landscape')

@section('styles')
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1a1a1a;
            line-height: 1.4;
        }

        .inventory-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 4px solid #34495e;
        }

        .company-name {
            font-size: 24pt;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .report-title {
            font-size: 18pt;
            font-weight: 700;
            color: #f39c12;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 5px 0;
        }

        .report-meta {
            font-size: 9pt;
            color: #7f8c8d;
            font-weight: 600;
        }

        .stats-grid {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-card {
            flex: 1;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }

        .stat-card.primary {
            background-color: #34495e;
            border-color: #34495e;
            color: white;
        }

        .stat-label {
            display: block;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #7f8c8d;
            margin-bottom: 5px;
        }

        .stat-card.primary .stat-label {
            color: rgba(255, 255, 255, 0.7);
        }

        .stat-value {
            font-size: 16pt;
            font-weight: 700;
            margin: 0;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table thead {
            background-color: #f8f9fa;
        }

        .items-table th {
            padding: 10px 12px;
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            color: #34495e;
            border-bottom: 2px solid #dee2e6;
            text-align: left;
        }

        .items-table td {
            padding: 12px;
            font-size: 9pt;
            border-bottom: 1px solid #ecf0f1;
        }

        .type-badge {
            font-size: 8pt;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .type-in {
            background-color: #d4edda;
            color: #155724;
        }

        .type-out {
            background-color: #f8d7da;
            color: #721c24;
        }

        .impact-value {
            font-weight: 700;
        }

        .impact-positive {
            color: #27ae60;
        }

        .impact-negative {
            color: #c0392b;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 20px;
        }

        .signature-box {
            width: 30%;
            text-align: center;
        }

        .signature-label {
            font-size: 9pt;
            font-weight: 700;
            text-transform: uppercase;
            border-bottom: 1px solid #34495e;
            padding-bottom: 5px;
            margin-bottom: 60px;
        }

        .footer {
            text-align: center;
            font-size: 8pt;
            color: #bdc3c7;
            margin-top: 40px;
            font-style: italic;
        }
    </style>
@endsection

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: flex-start;" class="inventory-header">
        <div>
            <h1 class="company-name">{{ $boutique->nom ?? 'Ma Boutique' }}</h1>
            <h2 class="report-title">Audit d'Inventaire</h2>
            <div class="report-meta">
                Période : {{ $filters['start_date'] ?? 'Toutes' }} au {{ $filters['end_date'] ?? now()->format('d/m/Y') }}
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 10pt; font-weight: 700; color: #2c3e50;">Document N° INF-{{ now()->format('YmdHi') }}
            </div>
            <div style="font-size: 8pt; color: #7f8c8d;">Généré le {{ now()->format('d/m/Y à H:i') }}</div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Total Entrées</span>
            <p class="stat-value" style="color: #27ae60;">+{{ number_format($stats['totalEntrees'], 0) }}</p>
        </div>
        <div class="stat-card">
            <span class="stat-label">Total Sorties</span>
            <p class="stat-value" style="color: #c0392b;">-{{ number_format($stats['totalSorties'], 0) }}</p>
        </div>
        <div class="stat-card">
            <span class="stat-label">Valeur Acquisition</span>
            <p class="stat-value">{{ number_format($stats['valeurAchatEntrante'], 0, ',', ' ') }}
                <small>{{ $boutique->devise ?? 'CFA' }}</small></p>
        </div>
        <div class="stat-card primary">
            <span class="stat-label">Flux Net Stock</span>
            <p class="stat-value">{{ number_format($stats['netMouvement'], 0) }} Unités</p>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 15%;">Date / Heure</th>
                <th style="width: 25%;">Désignation Article</th>
                <th style="width: 12%; text-align: center;">Nature</th>
                <th style="width: 18%;">Motif / Description</th>
                <th style="width: 10%; text-align: center;">Qté</th>
                <th style="width: 10%; text-align: right;">Prix Unit.</th>
                <th style="width: 10%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($inventaires as $item)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</td>
                    <td style="font-weight: 600;">{{ $item->produit->nom ?? 'N/A' }}</td>
                    <td style="text-align: center;">
                        <span class="type-badge {{ $item->type === 'retrait' ? 'type-out' : 'type-in' }}">
                            {{ $item->type === 'retrait' ? 'Sortie' : 'Entrée' }}
                        </span>
                    </td>
                    <td style="font-style: italic; font-size: 8pt; color: #7f8c8d;">{{ $item->description }}</td>
                    <td style="text-align: center; font-weight: 700;"
                        class="{{ $item->type === 'retrait' ? 'impact-negative' : 'impact-positive' }}">
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

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-label">Gestionnaire de Stock</div>
            <div style="font-size: 8pt; color: #bdc3c7; margin-top: 10px;">Date et Signature</div>
        </div>
        <div class="signature-box">
            <div class="signature-label">Audit Interne</div>
            <div style="font-size: 8pt; color: #bdc3c7; margin-top: 10px;">Visa et Cachet</div>
        </div>
        <div class="signature-box">
            <div class="signature-label">Direction Générale</div>
            <div style="font-size: 8pt; color: #bdc3c7; margin-top: 10px;">Approbation Finale</div>
        </div>
    </div>

    <div class="footer">
        Ce document est un rapport d'audit officiel généré par le système de gestion Ma Boutique.
        Toute altération rend ce document invalide.
    </div>
@endsection
