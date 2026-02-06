@extends('pdf.layouts.base')

@section('title', 'Bordereau')

@section('styles')
    <style>
        /* Réutilise les styles de facture avec quelques ajustements */
        .bordereau-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20mm;
            padding-bottom: 15mm;
            border-bottom: 2px solid #0f172a;
        }

        .bordereau-table {
            width: 100%;
            margin-bottom: 15mm;
        }

        .bordereau-table thead tr {
            background-color: #f8fafc;
        }

        .bordereau-table th {
            padding: 8px 10px;
            font-size: 7pt;
            font-weight: 900;
            color: #94a3b8;
            text-transform: uppercase;
        }

        .bordereau-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 9pt;
        }

        .signature-box {
            text-align: center;
            padding-top: 40px;
        }

        .signature-box p {
            font-size: 8pt;
            font-weight: 900;
            text-transform: uppercase;
            color: #cbd5e1;
            margin-bottom: 60px;
        }
    </style>
@endsection

@section('content')
    <div class="bordereau-header">
        <div>
            <h1 style="font-size: 24pt; font-weight: 700; margin-bottom: 5px;">{{ $boutique->nom ?? '-----' }}</h1>
            <p
                style="font-size: 7pt; font-weight: 700; color: #f59e0b; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 10px;">
                {{ $boutique->description_bordereau ?? 'BORDEREAU COMMERCIAL' }}
            </p>
            <div style="font-size: 8pt; font-weight: 700; color: #94a3b8; text-transform: uppercase;">
                <p style="margin: 2px 0;">{{ $boutique->adresse ?? '-----' }}</p>
                <p style="margin: 2px 0;">Tél: {{ $boutique->telephone ?? '-----' }}</p>
            </div>
        </div>
        <div style="text-align: right;">
            <h2 style="font-size: 24pt; font-weight: 900; margin-bottom: 5px;">
                {{ $vente->type_paiement === 'proforma' ? 'PROFORMA' : 'BORDEREAU' }}
            </h2>
            <div style="margin-top: 8px;">
                <p style="font-size: 12pt; font-weight: 900; margin: 2px 0;">N° {{ $vente->id }}/{{ date('y') }}</p>
                <p style="font-size: 7pt; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin: 2px 0;">
                    {{ \Carbon\Carbon::parse($vente->date_vente)->format('d / m / Y') }}
                </p>
            </div>
        </div>
    </div>

    <div
        style="display: flex; justify-content: space-between; margin-bottom: 20mm; padding-bottom: 10mm; border-bottom: 1px solid #f1f5f9;">
        <div>
            <span style="font-size: 7pt; font-weight: 900; color: #e2e8f0; text-transform: uppercase;">Destinataire</span>
            <p style="font-size: 14pt; font-weight: 700; margin-top: 3px; text-transform: uppercase;">
                {{ $vente->client->nom ?? 'CLIENT DE PASSAGE' }}</p>
        </div>
        <div style="text-align: right;">
            <span style="font-size: 7pt; font-weight: 900; color: #e2e8f0; text-transform: uppercase;">Contact Client</span>
            <p style="font-size: 9pt; font-weight: 700; text-transform: uppercase; margin-top: 3px;">
                {{ $vente->client->telephone ?? 'N/A' }}</p>
        </div>
    </div>

    <table class="bordereau-table">
        <thead>
            <tr>
                <th style="text-align: left;">Désignation</th>
                <th style="text-align: center; width: 60px;">Qté</th>
                <th style="text-align: right; width: 80px;">Prix Unit.</th>
                <th style="text-align: right; width: 100px;">Montant</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($vente->detailVentes as $detail)
                <tr>
                    <td>
                        <p style="font-weight: 700; margin: 0;">{{ $detail->produit->nom }}</p>
                        <p style="font-size: 7pt; color: #94a3b8; font-style: italic; margin: 2px 0;">Code:
                            #P-{{ $detail->produit->id }}</p>
                    </td>
                    <td style="text-align: center; font-weight: 700;">{{ $detail->quantite }}</td>
                    <td style="text-align: right;">{{ number_format($detail->prix_unitaire, 0, ',', ' ') }}</td>
                    <td style="text-align: right; font-weight: 900;">
                        {{ number_format($detail->montant_total, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30mm;">
        <div style="flex: 1; margin-right: 30px;">
            <div
                style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 15px; padding: 12px; font-size: 8pt; color: #94a3b8; font-style: italic;">
                {{ $boutique->footer_bordereau ?? 'Note: Ce document est la propriété de Ma Boutique jusqu\'au règlement intégral. Tout défaut de paiement pourra entraîner des poursuites.' }}
            </div>
        </div>
        <div style="width: 200px;">
            <div
                style="display: flex; justify-content: space-between; font-size: 8pt; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 5px; padding: 0 8px;">
                <span>Total Général</span>
                <span style="color: #0f172a;">{{ number_format($vente->montant_total, 0, ',', ' ') }}</span>
            </div>
            @if ($vente->type_paiement !== 'proforma' && $vente->montant_restant < $vente->montant_total)
                <div
                    style="display: flex; justify-content: space-between; font-size: 8pt; font-weight: 900; color: #10b981; text-transform: uppercase; margin-bottom: 8px; padding: 0 8px 8px; border-bottom: 1px solid #f1f5f9;">
                    <span>Déjà Réglé</span>
                    <span>- {{ number_format($vente->montant_total - $vente->montant_restant, 0, ',', ' ') }}</span>
                </div>
            @endif
            <div
                style="background-color: #0f172a; color: white; padding: 15px 20px; border-radius: 20px; text-align: right;">
                <span
                    style="display: block; font-size: 7pt; font-weight: 900; color: #f59e0b; text-transform: uppercase; margin-bottom: 3px;">
                    {{ $vente->type_paiement === 'proforma' ? 'NET A PAYER' : 'RESTE A PAYER' }}
                </span>
                <p style="font-size: 24pt; font-weight: 900; margin: 0;">
                    {{ number_format($vente->montant_restant, 0, ',', ' ') }}
                    <span style="font-size: 9pt; opacity: 0.4;">{{ $boutique->devise ?? 'CFA' }}</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Signatures -->
    <div style="display: flex; justify-content: space-between; padding-top: 20mm; border-top: 1px dashed #e2e8f0;">
        <div class="signature-box">
            <p>Le Responsable</p>
        </div>
        <div class="signature-box">
            <p>Le Client (Bon pour accord)</p>
        </div>
    </div>

    <p
        style="text-align: center; font-size: 6pt; color: #e2e8f0; text-transform: uppercase; letter-spacing: 0.3em; margin-top: 10mm;">
        DOCUMENT OFFICIEL DE GESTION - {{ strtoupper($boutique->nom ?? 'MA BOUTIQUE') }}
    </p>
@endsection
