@extends('pdf.layouts.base')

@section('title', 'Rapport Journalier')

@section('styles')
    <style>
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15mm;
            padding-bottom: 10mm;
            border-bottom: 3px solid #0f172a;
        }

        .section-title {
            font-size: 14pt;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            margin: 15mm 0 8mm 0;
            padding-bottom: 3mm;
            border-bottom: 2px solid #f59e0b;
        }

        .report-table {
            width: 100%;
            margin-bottom: 15mm;
        }

        .report-table thead tr {
            background-color: #0f172a;
        }

        .report-table th {
            padding: 8px 6px;
            font-size: 6pt;
            font-weight: 900;
            color: white;
            text-transform: uppercase;
        }

        .report-table td {
            padding: 6px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 7pt;
        }

        .report-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .subtotal-row {
            background-color: #e2e8f0 !important;
            font-weight: 900;
        }

        .subtotal-row td {
            padding: 10px 6px;
            font-size: 9pt;
            border-top: 2px solid #0f172a;
        }

        .summary-grid {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 20mm;
        }

        .summary-box {
            flex: 1;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
        }

        .summary-box.ventes {
            background-color: #10b981;
            color: white;
        }

        .summary-box.depenses {
            background-color: #ef4444;
            color: white;
        }

        .summary-box.benefice {
            background-color: #0f172a;
            color: white;
        }

        .summary-label {
            font-size: 7pt;
            font-weight: 700;
            text-transform: uppercase;
            opacity: 0.8;
            margin-bottom: 3px;
        }

        .summary-value {
            font-size: 20pt;
            font-weight: 900;
        }

        .stats-grid {
            display: flex;
            justify-content: space-around;
            margin-top: 10mm;
            padding: 10px;
            background-color: #f8fafc;
            border-radius: 10px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-label {
            font-size: 6pt;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
        }

        .stat-value {
            font-size: 12pt;
            font-weight: 900;
            color: #0f172a;
            margin-top: 2px;
        }
    </style>
@endsection

@section('content')
    <div class="report-header">
        <div style="text-align: left;">
            <h1 style="font-size: 28pt; font-weight: 900; margin-bottom: 5px;">{{ $boutique->nom ?? '-----' }}</h1>
            <p style="font-size: 8pt; font-weight: 700; color: #f59e0b; text-transform: uppercase; letter-spacing: 0.2em;">
                RAPPORT JOURNALIER
            </p>
            <div style="font-size: 8pt; font-weight: 700; color: #94a3b8; margin-top: 5px;">
                <p style="margin: 2px 0;">{{ $boutique->adresse ?? '-----' }}</p>
                <p style="margin: 2px 0;">Tél: {{ $boutique->telephone ?? '-----' }}</p>
            </div>
        </div>
        <div style="text-align: right;">
            <h2 style="font-size: 18pt; font-weight: 900; margin-bottom: 5px; color: #0f172a;">
                {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
            </h2>
            <p style="font-size: 9pt; font-weight: 700; color: #94a3b8; text-transform: uppercase;">
                {{ \Carbon\Carbon::parse($date)->locale('fr')->isoFormat('dddd') }}
            </p>
        </div>
    </div>

    <h3 class="section-title">VENTES DE LA JOURNÉE</h3>

    @if (count($ventes) > 0)
        <table class="report-table">
            <thead>
                <tr>
                    <th style="text-align: left; width: 40px;">N°</th>
                    <th style="text-align: left;">Client</th>
                    <th style="text-align: center; width: 60px;">Paiement</th>
                    <th style="text-align: left;">Produits</th>
                    <th style="text-align: center; width: 40px;">Qté</th>
                    <th style="text-align: right; width: 70px;">P.U.</th>
                    <th style="text-align: right; width: 70px;">Montant</th>
                    <th style="text-align: right; width: 60px;">Remise</th>
                    <th style="text-align: right; width: 80px;">Net</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ventes as $vente)
                    <tr>
                        <td style="font-weight: 900;">{{ $vente->id }}</td>
                        <td style="font-weight: 700;">{{ $vente->client->nom ?? 'CLIENT DE PASSAGE' }}</td>
                        <td style="text-align: center;">
                            <span
                                style="font-size: 6pt; font-weight: 900; padding: 2px 6px; border-radius: 5px; {{ $vente->type_paiement === 'credit' ? 'background-color: #fef3c7; color: #f59e0b;' : 'background-color: #d1fae5; color: #10b981;' }}">
                                {{ $vente->type_paiement === 'credit' ? 'CRÉDIT' : 'CASH' }}
                            </span>
                        </td>
                        <td>
                            @foreach ($vente->detailVentes as $detail)
                                <div style="margin: 1px 0;">
                                    <span style="font-weight: 700;">{{ $detail->produit->nom }}</span>
                                </div>
                            @endforeach
                        </td>
                        <td style="text-align: center; font-weight: 700;">
                            @foreach ($vente->detailVentes as $detail)
                                <div style="margin: 1px 0;">{{ $detail->quantite }}</div>
                            @endforeach
                        </td>
                        <td style="text-align: right;">
                            @foreach ($vente->detailVentes as $detail)
                                <div style="margin: 1px 0;">{{ number_format($detail->prix_unitaire, 0, ',', ' ') }}</div>
                            @endforeach
                        </td>
                        <td style="text-align: right; font-weight: 700;">
                            {{ number_format($vente->montant_total, 0, ',', ' ') }}
                        </td>
                        <td style="text-align: right; color: #10b981;">
                            {{ $vente->montant_remis > 0 ? '-' . number_format($vente->montant_remis, 0, ',', ' ') : '-' }}
                        </td>
                        <td style="text-align: right; font-weight: 900;">
                            {{ number_format($vente->montant_total - ($vente->montant_remis ?? 0), 0, ',', ' ') }}
                        </td>
                    </tr>
                @endforeach
                <tr class="subtotal-row">
                    <td colspan="6" style="text-align: right; text-transform: uppercase;">TOTAL VENTES:</td>
                    <td style="text-align: right;">{{ number_format($totaux['ventes_brut'], 0, ',', ' ') }}</td>
                    <td style="text-align: right; color: #10b981;">
                        -{{ number_format($totaux['remises'], 0, ',', ' ') }}
                    </td>
                    <td style="text-align: right; font-size: 11pt;">
                        {{ number_format($totaux['ventes_net'], 0, ',', ' ') }} {{ $boutique->devise ?? 'CFA' }}
                    </td>
                </tr>
            </tbody>
        </table>
    @else
        <p style="text-align: center; padding: 20px; color: #94a3b8; font-style: italic;">Aucune vente enregistrée pour
            cette journée</p>
    @endif

    <h3 class="section-title">💰 DÉPENSES DE LA JOURNÉE</h3>

    @if (count($depenses) > 0)
        <table class="report-table">
            <thead>
                <tr>
                    <th style="text-align: left; width: 40px;">N°</th>
                    <th style="text-align: left; width: 100px;">Type</th>
                    <th style="text-align: left;">Description</th>
                    <th style="text-align: right; width: 100px;">Montant</th>
                    <th style="text-align: left; width: 100px;">Enregistré par</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($depenses as $depense)
                    <tr>
                        <td style="font-weight: 900;">{{ $depense->id }}</td>
                        <td style="font-weight: 700; text-transform: uppercase;">{{ $depense->type }}</td>
                        <td>{{ $depense->description ?? '-' }}</td>
                        <td style="text-align: right; font-weight: 900; color: #ef4444;">
                            {{ number_format($depense->montant, 0, ',', ' ') }}
                        </td>
                        <td>{{ $depense->user->name ?? '-' }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal-row">
                    <td colspan="3" style="text-align: right; text-transform: uppercase;">TOTAL DÉPENSES:</td>
                    <td style="text-align: right; font-size: 11pt; color: #ef4444;">
                        {{ number_format($totaux['depenses'], 0, ',', ' ') }} {{ $boutique->devise ?? 'CFA' }}
                    </td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    @else
        <p style="text-align: center; padding: 20px; color: #94a3b8; font-style: italic;">Aucune dépense enregistrée pour
            cette journée</p>
    @endif

    <div class="summary-grid">
        <div class="summary-box ventes">
            <div class="summary-label">Total Ventes</div>
            <div class="summary-value">{{ number_format($totaux['ventes_net'], 0, ',', ' ') }}</div>
            <div style="font-size: 7pt; opacity: 0.8;">{{ $boutique->devise ?? 'CFA' }}</div>
        </div>
        <div class="summary-box depenses">
            <div class="summary-label">Total Dépenses</div>
            <div class="summary-value">{{ number_format($totaux['depenses'], 0, ',', ' ') }}</div>
            <div style="font-size: 7pt; opacity: 0.8;">{{ $boutique->devise ?? 'CFA' }}</div>
        </div>
        <div class="summary-box benefice">
            <div class="summary-label">Bénéfice Net</div>
            <div class="summary-value">{{ number_format($totaux['benefice_net'], 0, ',', ' ') }}</div>
            <div style="font-size: 7pt; opacity: 0.8;">{{ $boutique->devise ?? 'CFA' }}</div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-label">Nombre de Ventes</div>
            <div class="stat-value">{{ $stats['nombre_ventes'] }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Nombre de Dépenses</div>
            <div class="stat-value">{{ $stats['nombre_depenses'] }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Vente Moyenne</div>
            <div class="stat-value">{{ number_format($stats['vente_moyenne'], 0, ',', ' ') }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Ventes Crédit</div>
            <div class="stat-value">{{ $stats['ventes_credit'] }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Ventes Cash</div>
            <div class="stat-value">{{ $stats['ventes_cash'] }}</div>
        </div>
    </div>

    <p
        style="text-align: center; font-size: 6pt; color: #cbd5e1; text-transform: uppercase; letter-spacing: 0.3em; margin-top: 20mm;">
        RAPPORT GÉNÉRÉ AUTOMATIQUEMENT - {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}
    </p>
@endsection
